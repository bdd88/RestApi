<?php
namespace Bdd88\RestApi\Model;

use Exception;

/** Stores and parses data provided by the user request. */
class Request
{
    public string $uri;
    public string $method;
    public string|null $payload;
    public string $endpointName;
    public array $endpointValues;

    public string $token;

    public function __construct()
    {
        $this->uri = $_SERVER['REQUEST_URI'];
        $this->method = $_SERVER['REQUEST_METHOD'];
        $this->authorization = $_SERVER['HTTP_AUTHORIZATION'];
        $this->payload = file_get_contents("php://input");
        $this->parse();
    }

    /** Parse a URI request string. */
    private function parse(): void
    {
        // Retrieve the bearer token for authorization.
        list($authScheme, $authParameter) = explode(' ', $this->authorization);
        if ($authScheme !== 'Bearer') {
            throw new Exception('Wrong HTTP authorization scheme (or improperly formatted)');
        }
        $this->token = $authParameter;

        // Parse the URI and remove the script name from the request path.
        $requestArray = parse_url($this->uri);
        $requestPath = array_values(array_diff(
            explode('/', $requestArray['path']),
            explode('/', $_SERVER['SCRIPT_NAME'])
        ));

        // Validate the request path.
        $requestEntries = sizeof($requestPath);
        if ($requestEntries % 2 !== 0) {
            throw new Exception('Improperly formatted URI: Missing endpoint name or value.');
        }

        // Create a map of endpoint name/value pairs.
        for ($i = 0; $i < $requestEntries; $i++) {
            $entry = $requestPath[$i];
            if ($entry !== '') {
                $this->endpointValues[$entry] = $requestPath[$i + 1];
                $i++;
            }
        }

        // Set the endpoint name.
        $this->endpointName = implode('/', array_keys($this->endpointValues));
    }


}

?>
