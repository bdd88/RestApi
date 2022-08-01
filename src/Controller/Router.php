<?php
namespace Bdd88\RestApi\Controller;

use Exception;

/** Determines what classes to load based on a given request, and processes URI data for usage. */
class Router
{
    private array $endpoints;

    /** Create a mapping for endpoint name to class. */
    public function createEndpoint(string $endpointName, string $endpointClass): void
    {
        $this->endpoints[$endpointName] = $endpointClass;
    }

    /**
     * Determine the correct way to route a request based on the endpoint name.
     *
     * @return string The class name or http status code for the routed request.
     */
    public function route(string $endpointName): string
    {
        if (!isset($this->endpoints[$endpointName])) {
            throw new Exception('Endpoint doesn\'t exist: \'' . $endpointName . '\'');
        } else {
            return $this->endpoints[$endpointName];
        }
    }

}

?>