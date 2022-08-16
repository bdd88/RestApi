<?php
namespace Bdd88\RestApi\Controller;

use Bdd88\RestApi\Model\Request;
use Bdd88\RestApi\Model\HttpResponseCode;
use Exception;

/** Determines what classes to load based on a given request, and processes URI data for usage. */
class Router
{
    private const ALLOWED_METHODS = array(
        'POST',
        'GET',
        'PUT',
        'PATCH',
        'DELETE'
    );

    public function __construct(
        private Request $request,
        private Login $login,
        private HttpResponseCode $HttpResponseCode
        ) {}

    /**
     * Get the class name of the correct endpoint to route to based on the request and login status.
     *
     * Uses HttpResponseCode object to set the http response code as apropriate and store details for failed routing.
     * 
     * @return string|FALSE The class name of the proper endpoint. FALSE if the request can't be routed to an endpoint properly.
     */
    public function route(array $endpointMap): string|FALSE
    {
        $loginStatus = $this->login->verify();
        if ($this->request->endpointName === FALSE) {
            $this->HttpResponseCode->set(400, 'Malformed URI request.');

        } elseif ($loginStatus === NULL) {
            $this->HttpResponseCode->set(401, 'No token provided.');

        } elseif (is_string($loginStatus)) {
            $this->HttpResponseCode->set(403, $loginStatus);

        } elseif (!isset($endpointMap[$this->request->endpointName])) {
            $this->HttpResponseCode->set(404, 'Endpoint doesn\'t exist.');

        } elseif (!isset(SELF::ALLOWED_METHODS[$this->requestMethod])) {
            $this->HttpResponseCode->set(405, 'HTTP Method isn\'t allowed.');

        } elseif ($this->request->accept !== 'application/json') {
            $this->HttpResponseCode->set(406, 'Accept header isn\'t set to application/json.');
            
        }

        if ($this->HttpResponseCode->isSet()) {
            return FALSE;
        }
        $this->HttpResponseCode->set(200);
        return $endpointMap[$this->request->endpointName];
    }

}

?>