<?php

namespace Mdkyb\WebsiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Datetime;

/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class Image
{
    const THUMBNAIL_SIZE = 120;

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
     * @ORM\ManyToOne(targetEntity="Gallery", inversedBy="images")
     * @ORM\JoinColumn(name="image_id", referencedColumnName="id")
     */
    private $gallery;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $filename;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $thumbname;

    /**
     * @Assert\File
     */
    public $file;

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

    public function getGallery()
    {
        return $this->gallery;
    }

    public function setGallery(Gallery $gallery)
    {
        $this->gallery = $gallery;
    }

    public function setFilename($filename)
    {
        $this->filename = $filename;
    }

    public function getFilename()
    {
        return $this->filename;
    }

    public function setThumbname($thumbname)
    {
        $this->thumbname = $thumbname;
    }

    public function getThumbname()
    {
        return $this->thumbname;
    }

    public function getPath()
    {
        return $this->getUploadPath() . '/' . $this->filename;
    }

    public function getThumbPath()
    {
        return $this->getUploadPath() . '/' . $this->thumbname;
    }

    public function upload()
    {
        if (null !== $this->file) {
            if (isset($this->filename)) {
                unlink($this->getUploadPath() . '/' . $this->filename);
                unlink($this->getUploadPath() . '/' . $this->thumbname);
            }

            $id = uniqid();
            $extension = $this->file->guessExtension();
            $this->filename =  $id . '_image.' . $extension;
            $this->thumbname = $id . '_thumb.png';

            $this->file->move($this->getUploadPath(), $this->filename);

            $size = static::THUMBNAIL_SIZE;
            $path = $this->getUploadPath() . '/' . $this->filename;
            
            switch ($extension) {
                case 'jpg':
                case 'jpeg':
                    $image = imagecreatefromjpeg($path);
                    break;
                case 'png':
                    $image = imagecreatefrompng($path);
                    break;
                case 'gif':
                    $image = imagecreatefromgif($path);
                    break;
                default:
                    $image = imagecreatetruecolor($size, $size);
            }

            $width = imagesx($image);
            $height = imagesy($image);
            $shorter = min($width, $height);

            $thumbnail = imagecreatetruecolor($size, $size);
            imagecopyresampled($thumbnail, $image, 0, 0, 0, 0, $size, $size, $shorter, $shorter);
            imagepng($thumbnail, $this->getUploadPath() . '/' . $this->thumbname);
        }
    }

    /**
     * @ORM\PostRemove()
     */
    public function removeUpload()
    {
        unlink($this->getUploadPath() . '/' . $this->filename);
        unlink($this->getUploadPath() . '/' . $this->thumbname);
    }

    protected function getUploadPath()
    {
        return __DIR__ . '/../../../../web/uploads';
    }
}
