<?php
namespace Bdd88\RestApi\Model;

/** Loads and validates the database configuration file. */
class ConfigDatabase extends ConfigAbstract
{
    /** Validate that necessary settings are present and correctly typed in the configuration file. */
    protected function validate(): void
    {
        $this->verifySettingsAreSet(array(
            'hostname',
            'database',
            'username',
            'password'
        ));
        $this->verifySettingsType('string', array(
            'hostname',
            'database',
            'username',
            'password'
        ));
    }
}

?>
