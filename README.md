
# RestApi
RestApi is a library that makes creating a restfull api quick and simple. It uses json web tokens (JWT) for authentication and JSON payloads for delivery.

## Composer installation
Simply run the follow command in the root directory of your project:
```
composer require bdd88/restapi
```

## Usage
+ Supply a PEM format RSA public key to use the token validation feature.
+ Supply a PEM format RSA private key to use the token generation feature.
+ Extend the EndpointAbstract class for each endpoint that you want to run.
+ Multiple HTTP methods are supported, and are intended to be mapped to CRUD operations: POST (create), GET (read), PUT (replace), PATCH (update), DELETE (delete)

## Examples
Generate your RSA keys separately, and supply the keys needed for the features you want on each API.
Create as many endpoints as you like.
#### API
```
// Start the composer autoloader.
require realpath('../vendor/autoload.php');

// Start the API by providing it with the PEM format keys that are needed.
$publicKey = file_get_contents('public.pem');
$privateKey = file_get_contents('private.pem');
$restApi = new \Bdd88\RestApi\Controller\Main($publicKey, $privateKey);

// Create your endpoints by specifying the name(used in the uri) and the class.
$restApi->createEndpoint('companies', '\RestApiExample\Model\Companies');
$restApi->createEndpoint('companies/clients', '\RestApiExample\Model\CompaniesClients');

// Start serving requests.
$restApi->exec();
```

#### Endpoint
The appropriate method is called based on the HTTP method used. (See usage section above).
The httpResponseCode object is an optional way of standardizing API responses. It provides a JSON encoded with three fields:
- code: The integer HTTP response status
- description: The text description of the HTTP response status
- details: Your custom payload/message
```
class CompaniesClients extends \Bdd88\RestApi\Model\EndpointAbstract
{

    protected function create(): string
    {
        // Your code here
        $message = 'Some message here, such as insert id';
        $this->httpResponseCode->set(201, $message);
        header("Location: https://assetsdirector.com/someresource");
        return $this->httpResponseCode;
    }

    protected function read(): string
    {
        // Your code here
        $message = 'Some message here, such as query results';
        $this->httpResponseCode->set(200, $message);
        return $this->httpResponseCode;
    }

    protected function update(): string
    {
        // Your code here
        $message = 'Some message here, such as rows affected';
        $this->httpResponseCode->set(204, $message);
        return $this->httpResponseCode;
    }

    protected function replace(): string
    {
        // Your code here
        $message = 'Some message here, such as rows affected';
        $this->httpResponseCode->set(204, $message);
        return $this->httpResponseCode;
    }

    protected function delete(): string
    {
        // Your code here
        $message = 'Some message here, such as rows affected';
        $this->httpResponseCode->set(204, $message);
        return $this->httpResponseCode;
    }

}
```

#### Token generation
For more information on supported algorithms see the repository for dependency bdd88/jsonwebtoken.
```
$header = array('alg' => 'RS256', 'typ' => 'JWT');
$payload = array('username' => 'guy', 'enabled' => TRUE);
$token = $restApi->createToken($header, $payload);
```

#### PHP Client
Any client that can deliver an HTTP request with JSON payload will work with the API, the following (php client) is simply provided as an example for how a client might be implemented.
```
// Set the URL/Endpoint to query, the HTTP Request type, and the payload to deliver.
$url = "http://api.yoursite.com/companies/9/clients/7";
$requestMethod = 'PUT';
$data = array('test' => TRUE, 'items' => 'things', 'count' => 20);
$token = 'eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VybmFtZSI6Imd1eSIsImVuYWJsZWQiOnRydWV9.OwXe7R0JgfkzmHC9IdqWDorkvCNjeALMnmbahZD-wlwvMWLNJbTVv0WRFwnP1pkMPDj4ghTdGGGUI2Hjdytv9-z3nlrN3QNKvVUHrWGkDMRM-JpNZrcHn27olEXlN6gTYHnMNHLn3BIExrPpSNDiIagCMzWVvMmr8_80kvDQ6YL4tpgtHhCBh_K_BFm6FQu6nQfITzHd0AbR3sKAnRdMjD-98ISMwlwGGO_ye7IEtDwDYmBIc_rgbugJXe2fCZvrPcXnWUHY9Om6T5xfxfsCa9iaZTsqmOyoqI-trEeE3oGSIpc_lQRfSyWoVNB6lBhgPUCvrZx6HVevNWmauN0Ooc7mjLulBCE3p8qrT12LQodKs9ajFsvCxU2VddILGqu-NPXQuuV2Ectvo7ec0w5GO50YxAdZqUCe6nenWPmQ8uJzm0LVsFE5-gXXsNzjkuvO7-4NkPxSrdBKmYcDOWXOR40V_GgOrxZMV-ExW2XEBm7ybjx54NmtTOal1QmE6yHNPVkZmKiUEA0myxbYSF5rDKsjhmtNatl2pvGMcOlMqRYPuQR_5HSkGbl18EOXBqPOfwkVXliApOgYQQ1BjSLo-sS6glvU1AWrlctz_umEqHeeYqLuc0x0co-z0jqV2Z9oiqTNnHbsPdzxnk-uSLj9uSwmpNWVbN_7MMJ6fXlAz6A';
$header = array(
    "Content-Type: application/json",
    "Accept: application/json",
    "Authorization: Bearer " . $token
);

// Prepare CURL to send an HTTP request with the JSON payload.
$curl = curl_init($url);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $requestMethod);
curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($curl, CURLOPT_HTTPHEADER, $header);

// Run the query and store the response.
$response = curl_exec($curl);
$responseCode = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
$contentType = curl_getinfo($curl, CURLINFO_CONTENT_TYPE);
curl_close($curl);

// Echo the API response as it was received.
http_response_code($responseCode);
header('Content-Type: ' . $contentType);
echo $response;
```

#### URL Rewriting
The following is an example of a very simple apache url rewrite to make the API URI 'prettier'.
```
RewriteEngine On
SetEnvIf Authorization "(.*)" HTTP_AUTHORIZATION=$1

# Serve existing files and directories as normal.
RewriteCond %{REQUEST_FILENAME} -f [OR]
RewriteCond %{REQUEST_FILENAME} -d
RewriteRule ^ - [L]

# Serve the API if the requested file or directory does not actually exist.
RewriteRule ^(.+)$ api.php [L]
```