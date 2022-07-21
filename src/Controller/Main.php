<?php
namespace bdd88\RestApi\Controller;

use bdd88\RestApi\Model\DatabaseAbstract;
use bdd88\RestApi\Model\EndpointAbstract;
use bdd88\RestApi\Model\MySql;
use bdd88\RestApi\Model\Request;

/** Primary controller that handles flow of data between the user, sub-controllers, and models. */
class Main
{
    private DatabaseAbstract $database;
    private Router $router;
    private Request $request;

    public function __construct()
    {
        $this->database = new MySql();
        $this->router = new Router();
        $this->request = new Request();
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