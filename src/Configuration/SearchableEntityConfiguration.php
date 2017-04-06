<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2016.05.04.
 * Time: 17:11
 */

namespace Webtown\KunstmaanExtensionBundle\Configuration;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\Mapping\ClassMetadata;
use Elastica\Document;
use Doctrine\Common\Persistence\ManagerRegistry;
use Kunstmaan\SearchBundle\Helper\IndexableInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Webtown\KunstmaanExtensionBundle\Entity\SearchableEntityInterface;
use Webtown\KunstmaanExtensionBundle\Event\IndexEntityEvent;
use Kunstmaan\AdminBundle\Helper\DomainConfigurationInterface;
use Kunstmaan\NodeSearchBundle\Helper\SearchBoostInterface;
use Kunstmaan\SearchBundle\Configuration\SearchConfigurationInterface;
use Kunstmaan\SearchBundle\Provider\SearchProviderInterface;
use Kunstmaan\UtilitiesBundle\Helper\ClassLookup;
use Psr\Log\LoggerInterface;

class SearchableEntityConfiguration implements SearchConfigurationInterface
{
    /** @var string */
    protected $indexName;

    /** @var string */
    protected $indexType;

    /** @var SearchProviderInterface */
    protected $searchProvider;

    /** @var array */
    protected $locales = [];

    /** @var array */
    protected $analyzerLanguages;

    /** @var Registry */
    protected $doctrine;

    /** @var  ManagerRegistry */
    protected $mongo;

    /** @var  EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var array|Document[] */
    protected $documents = array();

    /** @var LoggerInterface */
    protected $logger = null;

    /**
     * @param $name
     * @param $type
     * @param SearchProviderInterface $searchProvider
     * @param DomainConfigurationInterface $domainConfiguration
     * @param $analyzerLanguages
     * @param Registry $doctrine
     * @param ManagerRegistry $mongo
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        $name,
        $type,
        SearchProviderInterface $searchProvider,
        DomainConfigurationInterface $domainConfiguration,
        $analyzerLanguages,
        Registry $doctrine,
        ManagerRegistry $mongo,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->indexName           = $name;
        $this->indexType           = $type;
        $this->searchProvider      = $searchProvider;
        $this->locales             = $domainConfiguration->getBackendLocales();
        $this->analyzerLanguages   = $analyzerLanguages;
        $this->doctrine            = $doctrine;
        $this->mongo               = $mongo;
        $this->eventDispatcher     = $eventDispatcher;
    }

    /**
     * Create indexes
     */
    public function createIndex()
    {
        // A \Kunstmaan\NodeSearchBundle\Configuration\NodePagesConfiguration::createIndex() által generált indexet használja.
    }

    /**
     * Populate the indexes
     */
    public function populateIndex()
    {
        $this->buildDocumentsByManager($this->doctrine);

        if ($this->mongo) {
            $this->buildDocumentsByManager($this->mongo);
        }

        if (!empty($this->documents)) {
            $response = $this->searchProvider->addDocuments($this->documents);
            $this->documents = [];
        }
    }

    protected function buildDocumentsByManager(ManagerRegistry $registry)
    {
        $manager = $registry->getManager();
        $meta = $manager->getMetadataFactory()->getAllMetadata();
        /** @var ClassMetadata $m */
        foreach ($meta as $m) {
            if ($m->getReflectionClass()->implementsInterface(SearchableEntityInterface::class)) {
                $repository = $manager->getRepository($m->getName());
                $entities = $repository->findAll();
                foreach ($entities as $entity) {
                    foreach ($this->locales as $locale) {
                        $this->addEntityToIndex($entity, $locale);
                    }
                }
            }
        }
    }

    /**
     * Delete indexes
     */
    public function deleteIndex()
    {
        // A \Kunstmaan\NodeSearchBundle\Configuration\NodePagesConfiguration::deleteIndex() fv ugyanezt csinálja.
    }

