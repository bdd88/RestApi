<?php
namespace Bdd88\RestApi\Controller;

use Bdd88\RestApi\Model\Request;
use Bdd88\JsonWebToken\JwtFactory;

class Login
{
    private Request $request;
    private JwtFactory $jwtFactory;
    private bool|NULL $loggedIn;

    public function __construct(Request $request, JwtFactory $jwtFactory)
    {
        $this->request = $request;
        $this->jwtFactory = $jwtFactory;
        $this->loggedIn = $this->verifyLogin();
    }

    public function verifyLogin(): bool|NULL
    {
        if ($this->request->tokenString === NULL) {
            return NULL;
        }

        $jwt = $this->jwtFactory->import($this->request->tokenString);
        return $jwt->validate();
    }
}

?>
