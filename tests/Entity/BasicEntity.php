<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2016.05.05.
 * Time: 11:39
 */

namespace Webtown\KunstmaanExtensionBundle\Tests\Entity;

use Doctrine\ORM\Mapping as ORM;
use Kunstmaan\AdminBundle\Entity\AbstractEntity;

/**
 * @ORM\Entity
 * @ORM\Table(name="webtown_kunstmaan_slider_image")
 */
class BasicEntity extends AbstractEntity
{
    /**
     * Name
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false, options={
     *          "comment": "Name"})
     */
    protected $name;

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }
}
