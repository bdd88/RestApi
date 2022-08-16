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

    /** 
     * Setter for class dependencies.
     * This is used instead of constructor injection so the user can overwrite and use the constructor for their own dependency objects.
     */
    public function injectDependencies(Request $request, HttpResponseCode $httpResponseCode): void
    {
        $this->request = $request;
        $this->httpResponseCode = $httpResponseCode;
    }

    /** Run the serve method and return the string value. */
    public function __toString(): string
    {
        return $this->serve();
    }

    /**
     * Create a new record.
     * @return string JSON formatted string response.
     */
    protected abstract function create(): string;

    /**
     * Retrieve one or more records.
     * @return string JSON formatted string response.
     */
    protected abstract function read(): string;

    /**
     * Replace an entire record.
     * @return string JSON formatted string response.
     */
    protected abstract function replace(): string;

    /**
     * Update part of a record.
     * @return string JSON formatted string response.
     */
    protected abstract function update(): string;

    /**
     * Delete a record.
     * @return string JSON formatted string response.
     */
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
        $action = $crudMethods[$this->requestMethod];
        return $this->$action();
    }
}