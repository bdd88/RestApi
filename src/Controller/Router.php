<?php
namespace bdd88\RestApi\Controller;

use Exception;

class Router
{
    private array $endpoints;


    /** Create a mapping for endpoint name to class. */
    public function createEndpoint(string $endpointName, string $endpointClass): void
    {
        $this->endpoints[$endpointName] = $endpointClass;
    }
    
    /**
     * Parse a URI request string.
     * @return array Associate array with two entries: 'name' stores the endpoint name, and  'values' stores an associative array of key/value pairs parsed from the uri string.
     */
    private function parseRequest(string $request): array
    {
        $requestArray = parse_url($request);
        $requestArray['path'] = array_diff(
            explode('/', $requestArray['path']),
            explode('/', $_SERVER['SCRIPT_NAME'])
        );
        $endpointValues = array();
        $requestEntries = sizeof($requestArray['path']);
        for ($i = 0; $i < $requestEntries; $i++) {
            $entry = $requestArray['path'][$i];
            if ($entry !== '') {
                $endpointValues[$entry] = $requestArray['path'][$i + 1];
                $i++;
            }
        }
        $endpointName = implode('/', array_keys($endpointValues));
        return array('name' => $endpointName, 'values' => $endpointValues);
    }

    /**
     * Use a supplied URI to determine the correct API endpoint, and process the necessary data.
     *
     * @param string $uri A properly formatted URI that can be parsed by parse_url().
     * @return array Associative array containing: string 'class', string 'name', and array 'values'.
     */
    public function exec(string $uri): array
    {
        $endpoint = $this->parseRequest($uri);
        if (!isset($this->endpoints[$endpoint['name']])) {
            throw new Exception('Endpoint doesn\'t exist: \'' . $endpoint['name'] . '\'');
        } else {
            return array('class' => $this->endpoints[$endpoint['name']], 'name' => $endpoint['name'], 'values' => $endpoint['values']);
        }
    }

}

?>