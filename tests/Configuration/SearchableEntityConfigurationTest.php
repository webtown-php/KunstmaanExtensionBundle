<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2016.05.05.
 * Time: 11:54
 */

namespace Webtown\KunstmaanExtensionBundle\Tests\Configuration;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;
use Elastica\Document;
use Kunstmaan\AdminBundle\Helper\DomainConfiguration;
use Kunstmaan\SearchBundle\Provider\ElasticaProvider;
use Kunstmaan\SearchBundle\Provider\SearchProviderChain;
use Kunstmaan\SearchBundle\Search\Search;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Webtown\KunstmaanExtensionBundle\Configuration\SearchableEntityConfiguration;
use Webtown\KunstmaanExtensionBundle\Event\IndexEntityEvent;
use Webtown\KunstmaanExtensionBundle\Tests\Entity\OtherSearchableEntity;
use Webtown\KunstmaanExtensionBundle\Tests\Entity\SearchableEntity;
use Webtown\KunstmaanExtensionBundle\Tests\Traits\PhpUnitTrait;
use Mockery as m;


class SearchableEntityConfigurationTest extends \PHPUnit_Framework_TestCase
{
    use PhpUnitTrait;

    public function tearDown()
    {
        m::close();
    }

    public function testCreateIndex()
    {
        $searchableEntityConfiguration = $this->buildConfigurationObject(
            ['en', 'hu'],
            function (m\MockInterface $elasticaProviderMock) {
                $elasticaProviderMock
                    ->shouldReceive('createIndex')
                    ->never()
                ;
            },
            function (m\MockInterface $emMock) {
                $emMock
                    ->shouldReceive('getRepository')
                    ->never()
                ;
            }
        );

        $searchableEntityConfiguration->createIndex();
    }

    public function testDeleteIndex()
    {
        $searchableEntityConfiguration = $this->buildConfigurationObject(
            ['en', 'hu'],
            function (m\MockInterface $elasticaProviderMock) {
                $elasticaProviderMock
                    ->shouldReceive('deleteIndex')
                    ->never()
                ;
            },
            function (m\MockInterface $emMock) {
                $emMock
                    ->shouldReceive('getRepository')
                    ->never()
                ;
            }
        );

        $searchableEntityConfiguration->deleteIndex();
    }

    /**
     * @param $text
     * @param $response
     *
     * @dataProvider getHtmlTexts
     */
    public function testRemoveHtml($text, $response)
    {
        $searchableEntityConfiguration = $this->buildConfigurationObject();

        $this->assertEquals($response, $this->callObjectProtectedMethod($searchableEntityConfiguration, 'removeHtml', [$text]));
    }

    public function getHtmlTexts()
    {
        return [
            ['', ''],
            ['Title 1', 'Title 1'],
            ['árvíztűrőtükörfúrógép', 'árvíztűrőtükörfúrógép'],
            ['<b>árvíztűrőtükörfúrógép</b>', 'árvíztűrőtükörfúrógép'],
            ['This <u>is a</u> <b>test</b> <i>message</i>', 'This is a test message'],
            ['This <u>is a</u> <b>&lt;test&gt;</b> <i>message</i>', 'This is a <test> message'],
        ];
    }

    /**
     * @param $entity
     * @param $locale
     * @param $response
     *
     * @dataProvider getDocUids
     */
    public function testGetDocUid($entity, $locale, $response)
    {
        $searchableEntityConfiguration = $this->buildConfigurationObject();

        $this->assertEquals($response, $this->callObjectProtectedMethod($searchableEntityConfiguration, 'getDocUid', [$entity, $locale]));
    }

    public function getDocUids()
    {
        return [
            [new SearchableEntity(1), 'en', 'searchable_entity.searchable_test_entity.1#en'],
            [new OtherSearchableEntity(2), 'en', 'searchable_entity.other_searchable_test_entity.2#en'],
            [new SearchableEntity(3), 'hu', 'searchable_entity.searchable_test_entity.3#hu'],
            [new OtherSearchableEntity(4), 'hu', 'searchable_entity.other_searchable_test_entity.4#hu'],
        ];
    }

