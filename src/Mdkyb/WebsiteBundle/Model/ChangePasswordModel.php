<?php

namespace Mdkyb\WebsiteBundle\Model;

/**
 * Model for the "change password form"
 */
class ChangePasswordModel
{
    private $oldPassword;
    private $newPassword;

    public function getNewPassword()
    {
        return $this->newPassword;
    }

    public function setNewPassword($password)
    {
        $this->newPassword = $password;
    }

    public function getOldPassword()
    {
        return $this->oldPassword;
    }

    public function setOldPassword($password)
    {
        $this->oldPassword = $password;
    }
}
