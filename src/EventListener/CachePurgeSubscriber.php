<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2016.04.20.
 * Time: 9:34
 */

namespace Webtown\KunstmaanExtensionBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use Kunstmaan\AdminBundle\Entity\AbstractEntity;
use Kunstmaan\NodeBundle\Entity\PageInterface;
use Kunstmaan\PagePartBundle\Helper\PagePartInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Webtown\KunstmaanExtensionBundle\HttpCache\Manager;

class CachePurgeSubscriber implements EventSubscriber
{
    /**
     * @var Manager
     */
    protected $httpCacheManager;

    /**
     * @var Session|null
     */
    protected $session;

    /**
     * CachePurgeSubscriber constructor.
     * @param Manager $httpCacheManager
     * @param Session $session
     */
    public function __construct(Manager $httpCacheManager, Session $session = null)
    {
        $this->httpCacheManager = $httpCacheManager;
        $this->session = $session;
    }

    /**
     * Returns an array of events this subscriber wants to listen to.
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            Events::onFlush => 'onFlush',
        ];
    }

    public function onFlush(OnFlushEventArgs $args)
    {
        if ($this->checkNeedPurge($args)) {
            try {
                $this->httpCacheManager->forcePurgeAll();
            } catch (\Exception $e) {
                if (!$this->session) {
                    throw $e;
                }

                // The Translator throws CircularReferenceException!
                $this->session->getFlashBag()->add('error', $e->getMessage());
            }
        }
    }

    protected function checkNeedPurge(OnFlushEventArgs $args)
    {
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();
        foreach ($uow->getScheduledEntityInsertions() as $insertedObject) {
            if ($this->checkEntity($insertedObject)) {
                return true;
            }
        }
        foreach ($uow->getScheduledEntityDeletions() as $deletedObject) {
            if ($this->checkEntity($deletedObject)) {
                return true;
            }
        }
        foreach ($uow->getScheduledEntityUpdates() as $updatedObject) {
            if ($this->checkEntity($updatedObject)) {
                return true;
            }
        }

        return false;
    }

    protected function checkEntity($entity)
    {
        if ($entity instanceof PageInterface) {
            return true;
        }
        if ($entity instanceof PagePartInterface) {
            return true;
        }
        if ($entity instanceof AbstractEntity) {
            return true;
        }

        return false;
    }
}
