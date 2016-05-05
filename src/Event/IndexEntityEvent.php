<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2016.05.04.
 * Time: 13:51
 */

namespace Webtown\KunstmaanExtensionBundle\Event;


use Webtown\KunstmaanExtensionBundle\Entity\SearchableEntityInterface;
use Symfony\Component\EventDispatcher\Event;

class IndexEntityEvent extends Event
{
    const EVENT_NAME = 'wt_kuma_extension.search_index_event';

    /**
     * @var SearchableEntityInterface
     */
    protected $entity;

    /**
     * @var array
     */
    protected $doc;

    /**
     * IndexEntityEvent constructor.
     * @param SearchableEntityInterface $entity
     * @param array $doc
     */
    public function __construct(SearchableEntityInterface $entity, array $doc)
    {
        $this->entity = $entity;
        $this->doc = $doc;
    }

    /**
     * @return SearchableEntityInterface
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * @return array
     */
    public function getDoc()
    {
        return $this->doc;
    }

    /**
     * @param array $doc
     *
     * @return $this
     */
    public function setDoc($doc)
    {
        $this->doc = $doc;

        return $this;
    }
}
