<?php
namespace Bdd88\RestApi\Controller;

use Bdd88\JsonWebToken\JwtFactory;
use Bdd88\ServiceContainer\ServiceContainer;
use Bdd88\RestApi\Model\EndpointAbstract;
use Bdd88\RestApi\Model\HttpResponseCode;

/** Primary controller that handles flow of data between the user, sub-controllers, and models. */
class Main
{
    private ServiceContainer $serviceContainer;
    private array $endpointMap;
    
    public function __construct(?string $publicKey = NULL, ?string $privateKey = NULL, private ?bool $debugMode = NULL)
    {
        set_exception_handler(array($this, 'exceptionHandler'));
        set_error_handler(array($this, 'errorHandler'), E_WARNING);
        $this->serviceContainer = new ServiceContainer();
        $this->serviceContainer->create('\Bdd88\JsonWebToken\JwtFactory', [$publicKey, $privateKey]);
        header('Content-Type: application/json');
    }

    /** Catch all error handler that converts errors into exceptions. */
    public function errorHandler(int $number, string $description, string $file, int $line)
    {
        throw new \Exception($description);
    }

    /** Catch all exception handler that hides specific error details from the client unless debug mode is enabled. */
    public function exceptionHandler(\Throwable $exception): void
    {
        /** @var HttpResponseCode $httpResponseCode */
        $httpResponseCode = $this->serviceContainer->create('\Bdd88\RestApi\Model\HttpResponseCode');
        $message = ($this->debugMode === TRUE)? explode(PHP_EOL, $exception): 'Oops! We ran into an error. Please try again in a few minutes, and contact the site administrator if the error persists.';
        $httpResponseCode->set(500, $message);
        echo $httpResponseCode;
        exit;
    }

    public function createToken(array $header, array $payload): string
    {
        /** @var JwtFactory $jwtFactory */
        $jwtFactory = $this->serviceContainer->get('\Bdd88\JsonWebToken\JwtFactory');
        $token = $jwtFactory->generate($header, $payload);
        return $token;
    }

    /** Create a mapping for endpoint name to class. */
    public function createEndpoint(string $endpointName, string $endpointClass): void
    {
        $this->endpointMap[$endpointName] = $endpointClass;
    }

    /** Process the requested URI, and serve the appropriate endpoint. */
    public function exec(): void
    {
        // Use the router to find the correct endpoint class.
        /** @var Router $router */
        $router = $this->serviceContainer->create('\Bdd88\RestApi\Controller\Router');
        $destination = $router->route($this->endpointMap);

        // If there was an error routing then display the HTTP Response Code and related details.
        if ($destination === FALSE) {
            echo $this->serviceContainer->get('\Bdd88\RestApi\Model\HttpResponseCode');
            return;
        }

        // Instiate and automatically inject dependencies for the users Endpoint class. Manually inject Request and HttpResponseCode dependencies.
        /** @var EndpointAbstract $endpoint */
        $endpoint = $this->serviceContainer->create($destination);
        $endpoint->injectDependencies(
            $this->serviceContainer->get('\Bdd88\RestApi\Model\Request'),
            $this->serviceContainer->get('\Bdd88\RestApi\Model\HttpResponseCode')
        );
        echo $endpoint;
    }

}


?>