    /**
     * @param $entity
     * @param $locale
     * @param $resultDoc
     *
     * @dataProvider getIndexingEntities
     */
    public function testAddEntityToIndex($entity, $locale, Document $resultDoc)
    {
        $searchableEntityConfiguration = $this->buildConfigurationObject(
            ['en', 'hu'],
            function (m\MockInterface $elasticaProviderMock) use ($resultDoc) {
                $elasticaProviderMock
                    ->shouldReceive('createDocument')
                    ->with(
                        $resultDoc->getId(),
                        $resultDoc->getData(),
                        $resultDoc->getType(),
                        $resultDoc->getIndex()
                    )
                    ->once()
                    ->andReturn($resultDoc)
                ;
            },
            null,
            function (m\MockInterface $eventDispatcherMock) use ($resultDoc) {
                $eventDispatcherMock
                    ->shouldReceive('dispatch')
                    ->with(m::on(function ($name) {
                        return $name == IndexEntityEvent::EVENT_NAME;
                    }), m::on(function ($event) use ($resultDoc) {
                        return $event instanceof IndexEntityEvent
                            && $resultDoc->getData() == $event->getDoc();
                    }))
                    ->andReturn($resultDoc)
                ;
            }
        );

        $documents = $this->getObjectProtectedAttribute($searchableEntityConfiguration, 'documents');
        $this->assertCount(0, $documents);
        $this->callObjectProtectedMethod($searchableEntityConfiguration, 'addEntityToIndex', [$entity, $locale]);
        $documents = $this->getObjectProtectedAttribute($searchableEntityConfiguration, 'documents');
        $this->assertCount(1, $documents);
        $doc = array_pop($documents);
        $this->assertEqualDocuments($resultDoc, $doc);
    }

    public function getIndexingEntities()
    {
        $entity1 = new SearchableEntity(1);
        $entity1->setName('Test 1');
        $entity2 = new OtherSearchableEntity(2);
        $entity2->setName('Test 2');

        $htmlEntity1 = new SearchableEntity(3);
        $htmlEntity1->setName('Test <b>3</b>');
        $htmlEntity2 = new OtherSearchableEntity(4);
        $htmlEntity2->setName('&lt;Test&gt; <b>4</b>');

        return [
            [clone $entity1, 'en', new Document(
                'searchable_entity.searchable_test_entity.1#en',
                [
                    'title'               => 'Test 1',
                    'lang'                => 'en',
                    'route_name'          => 'search_entity_show',
                    'route_parameters'    => ['id' => 1],
                    'entity'              => SearchableEntity::class,
                    'view_roles'          => ['IS_AUTHENTICATED_ANONYMOUSLY'],
                    'content'             => 'Test 1',
                    'contentanalyzer'     => 'english',
                    '_boost'              => 1.0
                ],
                'index_prefixtest_name',
                'test_type_en'
            )],
            [clone $entity1, 'hu', new Document(
                'searchable_entity.searchable_test_entity.1#hu',
                [
                    'title'               => 'Test 1',
                    'lang'                => 'hu',
                    'route_name'          => 'search_entity_show',
                    'route_parameters'    => ['id' => 1],
                    'entity'              => SearchableEntity::class,
                    'view_roles'          => ['IS_AUTHENTICATED_ANONYMOUSLY'],
                    'content'             => 'Test 1',
                    'contentanalyzer'     => 'hungarian',
                    '_boost'              => 1.0
                ],
                'index_prefixtest_name',
                'test_type_hu'
            )],
            [clone $entity2, 'en', new Document(
                'searchable_entity.other_searchable_test_entity.2#en',
                [
                    'title'               => 'Test 2',
                    'lang'                => 'en',
                    'route_name'          => 'other_search_entity_show',
                    'route_parameters'    => ['id' => 2],
                    'entity'              => OtherSearchableEntity::class,
                    'view_roles'          => ['IS_AUTHENTICATED_ANONYMOUSLY'],
                    'content'             => 'Test 2',
                    'contentanalyzer'     => 'english',
                    '_boost'              => 3.0
                ],
                'index_prefixtest_name',
                'test_type_en'
            )],
            [clone $entity2, 'hu', new Document(
                'searchable_entity.other_searchable_test_entity.2#hu',
                [
                    'title'               => 'Test 2',
                    'lang'                => 'hu',
                    'route_name'          => 'other_search_entity_show',
                    'route_parameters'    => ['id' => 2],
                    'entity'              => OtherSearchableEntity::class,
                    'view_roles'          => ['IS_AUTHENTICATED_ANONYMOUSLY'],
                    'content'             => 'Test 2',
                    'contentanalyzer'     => 'hungarian',
                    '_boost'              => 3.0
                ],
                'index_prefixtest_name',
                'test_type_hu'
            )],

            [clone $htmlEntity1, 'en', new Document(
                'searchable_entity.searchable_test_entity.3#en',
                [
                    'title'               => 'Test <b>3</b>',
                    'lang'                => 'en',
                    'route_name'          => 'search_entity_show',
                    'route_parameters'    => ['id' => 3],
                    'entity'              => SearchableEntity::class,
                    'view_roles'          => ['IS_AUTHENTICATED_ANONYMOUSLY'],
                    'content'             => 'Test 3',
                    'contentanalyzer'     => 'english',
                    '_boost'              => 1.0
                ],
                'index_prefixtest_name',
                'test_type_en'
            )],
            [clone $htmlEntity1, 'hu', new Document(
                'searchable_entity.searchable_test_entity.3#hu',
                [
                    'title'               => 'Test <b>3</b>',
                    'lang'                => 'hu',
                    'route_name'          => 'search_entity_show',
                    'route_parameters'    => ['id' => 3],
                    'entity'              => SearchableEntity::class,
                    'view_roles'          => ['IS_AUTHENTICATED_ANONYMOUSLY'],
                    'content'             => 'Test 3',
                    'contentanalyzer'     => 'hungarian',
                    '_boost'              => 1.0
                ],
                'index_prefixtest_name',
                'test_type_hu'
            )],
            [clone $htmlEntity2, 'en', new Document(
                'searchable_entity.other_searchable_test_entity.4#en',
                [
                    'title'               => '&lt;Test&gt; <b>4</b>',
                    'lang'                => 'en',
                    'route_name'          => 'other_search_entity_show',
                    'route_parameters'    => ['id' => 4],
                    'entity'              => OtherSearchableEntity::class,
                    'view_roles'          => ['IS_AUTHENTICATED_ANONYMOUSLY'],
                    'content'             => '<Test> 4',
                    'contentanalyzer'     => 'english',
                    '_boost'              => 3.0
                ],
                'index_prefixtest_name',
                'test_type_en'
            )],
            [clone $htmlEntity2, 'hu', new Document(
                'searchable_entity.other_searchable_test_entity.4#hu',
                [
                    'title'               => '&lt;Test&gt; <b>4</b>',
                    'lang'                => 'hu',
                    'route_name'          => 'other_search_entity_show',
                    'route_parameters'    => ['id' => 4],
                    'entity'              => OtherSearchableEntity::class,
                    'view_roles'          => ['IS_AUTHENTICATED_ANONYMOUSLY'],
                    'content'             => '<Test> 4',
                    'contentanalyzer'     => 'hungarian',
                    '_boost'              => 3.0
                ],
                'index_prefixtest_name',
                'test_type_hu'
            )],
        ];
    }

