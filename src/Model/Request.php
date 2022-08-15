<?php
namespace Bdd88\RestApi\Model;

use Exception;

/** Stores and parses data provided by the user request. */
class Request
{
    public string $method;
    public string|FALSE|NULL $tokenString;
    public string|FALSE $endpointName;
    public array $endpointValues;
    public string|NULL $payload;

    public function __construct()
    {
        $this->method = $_SERVER['REQUEST_METHOD'];
        $this->accept = $_SERVER['HTTP_ACCEPT'];
        $this->tokenString = $this->parseTokenString($_SERVER['HTTP_AUTHORIZATION']);
        $this->endpointName = $this->parseUri($_SERVER['REQUEST_URI']);
        $this->payload = file_get_contents("php://input");
    }

    /**
     * Parse the authorization header and return the token string.
     *
     * @param string $authorization Authorization header from the client.
     * @return string|FALSE|NULL The token string from the auth header. FALSE if it's not a BEARER token. NULL if no authorization header was provided.
     */
    private function parseTokenString(string $authorization): string|FALSE|NULL
    {
        if ($authorization === NULL) {
            return NULL;
        }
        list($authScheme, $authParameter) = explode(' ', $authorization);
        if ($authScheme !== 'Bearer') {
            return FALSE;
        }
        return $authParameter;
    }

    /**
     * Parse a URI request string.
     *
     * @param string $uri Requested URI.
     * @return string|FALSE The endpoint name requested. FALSE if the request is malformed.
     */
    private function parseUri(string $uri): string|FALSE
    {
        // Parse the URI and remove the script name from the request path.
        $requestArray = parse_url($uri);
        $requestPath = array_values(array_diff(
            explode('/', $requestArray['path']),
            explode('/', $_SERVER['SCRIPT_NAME'])
        ));

        // Set the endpoing name to FALSE and return early if the URI request is malformed.
        $requestEntries = sizeof($requestPath);
        if ($requestEntries % 2 !== 0) {
            return FALSE;
        }

        // Create a map of endpoint name/value pairs.
        for ($i = 0; $i < $requestEntries; $i++) {
            $entry = $requestPath[$i];
            if ($entry !== '') {
                $this->endpointValues[$entry] = $requestPath[$i + 1];
                $i++;
            }
        }

        // Return the endpoint name without values.
        return implode('/', array_keys($this->endpointValues));
    }

}

?>
