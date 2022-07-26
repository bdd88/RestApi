<?php
namespace ExampleProject\Model;

use bdd88\RestApi\Model\EndpointAbstract;

class CompaniesUsers extends EndpointAbstract
{

    protected function create(): string
    {
        $this->displayInfo(__METHOD__);
        return '';
    }

    protected function read(): string
    {
        $this->displayInfo(__METHOD__);
        return '';
    }

    protected function update(): string
    {
        $this->displayInfo(__METHOD__);
        return '';
    }

    protected function replace(): string
    {
        $this->displayInfo(__METHOD__);
        return '';
    }

    protected function delete(): string
    {
        $this->displayInfo(__METHOD__);
        return '';
    }

    private function displayInfo(string $methodName): void
    {
        echo 'HTTP method: ' . $this->requestMethod . '<br>';
        echo 'Class method: ' . $methodName . '<br>';
        echo 'Endpoint name: ' . $this->endpointName . '<br>';
        echo 'Endpoint values: <br>';
        var_dump($this->endpointValues);
        echo '<br>Payload: <br>';
        var_dump($this->query);
    }

}

?>