    protected function assertEqualDocuments(Document $expected, Document $actual)
    {
        $this->assertEquals($expected->getId(), $actual->getId());
        $this->assertEquals($expected->getData(), $actual->getData());
        $this->assertEquals($expected->getType(), $actual->getType());
        $this->assertEquals($expected->getIndex(), $actual->getIndex());
    }

    protected function compareDocuments(Document $expected, Document $actual)
    {
        return $expected->getId() == $actual->getId()
            && $expected->getData() == $actual->getData()
            && $expected->getType() == $actual->getType()
            && $expected->getIndex() == $actual->getIndex()
        ;
    }

    protected function buildConfigurationObject(
        $backendLocales = ['en', 'hu'],
        $elasticaProviderBehavior = null,
        $emBehavior = null,
        $eventDispatcherBehavior = null
    ) {
        /** @var ElasticaProvider $elasticaProviderMock */
        $elasticaProviderMock = m::mock(ElasticaProvider::class);
        if (is_callable($elasticaProviderBehavior)) {
            $elasticaProviderBehavior($elasticaProviderMock);
        }
        $searchProvider = new SearchProviderChain();
        $searchProvider->addProvider($elasticaProviderMock, 'Elastica');
        $search = new Search($searchProvider, 'index_prefix', 'Elastica');

        /** @var DomainConfiguration $domainConfigurationMock */
        $domainConfigurationMock = m::mock(DomainConfiguration::class, [
            'getBackendLocales' => $backendLocales,
        ]);

        $analyzerLanguages = [
            'en' => ['analyzer' => 'english' ],
            'hu' => ['analyzer' => 'hungarian' ],
        ];

        // EntityManager
        $emMock = m::mock(EntityManager::class);
        if (is_callable($emBehavior)) {
            $emBehavior($emMock);
        }
        /** @var Registry $doctrineMock */
        $doctrineMock = m::mock(Registry::class, [
            'getManager' => $emMock,
        ]);
        /** @var EventDispatcher $eventDispatcherMock */
        $eventDispatcherMock = m::mock(EventDispatcher::class);
        if (is_callable($eventDispatcherBehavior)) {
            $eventDispatcherBehavior($eventDispatcherMock);
        }

        return new SearchableEntityConfiguration(
            'test_name',
            'test_type',
            $search,
            $domainConfigurationMock,
            $analyzerLanguages,
            $doctrineMock,
            $eventDispatcherMock
        );
    }
}
