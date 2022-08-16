<?php
namespace Bdd88\RestApi\Controller;

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
        $this->serviceContainer = new ServiceContainer();
        $this->serviceContainer->create('\Bdd88\JsonWebToken\JwtFactory', [$publicKey, $privateKey]);
        header('Content-Type: application/json');
    }

    // Catch all exception handler that hides specific error details from the client unless debug mode is enabled.
    public function exceptionHandler(\Throwable $exception): void
    {
        if ($this->debugMode === TRUE) {
            header('Content-Type: text/plain');
            echo $exception;
        } else {
            /** @var HttpResponseCode $httpResponseCode */
            $httpResponseCode = $this->serviceContainer->create('\Bdd88\RestApi\Model\HttpResponseCode');
            $httpResponseCode->set(500, 'Oops! We ran into an error. Please try again in a few minutes, and contact the site administrator if the error persists.');
            echo $httpResponseCode;
        }
        exit;
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