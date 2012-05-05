<?php

namespace Mdkyb\WebsiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Represents a membership application
 * 
 * @ORM\Entity
 */
class MembershipApplication
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="boolean")
     */
    private $supporting;

    /**
     * @ORM\Column(type="boolean")
     */
    private $student;

    /**
     * @ORM\Column(type="boolean")
     */
    private $male;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $surename;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $lastname;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $street;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $postalCode;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $city;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $phone;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $comment;

    public function getId()
    {
        return $this->id;
    }

    public function isSupporting()
    {
        return $this->supporting;
    }

    public function setSupporting($supporting)
    {
        $this->supporting = $supporting;
    }

    public function isStudent()
    {
        return $this->student;
    }

    public function setStudent($student)
    {
        $this->student = $student;
    }

    public function isMale()
    {
        return $this->male;
    }

    public function setMale($male)
    {
        $this->male = $male;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getSurename()
    {
        return $this->surename;
    }

    public function setSurename($surename)
    {
        $this->surename = $surename;
    }

    public function getLastname()
    {
        return $this->lastname;
    }

    public function setLastname($lastname)
    {
        $this->lastname = $lastname;
    }

    public function getStreet()
    {
        return $this->street;
    }

    public function setStreet($street)
    {
        $this->street = $street;
    }

    public function getPostalCode()
    {
        return $this->postalCode;
    }

    public function setPostalCode($postalCode)
    {
        $this->postalCode = $postalCode;
    }

    public function getCity()
    {
        return $this->city;
    }

    public function setCity($city)
    {
        $this->city = $city;
    }

    public function getPhone()
    {
        return $this->phone;
    }

    public function setPhone($phone)
    {
        $this->phone = $phone;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function getComment()
    {
        return $this->comment;
    }

    public function setComment($comment)
    {
        $this->comment = $comment;
    }
}
