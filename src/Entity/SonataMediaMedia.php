<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sonata\MediaBundle\Entity\BaseMedia;
use Sonata\ClassificationBundle\Model\CategoryInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="media__media")
 * @method void setCategory(?CategoryInterface $category)
 */
class SonataMediaMedia extends BaseMedia
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected $id;

    public function getId()
    {
        return $this->id;
    }

}