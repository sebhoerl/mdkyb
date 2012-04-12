<?php

namespace Mdkyb\WebsiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Datetime;

/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class Download
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
     * @ORM\Column(type="string", length=100)
     */
    private $type;

    /**
     * @ORM\Column(type="string")
     */
    private $description;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="string")
     */
    private $serverFileName;

    /**
     * @ORM\Column(type="string")
     */
    private $mimeType;

    /**
     * @ORM\Column(type="string")
     */
    private $originalFileName;

    /**
     * @Assert\File
     */
    public $file;

    public function __construct()
    {
        $this->createdAt = new Datetime('now');
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

    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function setCreatedAt(Datetime $createdAt)
    {
        $this->createdAt = $createdAt;
    }

    public function getPath()
    {
        return $this->getUploadPath() . '/' . $this->serverFileName;
    }

    public function getMimeType()
    {
        return $this->mimeType;
    }

    public function getServerFileName()
    {
        return $this->serverFileName;
    }

    public function getOriginalFileName()
    {
        return $this->originalFileName;
    }

    public function getDisplayType()
    {
        list($first, $second) = explode('/', $this->mimeType);
        return $second;
    }

    /**
     * Moves an uploaded file to the destination directory
     */
    public function upload()
    {
        if (null !== $this->file) {
            if (isset($this->serverFileName)) {
                unlink($this->getUploadPath() . '/' . $this->serverFileName);
            }

            $this->serverFileName = uniqid() . '.' . $this->file->guessExtension();
            $this->mimeType = $this->file->getMimeType();
            $this->originalFileName = $this->file->getClientOriginalName();

            $this->file->move($this->getUploadPath(), $this->serverFileName);
        }
    }

    /**
     * Removes the file of a removed download
     * 
     * @ORM\PostRemove()
     */
    public function removeUpload()
    {
        unlink($this->getUploadPath() . '/' . $this->serverFileName);
    }

    protected function getUploadPath()
    {
        return __DIR__ . '/../../../../uploads';
    }
}
