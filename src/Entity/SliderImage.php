<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2016.03.08.
 * Time: 10:15
 */

namespace Webtown\KunstmaanExtensionBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Kunstmaan\AdminBundle\Entity\AbstractEntity;
use Kunstmaan\MediaBundle\Entity\Media;
use Symfony\Component\Validator\Constraints as Assert;
use Webtown\KunstmaanExtensionBundle\Entity\PageParts\SliderPagePart;

/**
 * ImagePagePart
 *
 * @ORM\Entity
 * @ORM\Table(name="webtown_kunstmaan_slider_image")
 */
class SliderImage extends AbstractEntity
{
    /**
     * @var SliderPagePart $sliderPagePart
     *
     * @ORM\ManyToOne(targetEntity="\Webtown\KunstmaanExtensionBundle\Entity\PageParts\SliderPagePart", inversedBy="images", cascade={"persist"})
     * @ORM\JoinColumn(name="slider_page_part_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $sliderPagePart;

    /**
     * @var Media
     *
     * @ORM\ManyToOne(targetEntity="Kunstmaan\MediaBundle\Entity\Media")
     * @ORM\JoinColumn(name="media_id", referencedColumnName="id")
     * @Assert\NotNull()
     */
    private $media;

    /**
     * @var string
     *
     * @ORM\Column(type="string", name="caption_title", nullable=true)
     */
    private $captionTitle;

    /**
     * @var string
     *
     * @ORM\Column(type="string", name="caption", nullable=true)
     */
    private $caption;

    /**
     * @var string
     *
     * @ORM\Column(type="string", name="alt_text", nullable=true)
     */
    private $altText;

    /**
     * @var string
     *
     * @ORM\Column(name="link", type="string", nullable=true)
     */
    private $link;

    /**
     * @var bool
     *
     * @ORM\Column(name="open_in_new_window", type="boolean", nullable=true)
     */
    private $openInNewWindow;

    /**
     * Sorting
     *
     * @var int $displayOrder
     *
     * @ORM\Column(name="display_order", type="integer", nullable=false, options={
     *          "unsigned": true,
     *          "comment": "Sorting"})
     */
    protected $displayOrder;

    /**
     * @return SliderPagePart
     */
    public function getSliderPagePart()
    {
        return $this->sliderPagePart;
    }

    /**
     * @param SliderPagePart $sliderPagePart
     *
     * @return $this
     */
    public function setSliderPagePart(SliderPagePart $sliderPagePart)
    {
        $this->sliderPagePart = $sliderPagePart;

        return $this;
    }

    /**
     * @return Media
     */
    public function getMedia()
    {
        return $this->media;
    }

    /**
     * @param Media $media
     *
     * @return $this
     */
    public function setMedia(Media $media)
    {
        $this->media = $media;

        return $this;
    }

    /**
     * @return string
     */
    public function getCaptionTitle()
    {
        return $this->captionTitle;
    }

    /**
     * @param string $captionTitle
     *
     * @return $this
     */
    public function setCaptionTitle($captionTitle)
    {
        $this->captionTitle = $captionTitle;

        return $this;
    }

    /**
     * @return string
     */
    public function getCaption()
    {
        return $this->caption;
    }

    /**
     * @param string $caption
     *
     * @return $this
     */
    public function setCaption($caption)
    {
        $this->caption = $caption;

        return $this;
    }

    /**
     * @return string
     */
    public function getAltText()
    {
        return $this->altText;
    }

    /**
     * @param string $altText
     *
     * @return $this
     */
    public function setAltText($altText)
    {
        $this->altText = $altText;

        return $this;
    }

    /**
     * @return string
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * @param string $link
     *
     * @return $this
     */
    public function setLink($link)
    {
        $this->link = $link;

        return $this;
    }

    /**
     * @return bool
     */
    public function getOpenInNewWindow()
    {
        return $this->openInNewWindow;
    }

    /**
     * @param bool $openInNewWindow
     *
     * @return $this
     */
    public function setOpenInNewWindow($openInNewWindow)
    {
        $this->openInNewWindow = $openInNewWindow;

        return $this;
    }

    /**
     * @return int
     */
    public function getDisplayOrder()
    {
        return $this->displayOrder;
    }

    /**
     * @param int $displayOrder
     *
     * @return $this
     */
    public function setDisplayOrder($displayOrder)
    {
        $this->displayOrder = $displayOrder;

        return $this;
    }
}
