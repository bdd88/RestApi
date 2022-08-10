<?php
namespace Bdd88\RestApi\Model;


/** Custom version of \Bdd88\MySql\MySql that uses the ConfigDatabase object and DI instead of manually written arguments for the database credentials. */
class MySql extends \Bdd88\MySql\MySql
{
    public function __construct(ConfigDatabase $databaseConfig)
    {
        list($this->hostname, $this->username, $this->password, $this->database) = $databaseConfig->getAllSettings();
        $this->connect();
    }

}

?>
