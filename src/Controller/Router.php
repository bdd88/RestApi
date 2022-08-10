<?php
namespace Bdd88\RestApi\Controller;

use Bdd88\RestApi\Model\Request;
use Exception;

/** Determines what classes to load based on a given request, and processes URI data for usage. */
class Router
{
    private Request $request;
    private Login $login;
    private array $endpoints;

    public function __construct(Request $request, Login $login)
    {
        $this->request  = $request;
        $this->login = $login;
    }


    /** Create a mapping for endpoint name to class. */
    public function createEndpoint(string $endpointName, string $endpointClass): void
    {
        $this->endpoints[$endpointName] = $endpointClass;
    }

    /**
     * Determine the correct way to route a request based on the endpoint name supplied by the request.
     *
     * @return string The class name or http status code for the routed request.
     */
    public function route(): string
    {
        if (!isset($this->endpoints[$this->request->endpointName])) {
            throw new Exception('Endpoint doesn\'t exist: \'' . $this->request->endpointName . '\'');
        } else {
            return $this->endpoints[$this->request->endpointName];
        }
    }

}

?>