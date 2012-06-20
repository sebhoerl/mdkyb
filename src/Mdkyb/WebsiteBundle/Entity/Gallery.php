<?php

namespace Mdkyb\WebsiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Datetime;

/**
 * @ORM\Entity
 */
class Gallery
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\OneToMany(targetEntity="Image", mappedBy="gallery")
     */
    private $images;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    public function __construct()
    {
        $this->images = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getImages()
    {
        return $this->images;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function __toString()
    {
        return $this->title;
    }
}
