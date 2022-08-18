<?php
namespace Bdd88\RestApi\Model;

/**
 * Defines the basic methods necessary to create custom endpoints.
 * Extend this class and use the injected MySql object to create your endpoint CRUD operations.
 */
abstract class EndpointAbstract
{
    protected Request $request;
    protected HttpResponseCode $httpResponseCode;

    /** Run the serve method and return the string value. */
    public function __toString(): string
    {
        return $this->serve();
    }

    /** 
     * Setter for class dependencies.
     * This is used instead of constructor injection so the user can overwrite and use the constructor for their own dependency objects.
     */
    public function injectDependencies(Request $request, HttpResponseCode $httpResponseCode): void
    {
        $this->request = $request;
        $this->httpResponseCode = $httpResponseCode;
    }

    protected abstract function create(): string;
    protected abstract function read(): string;
    protected abstract function replace(): string;
    protected abstract function update(): string;
    protected abstract function delete(): string;

    /** Execute the correct class method based on the HTTP request method. */
    final public function serve(): string
    {
        $crudMethods = array(
            'POST' => 'create',
            'GET' => 'read',
            'PUT' => 'replace',
            'PATCH' => 'update',
            'DELETE' => 'delete'
        );
        $action = $crudMethods[$this->request->method];
        return $this->$action();
    }
}