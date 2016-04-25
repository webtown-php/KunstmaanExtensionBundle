<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2016.03.08.
 * Time: 10:14
 */

namespace Webtown\KunstmaanExtensionBundle\Entity\PageParts;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Kunstmaan\PagePartBundle\Entity\AbstractPagePart;
use Symfony\Component\Form\AbstractType;
use Webtown\KunstmaanExtensionBundle\Entity\SliderImage;
use Webtown\KunstmaanExtensionBundle\Form\PageParts\SliderPagePartAdminType;

/**
 * ImagePagePart
 *
 * @ORM\Entity
 * @ORM\Table(name="webtown_kunstmaan_slider_page_parts")
 */
class SliderPagePart extends AbstractPagePart
{
    /**
     * @var ArrayCollection|SliderImage[] $images
     *
     * @ORM\OneToMany(targetEntity="\Webtown\KunstmaanExtensionBundle\Entity\SliderImage", mappedBy="sliderPagePart", cascade={"persist"})
     * @ORM\OrderBy({"displayOrder" = "ASC"})
     */
    protected $images;

    public function __construct()
    {
        $this->images = new ArrayCollection();
    }

    /**
     * @return ArrayCollection|SliderImage[]
     */
    public function getImages()
    {
        return $this->images;
    }

    /**
     * @param ArrayCollection|SliderImage[] $images
     *
     * @return $this
     */
    public function setImages($images)
    {
        $this->images = $images;

        return $this;
    }

    public function addImage(SliderImage $image)
    {
        $image->setSliderPagePart($this);
        $this->images->add($image);

        return $this;
    }

    public function removeImage(SliderImage $image)
    {
        $this->images->removeElement($image);

        return $this;
    }

    /**
     * Returns the view used in the frontend
     *
     * @return string
     */
    public function getDefaultView()
    {
        return 'WebtownKunstmaanExtensionBundle:PageParts:SliderPagePart/view.html.twig';
    }

    /**
     * Returns the view used in the backend
     *
     * @return string
     */
    public function getAdminView()
    {
        return 'WebtownKunstmaanExtensionBundle:PageParts:SliderPagePart/view-admin.html.twig';
    }

    /**
     * @return AbstractType
     */
    public function getDefaultAdminType()
    {
        return new SliderPagePartAdminType();
    }
}
