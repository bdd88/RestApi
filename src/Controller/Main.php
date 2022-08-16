<?php
namespace Bdd88\RestApi\Controller;

use Bdd88\ServiceContainer\ServiceContainer;
use Bdd88\RestApi\Model\EndpointAbstract;
use Bdd88\RestApi\Model\HttpResponseCode;
use Throwable;

/** Primary controller that handles flow of data between the user, sub-controllers, and models. */
class Main
{
    private ServiceContainer $serviceContainer;
    private Router $router;
    private bool $debugMode;

    /**
     * @param string $databaseConfigPath Path to ini file containing database credentials.
     * @param string $publicKey PEM formatted Public Key for RSA, and Shared Secret for HMAC.
     * @param string|null $privateKey (optional) PEM formatted RSA private key to use for signing tokens.
     * @param string|null $debugMode (optional) TRUE will display errors/exceptions as they occur to the end user. FALSE will hide errors behind a generic message.
     */
    public function __construct(string $databaseConfigPath, string $publicKey, ?string $privateKey = NULL, ?string $debugMode = NULL)
    {
        // Setup debug mode error handling.
        $this->debugMode = $debugMode ?? FALSE;
        set_exception_handler(array($this, 'exceptionHandler'));

        // Use the service container to setup, inject, and configure class instances.
        $this->serviceContainer = new ServiceContainer();
        $this->serviceContainer->create('\Bdd88\RestApi\Model\ConfigDatabase', [$databaseConfigPath]);
        $this->serviceContainer->create('\Bdd88\JsonWebToken\JwtFactory', [$publicKey, $privateKey]);
        $this->router = $this->serviceContainer->create('\Bdd88\RestApi\Controller\Router');
    }

    public function exceptionHandler(Throwable $exception): void
    {
        if ($this->debugMode) {
            echo '<pre>';
            echo $exception;
            echo '</pre>';
        } else {
            /** @var HttpResponseCode $httpResponseCode */
            $httpResponseCode = $this->serviceContainer->create('\Bdd88\RestApi\Model\HttpResponseCode');
            $httpResponseCode->set(500, 'Oops! We ran into an error. Please try again in a few minutes, and contact the site administrator if the error persists.');
            header("Content-Type: application/json");
            echo $httpResponseCode->__toString();
        }
    }

    /** Create a mapping for endpoint name to class. */
    public function createEndpoint(string $endpointName, string $endpointClass): void
    {
        $this->router->createEndpoint($endpointName, $endpointClass);
    }

    /** Process the requested URI, and serve the appropriate endpoint. */
    public function exec()
    {
        $destination = $this->router->route();
        if ($destination === FALSE) {
            /** @var HttpResponseCode $output */
            $output = $this->serviceContainer->get('\Bdd88\RestApi\Model\HttpResponseCode');
        } else {
            /** @var EndpointAbstract $output */
            $output = $this->serviceContainer->create($destination);
        }
        header("Content-Type: application/json");
        echo $output->__toString();
    }

}


?>