    /**
     * @param SearchableEntityInterface $entity
     *
     * @todo (Chris) Teszt kellene még hozzá
     */
    public function indexEntity(SearchableEntityInterface $entity)
    {
        foreach ($this->locales as $locale) {
            $this->addEntityToIndex($entity, $locale);
        }

        if (!empty($this->documents)) {
            $response = $this->searchProvider->addDocuments($this->documents);
            $this->documents = [];
        }
    }

    /**
     * @param SearchableEntityInterface $entity
     *
     * @todo (Chris) Teszt kellene még hozzá
     */
    public function deleteEntity(SearchableEntityInterface $entity)
    {
        foreach ($this->locales as $locale) {
            $uid       = $this->getDocUid($entity, $locale);
            $indexType = $this->indexType;
            $this->searchProvider->deleteDocument($this->indexName, $indexType, $uid);
        }
    }

    protected function addEntityToIndex(SearchableEntityInterface $entity, $locale)
    {
        if ($entity instanceof IndexableInterface && !$entity->isIndexable()) {
            return;
        }

        $doc = [
//            'root_id'             => $rootNode->getId(),
//            'node_id'             => $node->getId(),
//            'node_translation_id' => $nodeTranslation->getId(),
//            'node_version_id'     => $publicNodeVersion->getId(),
            'title'               => $entity->getSearchTitle($locale),
            'lang'                => $locale,
            'route_name'          => $entity->getSearchRouteName(),
            'route_parameters'    => $entity->getSearchRouteParameters(),
            'entity'              => ClassLookup::getClass($entity),
            'view_roles'          => ['IS_AUTHENTICATED_ANONYMOUSLY'],
            'content'             => $this->removeHtml($entity->getSearchContent($locale)),
            'type'                => $entity->getSearchType(),
            'id'                  => $entity->getId(),
        ];

        // Analyzer field
        $this->addAnalyzer($locale, $doc);
        $this->addBoost($entity, $doc);
        $this->addCustomData($entity, $doc);

        $uid = $this->getDocUid($entity, $locale);

        $this->documents[] = $this->searchProvider->createDocument(
            $uid,
            $doc,
            $this->indexName . '_' . $locale,
            $this->indexType
        );
    }

    /**
     * Add content analyzer to the index document
     *
     * @param string $locale
     * @param array  $doc
     *
     * @return array
     */
    protected function addAnalyzer($locale, &$doc)
    {
        $language               = $this->analyzerLanguages[$locale]['analyzer'];
        $doc['contentanalyzer'] = $language;
    }

    protected function addBoost($entity, &$doc)
    {
        // Check page type boost
        $doc['_boost'] = 1.0;
        if ($entity instanceof SearchBoostInterface) {
            $doc['_boost'] += $entity->getSearchBoost();
        }
    }

    /**
     * Add custom data to index document (you can override to add custom fields
     * to the search index)
     *
     * @param SearchableEntityInterface $entity
     * @param array                     $doc
     *
     * @todo (Chris) Ehhez külön teszt kellene.
     */
    protected function addCustomData(SearchableEntityInterface $entity, &$doc)
    {
        $event = new IndexEntityEvent($entity, $doc);
        $this->eventDispatcher->dispatch(IndexEntityEvent::EVENT_NAME, $event);

        $doc = $event->getDoc();
    }

    /**
     * Removes all HTML markup & decode HTML entities
     *
     * @param $text
     *
     * @return string
     */
    protected function removeHtml($text)
    {
        // Remove HTML markup
        $result = strip_tags($text);

        // Decode HTML entities
        $result = trim(html_entity_decode($result, ENT_QUOTES));

        return $result;
    }

    protected function getDocUid(SearchableEntityInterface $entity, $locale)
    {
        return sprintf(
            'searchable_entity.%s.%s#%s',
            $entity->getSearchUniqueEntityName(),
            $entity->getId(),
            $locale
        );
    }
}
