<?php

namespace Mdkyb\WebsiteBundle\Model;

/**
 * Model for the registration process
 */
class Registration
{
    private $password;

    public function getPassword()
    {
        return $this->password;
    }

    public function setPassword($password)
    {
        $this->password = $password;
    }
}
