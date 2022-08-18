<?php
namespace Bdd88\RestApi\Model;

/** Handles basic operations relating to HTTP Status Codes. */
class HttpResponseCode
{
    private const STATUS_CODES = array(
        '100' => 'Continue', 
        '101' => 'Switching Protocols', 
        '200' => 'OK', 
        '201' => 'Created', 
        '202' => 'Accepted', 
        '203' => 'Non-Authoritative Information', 
        '204' => 'No Content', 
        '205' => 'Reset Content', 
        '206' => 'Partial Content', 
        '300' => 'Multiple Choices', 
        '301' => 'Moved Permanently', 
        '302' => 'Found', 
        '303' => 'See Other', 
        '304' => 'Not Modified', 
        '305' => 'Use Proxy', 
        '307' => 'Temporary Redirect', 
        '400' => 'Bad Request', 
        '401' => 'Unauthorized', 
        '402' => 'Payment Required', 
        '403' => 'Forbidden', 
        '404' => 'Not Found', 
        '405' => 'Method Not Allowed', 
        '406' => 'Not Acceptable', 
        '407' => 'Proxy Authentication Required', 
        '408' => 'Request Timeout', 
        '409' => 'Conflict', 
        '410' => 'Gone', 
        '411' => 'Length Required', 
        '412' => 'Precondition Failed', 
        '413' => 'Payload Too Large', 
        '414' => 'URI Too Long', 
        '415' => 'Unsupported Media Type', 
        '416' => 'Range Not Satisfiable', 
        '417' => 'Expectation Failed', 
        '426' => 'Upgrade Required', 
        '500' => 'Internal Server Error', 
        '501' => 'Not Implemented', 
        '502' => 'Bad Gateway', 
        '503' => 'Service Unavailable', 
        '504' => 'Gateway Timeout', 
        '505' => 'HTTP Version Not Supported'
    );
    private int $statusCode;
    private mixed $details;

    /** JSON encoded string containing the code number, description, and any additional details. */
    public function __toString(): string
    {
        $output = array(
            'code' => $this->statusCode,
            'description' => SELF::STATUS_CODES[$this->statusCode],
            'details' => $this->details
        );
        return json_encode($output);
    }

    /** Set the HTTP Response code and additional details */
    public function set(int $code, mixed $details = NULL): void
    {
        http_response_code($code);
        $this->statusCode = $code;
        $this->details = $details;
    }

    /** Retrieve the currently set http response status code. */
    public function get(): int
    {
        return $this->statusCode;
    }

    /** Return the description for a specified HTTP status code. */
    public function getDescription(int $statusCode): string
    {
        return SELF::STATUS_CODES[$statusCode];
    }

    public function isSet(): bool
    {
        if (isset($this->statusCode)) {
            return TRUE;
        }
        return FALSE;
    }
}

?>