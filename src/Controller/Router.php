<?php
namespace Bdd88\RestApi\Controller;

use Bdd88\RestApi\Model\Request;
use Bdd88\RestApi\Model\HttpResponseCode;
use Exception;

/** Determines what classes to load based on a given request, and processes URI data for usage. */
class Router
{
    private Request $request;
    private Login $login;
    private HttpResponseCode $HttpResponseCode;
    private const ALLOWED_METHODS = array(
        'POST',
        'GET',
        'PUT',
        'PATCH',
        'DELETE'
    );
    private array $endpoints;

    public function __construct(Request $request, Login $login, HttpResponseCode $HttpResponseCode)
    {
        $this->request  = $request;
        $this->login = $login;
        $this->HttpResponseCode = $HttpResponseCode;
    }


    /** Create a mapping for endpoint name to class. */
    public function createEndpoint(string $endpointName, string $endpointClass): void
    {
        $this->endpoints[$endpointName] = $endpointClass;
    }

    /**
     * Get the class name of the correct endpoint to route to based on the request and login status.
     *
     * Uses HttpResponseCode object to set the http response code as apropriate and store details for failed routing.
     * 
     * @return string|FALSE The class name of the proper endpoint. FALSE if the request can't be routed to an endpoint properly.
     */
    public function route(): string|FALSE
    {
        // Malformed URI request.
        if ($this->request->endpointName === FALSE) {
            $this->HttpResponseCode->set(400);
        }
        // No token provided.
        if ($this->login->loggedIn === NULL) {
            $this->HttpResponseCode->set(401);
        }
        // Token is invalid.
        if ($this->login->loggedIn === FALSE) {
            $this->HttpResponseCode->set(403);
        }
        // Endpoint doesn't exist.
        if (!isset($this->endpoints[$this->request->endpointName])) {
            $this->HttpResponseCode->set(404);
        }
        // HTTP Method isn't allowed.
        if (!isset(SELF::ALLOWED_METHODS[$this->requestMethod])) {
            $this->HttpResponseCode->set(405);
        }
        // Accept header isn't set to JSON.
        if ($this->request->accept !== 'application/json') {
            $this->HttpResponseCode->set(406);
        }

        if ($this->HttpResponseCode->isSet()) {
            return FALSE;
        }
        $this->HttpResponseCode->set(200);
        return $this->endpoints[$this->request->endpointName];
    }

}

?>