<?php
namespace Bdd88\RestApi\Controller;

use Bdd88\RestApi\Model\Request;
use Bdd88\JsonWebToken\JwtFactory;

class Login
{
    private Request $request;
    private JwtFactory $jwtFactory;

    public function __construct(Request $request, JwtFactory $jwtFactory)
    {
        $this->request = $request;
        $this->jwtFactory = $jwtFactory;
    }

    /**
     * Verify the login status by importing and validating the JWT.
     *
     * @return TRUE|NULL|string TRUE if login is good. NULL if no token was provided. String containing error if the token failed validation.
     */
    public function verify(): bool|NULL|string
    {
        if ($this->request->tokenString === NULL) {
            return NULL;
        }

        $jwt = $this->jwtFactory->import($this->request->tokenString);
        return $jwt->validate();
    }
}

?>
