<?php

namespace Mdkyb\WebsiteBundle\Model;

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
