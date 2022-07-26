<?php
namespace bdd88\RestApi\Model;

use Exception;

/** Stores and parses data provided by the user request. */
class Request
{
    public string $uri;
    public string $method;
    public string|null $payload;
    public string $endpointName;
    public array $endpointValues;

    public function __construct()
    {
        $this->uri = $_SERVER['REQUEST_URI'];
        $this->method = $_SERVER['REQUEST_METHOD'];
        $this->payload = file_get_contents("php://input");
        $this->parse();
    }

    /** Parse a URI request string. */
    private function parse(): void
    {
        $requestArray = parse_url($this->uri);
        $requestPath = array_values(array_diff(
            explode('/', $requestArray['path']),
            explode('/', $_SERVER['SCRIPT_NAME'])
        ));
        $requestEntries = sizeof($requestPath);
        if ($requestEntries % 2 !== 0) {
            throw new Exception('Improperly formatted URI: Missing endpoint name or value.');
        }
        for ($i = 0; $i < $requestEntries; $i++) {
            $entry = $requestPath[$i];
            if ($entry !== '') {
                $this->endpointValues[$entry] = $requestPath[$i + 1];
                $i++;
            }
        }
        $this->endpointName = implode('/', array_keys($this->endpointValues));
    }


}

?>
