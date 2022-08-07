<?php
namespace Bdd88\RestApi\Controller;

use Bdd88\RestApi\Model\ConfigDatabase;
use Bdd88\RestApi\Model\EndpointAbstract;
use Bdd88\RestApi\Model\Request;
use Bdd88\MySql\MySql;
use Throwable;

/** Primary controller that handles flow of data between the user, sub-controllers, and models. */
class Main
{
    private MySql $database;
    private Router $router;
    private Request $request;
    private ConfigDatabase $databaseConfig;
    private bool $debugMode;

    public function __construct(string $databaseConfigPath, ?string $debugMode = NULL)
    {
        $this->debugMode = $debugMode ?? FALSE;
        set_exception_handler(array($this, 'exceptionHandler'));
        $this->databaseConfig = new ConfigDatabase($databaseConfigPath);
        $this->database = new MySql(...$this->databaseConfig->getAllSettings());
        $this->request = new Request();
        $this->router = new Router();
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
        $destination = $this->router->route($this->request->endpointName);
        /** @var EndpointAbstract $endpoint */
        $endpoint = new $destination($this->database, $this->request);
        echo $endpoint->serve();
    }

}


?>