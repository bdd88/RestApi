<?php

// This is a example script of an API client.

// Build the URL to query the API. In this example the API resides in the same directory on the same website as this script.
$uriRequest = explode('/', $_SERVER["REQUEST_URI"]);
array_pop($uriRequest);
$urlPartial = $_SERVER["REQUEST_SCHEME"] . '://' . $_SERVER["HTTP_HOST"] . implode('/', $uriRequest);

// Set the URL/Endpoint to query, the HTTP Request type, and the payload to deliver.
$url = $urlPartial . "/companies/9/clients/7";
$requestMethod = 'PUT';
$data = array('test' => TRUE, 'items' => 'things', 'count' => 20);

// Prepare CURL to send an HTTP request with the JSON payload.
$curl = curl_init($url);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $requestMethod);
curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));

// Run the query and store the response.
$response = curl_exec($curl);
$responseCode = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
$contentType = curl_getinfo($curl, CURLINFO_CONTENT_TYPE);
curl_close($curl);

// Output the response exactly as the API sent it.
http_response_code($responseCode);
header('Content-Type: ' . $contentType);
echo $response;

?>
