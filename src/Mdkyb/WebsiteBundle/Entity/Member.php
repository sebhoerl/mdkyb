<?php

namespace Mdkyb\WebsiteBundle\Entity;

use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 */
class Member implements UserInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $name;

    /**
     * @ORM\Column(type="string")
     */
    private $info;

    /**
     * @ORM\Column(type="integer")
     */
    private $function;

    /**
     * @ORM\Column(type="string", length=40)
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=32)
     */
    private $salt;

    /**
     * @ORM\Column(type="array")
     * @Assert\Choice(choices={
     *     "ROLE_ADMIN", "ROLE_WEB", "ROLE_MEMBER"
      * }, multiple = true)
     */
    private $roles = array();

    /**
     * @ORM\Column(type="string", length=32)
     */
    private $registrationKey;

    /**
     * @ORM\Column(type="integer")
     */
    private $forumId = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $wikiId = 0;

    /**
     * @ORM\Column(type="boolean")
     */
    private $paid;

    /**
     * @ORM\OneToOne(targetEntity="Image")
     */
    private $image;

    public function __construct()
    {
        $this->salt = md5(uniqid(mt_rand(), true));
        $this->registrationKey = '';
    }

    public function getId(){
        return $this->id;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * Neccesary for UserInterface
     */
    public function getUsername()
    {
        return $this->email;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function setPassword($password)
    {
        $this->password = $password;
    }

    public function getSalt()
    {
        return $this->salt;
    }

    public function setSalt($salt)
    {
        $this->salt = $salt;
    }

    public function getForumId()
    {
        return $this->forumId;
    }

    public function setForumId($id)
    {
        $this->forumId = $id;
    }

    public function getWikiId()
    {
        return $this->wikiId;
    }

    public function setWikiId($id)
    {
        $this->wikiId = $id;
    }

    public function eraseCredentials()
    {}

    public function equals(UserInterface $user)
    {
        return $this->email == $user->getEmail();
    }

    public function getRoles()
    {
        return $this->roles;
    }

    public function setRoles(array $roles)
    {
        $this->roles = $roles;
    }

    public function getRegistrationKey()
    {
        return $this->registrationKey;
    }

    public function setRegistrationKey($registrationKey)
    {
        $this->registrationKey = $registrationKey;
    }

    public function setPaid($paid)
    {
        $this->paid = $paid;
    }

    public function getPaid()
    {
        return $this->paid;
    }

    /**
     * Generates a new registration key
     */
    public function generateKey()
    {
        $this->registrationKey = md5(uniqid());
    }

    public function getInfo()
    {
        return $this->info;
    }

    public function setInfo($info)
    {
        $this->info = $info;
    }

    public function getFunction()
    {
        return $this->function;
    }

    public function setFunction($function)
    {
        $this->function = $function;
    }

    public function setImage(Image $image = null)
    {
        $this->image = $image;
    }

    public function getImage()
    {
        return $this->image;
    }

    public function __sleep()
    {
        $properties = get_object_vars($this); 
        unset($properties['image']); 
        return array_keys($properties);
    }
}
