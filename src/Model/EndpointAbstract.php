<?php
namespace Bdd88\RestApi\Model;

use Bdd88\MySql\MySql;
use Exception;

/**
 * Defines the basic methods necessary to create custom endpoints.
 * Extend this class and use the injected MySql object to create your endpoint CRUD operations.
 */
abstract class EndpointAbstract
{
    protected MySql $database;
    protected string $name;
    protected array $uriValues;
    protected string $requestMethod;
    protected mixed $query;

    final public function __construct(MySql $database, Request $request)
    {
        $this->database = $database;
        $this->endpointName = $request->endpointName;
        $this->endpointValues = $request->endpointValues;
        $this->requestMethod = $request->method;
        $this->query = json_decode($request->payload);
    }

    /**
     * Create a new record.
     * @return string JSON formatted string containing HTTP Status code and creation success status.
     */
    protected abstract function create(): string;

    /**
     * Retrieve one or more records.
     * @return string JSON formatted string containing HTTP Status code and requested records.
     */
    protected abstract function read(): string;

    /**
     * Replace an entire record.
     * @return string JSON formatted string containing HTTP Status code and update success status.
     */
    protected abstract function replace(): string;

    /**
     * Update part of a record.
     * @return string JSON formatted string containing HTTP Status code and update success status.
     */
    protected abstract function update(): string;

    /**
     * Delete a record.
     * @return string JSON formatted string containing HTTP Status code and deletion success status.
     */
    protected abstract function delete(): string;

    /** Execute the correct class method based on the HTTP request method. */
    final public function serve(): void
    {
        $crudMethods = array('POST' => 'create', 'GET' => 'read', 'PUT' => 'replace', 'PATCH' => 'update', 'DELETE' => 'delete');
        $action = $crudMethods[$this->requestMethod] ?? NULL;
        if (is_null($action)) {
            throw new Exception('Unsupported HTTP request method.');
        } else {
            $this->$action();
        }
    }
}