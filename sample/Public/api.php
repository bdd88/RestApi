<?php

// This is an example script for implementing the REST API.

// Load necessary source files manually or with an autoloader.
// This example is using a lazy autoloader to load files as needed (https://github.com/bdd88/AutoLoader).
$rootDir = realpath('../..');
require $rootDir . '/sample/Model/AutoLoader.php';
$autoloader = new \bdd88\AutoLoader\AutoLoader();
$autoloader->register('\ExampleProject', $rootDir . '/sample');
$autoloader->register('\bdd88\RestApi', $rootDir . '/src');
spl_autoload_register(array($autoloader, 'load'));

// Create an instance of the API and specify the config file path.
$aPhpApi = new \bdd88\RestApi\Controller\Main($rootDir . '/sample/config/database.ini', TRUE);

// Create one or more endpoints by supplying the desired endpoint name and the fully qualified class name.
$aPhpApi->createEndpoint('companies', '\ExampleProject\Model\Companies');
$aPhpApi->createEndpoint('companies/clients', '\ExampleProject\Model\CompaniesClients');

// Start the endpoints by supplying a URI string from the client.
$aPhpApi->exec($_SERVER['REQUEST_URI']);
//$aPhpApi->exec('https://api.mywebsite.com/companies/9/clients/4');

// You would access the endpoints in this example by using GET/POST/UPDATE/PATCH/DELETE at the following addresses:
// https://api.mywebsite.com/companies/{companyID}
// https://api.mywebsite.com/companies/{companyID}/clients/{clientID}

?>