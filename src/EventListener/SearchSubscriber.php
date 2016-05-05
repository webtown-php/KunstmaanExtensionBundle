<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2016.05.03.
 * Time: 17:14
 */

namespace Webtown\KunstmaanExtensionBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Webtown\KunstmaanExtensionBundle\Entity\SearchableEntityInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class SearchSubscriber implements EventSubscriber
{
    /**
     * @var ContainerInterface $container
     */
    protected $container;

    /**
     * Azért `Container`-t kap, hogy a configuration ne inicializálódjon, ha nem muszáj.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2')))
     *
     * @return array The event names to listen to
     */
    public function getSubscribedEvents()
    {
        return [
            Events::postPersist => 'postPersist',
            Events::postUpdate => 'postUpdate',
            Events::postRemove => 'postRemove',
        ];
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        if ($args->getObject() instanceof SearchableEntityInterface) {
            $this->index($args->getObject());
        }
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        if ($args->getObject() instanceof SearchableEntityInterface) {
            $this->index($args->getObject());
        }
    }

    public function postRemove(LifecycleEventArgs $args)
    {
        if ($args->getObject() instanceof SearchableEntityInterface) {
            $this->delete($args->getObject());
        }
    }

    /**
     * @param SearchableEntityInterface $entity
     */
    protected function index(SearchableEntityInterface $entity)
    {
        $configuration = $this->container->get('webtown_kunstmaan_extension.searchable_entity_configuration');
        $configuration->indexEntity($entity);
    }

    /**
     * @param SearchableEntityInterface $entity
     */
    protected function delete(SearchableEntityInterface $entity)
    {
        $configuration = $this->container->get('webtown_kunstmaan_extension.searchable_entity_configuration');
        $configuration->deleteEntity($entity);
    }
}
