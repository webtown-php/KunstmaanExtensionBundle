<?php

namespace Webtown\KunstmaanExtensionBundle\Entity\PageParts;

use Doctrine\ORM\Mapping as ORM;

/**
 * InsertPagePagePart
 *
 * @ORM\Table(name="webtown_kunstmaanextensionbundle_insert_page_page_parts")
 * @ORM\Entity
 */
class InsertPagePagePart extends \Kunstmaan\PagePartBundle\Entity\AbstractPagePart
{
    /**
     * @var \Kunstmaan\NodeBundle\Entity\Node
     *
     * @ORM\ManyToOne(targetEntity="Kunstmaan\NodeBundle\Entity\Node")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="node_id", referencedColumnName="id")
     * })
     */
    private $node;

    /**
     * Set node
     *
     * @param \Kunstmaan\NodeBundle\Entity\Node $node
     *
     * @return InsertPagePagePart
     */
    public function setNode(\Kunstmaan\NodeBundle\Entity\Node $node = null)
    {
        $this->node = $node;

        return $this;
    }

    /**
     * Get node
     *
     * @return \Kunstmaan\NodeBundle\Entity\Node
     */
    public function getNode()
    {
        return $this->node;
    }

    /**
     * Get the twig view.
     *
     * @return string
     */
    public function getDefaultView()
    {
        return 'WebtownKunstmaanExtensionBundle:PageParts:InsertPagePagePart/view.html.twig';
    }

    /**
     * Get the admin form type.
     *
     * @return \Webtown\KunstmaanExtensionBundle\Form\PageParts\InsertPagePagePartAdminType
     */
    public function getDefaultAdminType()
    {
        return 'webtown_kunstmaan_extension.page_insert.admin_form_type';
    }
}
