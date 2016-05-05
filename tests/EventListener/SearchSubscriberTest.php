<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2016.05.05.
 * Time: 11:03
 */

namespace Webtown\KunstmaanExtensionBundle\Tests\EventListener;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Mockery as m;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Webtown\KunstmaanExtensionBundle\Configuration\SearchableEntityConfiguration;
use Webtown\KunstmaanExtensionBundle\EventListener\SearchSubscriber;
use Webtown\KunstmaanExtensionBundle\Tests\Entity\BasicEntity;
use Webtown\KunstmaanExtensionBundle\Tests\Entity\SearchableEntity;

class SearchSubscriberTest extends \PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function testGetSubscribedEvents()
    {
        /** @var ContainerInterface $containerMock */
        $containerMock = m::mock(ContainerInterface::class);
        $searchSubscriber = new SearchSubscriber($containerMock);
        $subscribedEvents = $searchSubscriber->getSubscribedEvents();
        $events = array_keys($subscribedEvents);
        $shouldEvents = [
            Events::postPersist,
            Events::postUpdate,
            Events::postRemove,
        ];
        asort($events);
        asort($shouldEvents);

        $this->assertEquals($shouldEvents, $events);
        foreach ($events as $event=>$method) {
            $this->assertTrue(method_exists($searchSubscriber, $method), sprintf('Missing method: `%s`', $method));
        }
    }

    /**
     * @param string $method
     * @param mixed $entity
     * @param null $callMethod
     *
     * @dataProvider getPostMethodTests
     */
    public function testPostMethod($method, $entity, $callMethod = null)
    {
        /** @var ObjectManager $objectManagerMock */
        $objectManagerMock = m::mock(ObjectManager::class);
        $args = new LifecycleEventArgs($entity, $objectManagerMock);
        $configuration = m::mock(SearchableEntityConfiguration::class);
        if ($callMethod) {
            $configuration->shouldReceive($callMethod)->with($entity)->once();
        }
        /** @var ContainerInterface $containerMock */
        $containerMock = m::mock(ContainerInterface::class, [
            'get' => $configuration,
        ]);
        $searchSubscriber = new SearchSubscriber($containerMock);

        $searchSubscriber->$method($args);
    }

    public function getPostMethodTests()
    {
        return [
            ['postPersist', new BasicEntity(), null],
            ['postPersist', new SearchableEntity(), 'indexEntity'],
            ['postUpdate', new BasicEntity(), null],
            ['postUpdate', new SearchableEntity(), 'indexEntity'],
            ['postRemove', new BasicEntity(), null],
            ['postRemove', new SearchableEntity(), 'deleteEntity'],
        ];
    }
}
