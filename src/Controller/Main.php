<?php
namespace Bdd88\RestApi\Controller;

use Bdd88\ServiceContainer\ServiceContainer;
use Bdd88\RestApi\Model\EndpointAbstract;
use Throwable;

/** Primary controller that handles flow of data between the user, sub-controllers, and models. */
class Main
{
    private ServiceContainer $serviceContainer;
    private bool $debugMode;

    /**
     * @param string $databaseConfigPath Path to ini file containing database credentials.
     * @param string $publicKey PEM formatted Public Key for RSA, and Shared Secret for HMAC.
     * @param string|null $privateKey (optional) PEM formatted RSA private key to use for signing tokens.
     * @param string|null $debugMode (optional) TRUE will display errors/exceptions as they occur to the end user. FALSE will hide errors behind a generic message.
     */
    public function __construct(string $databaseConfigPath, string $publicKey, ?string $privateKey = NULL, ?string $debugMode = NULL)
    {
        $this->debugMode = $debugMode ?? FALSE;
        set_exception_handler(array($this, 'exceptionHandler'));
        $this->serviceContainer = new ServiceContainer();
        $this->serviceContainer->create('\Bdd88\RestApi\Model\ConfigDatabase', [$databaseConfigPath]);
        $this->serviceContainer->create('\Bdd88\RestApi\Controller\Router');
    }

    public function exceptionHandler(Throwable $exception): void
    {
        if ($this->debugMode) {
            echo '<pre>';
            echo $exception;
            echo '</pre>';
        } else {
            echo 'Oops! We ran into an error. Please try again in a few minutes, and contact the site administrator if the error persists.';
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
        /** @var Router $router */
        $router = $this->serviceContainer->get('\Bdd88\RestApi\Controller\Router');
        /** @var EndpointAbstract $endpoint */
        $endpoint = $this->serviceContainer->create($router->route());
        echo $endpoint->serve();
    }

}


?>