<?php

namespace Webtown\KunstmaanExtensionBundle\Twig\Extension;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;
use Kunstmaan\NodeBundle\Entity\AbstractPage;
use Kunstmaan\NodeBundle\Entity\NodeTranslation;
use Kunstmaan\PagePartBundle\Helper\HasPagePartsInterface;
use Kunstmaan\PagePartBundle\Twig\Extension\PagePartTwigExtension;
use Webtown\KunstmaanExtensionBundle\Entity\PageParts\InsertPagePagePart;
use Webtown\KunstmaanExtensionBundle\Exception\InsertedMaxDepthException;

// @todo (Chris) A következő módosításokat kell végrehajtani: 1. Legyen egy erre létrehozott külön Page típus. 2. Az adott Page típusba ne lehessen beszúrni más oldalt.
class PageInsertTwigExtension extends \Twig_Extension
{
    /**
     * @var Registry
     */
    protected $doctrine;

    /**
     * @var PagePartTwigExtension
     */
    protected $pagePartTwigExtension;

    /**
     * @var int
     */
    protected $maxDepth;

    /**
     * @var array|HasPagePartsInterface
     */
    protected $insertedPages = [];

    /**
     * PageInsertTwigExtension constructor.
     *
     * @param Registry              $doctrine
     * @param PagePartTwigExtension $pagePartTwigExtension
     * @param int                   $maxDepth
     */
    public function __construct(Registry $doctrine, PagePartTwigExtension $pagePartTwigExtension, $maxDepth = 3)
    {
        $this->doctrine = $doctrine;
        $this->pagePartTwigExtension = $pagePartTwigExtension;
        $this->maxDepth = 3;
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('render_inserted_pageparts', [$this, 'renderInsertedPageParts'], [
                'needs_environment' => true,
                'needs_context' => true,
                'is_safe' => ['html']
            ]),
            new \Twig_SimpleFunction('get_page_from_insert_page_page_part', [$this, 'getPageFromInsertPagePagePart']),
            new \Twig_SimpleFunction('get_page_by_internal_name', [$this, 'getPageByInternalName']),
        ];
    }

    public function renderInsertedPageParts(
        \Twig_Environment $env,
        array $twigContext,
        InsertPagePagePart $insertPagePagePart,
        $locale,
        $includeOffline = true,
        $contextName = 'main',
        array $parameters = []
    ) {
        /** @var HasPagePartsInterface|AbstractPage $page */
        $page = $this->getPageFromInsertPagePagePart($insertPagePagePart, $locale, $includeOffline);
        $this->insertedPages[] = $page;
        if (count($this->insertedPages) > $this->maxDepth) {
            $pageTitles = [];
            /** @var AbstractPage $insertedPage */
            foreach ($this->insertedPages as $insertedPage) {
                $pageTitles[] = $insertedPage->__toString();
            }
            $msg = sprintf(
                'Too many insertion depth (max: %d): `%s`',
                $this->maxDepth,
                implode('` » `', $pageTitles)
            );
            array_pop($this->insertedPages);
            // @todo (Chris) Ez adminon vagy inkább egy JS alert legyen, vagy validálni kellene, hogy nem alakul-e ki végtelen ciklus. De inkább az előbbi!
            return $msg;
            //throw new InsertedMaxDepthException($msg);
        }
        $twigContext['page'] = $page;
        unset($twigContext['pageparts']);
        $content = $this->pagePartTwigExtension->renderPageParts($env, $twigContext, $page, $contextName, $parameters);
        array_pop($this->insertedPages);

        return $content;
    }

    public function getPageFromInsertPagePagePart(InsertPagePagePart $insertPagePagePart, $locale, $includeOffline = true)
    {
        /** @var EntityManager $em */
        $em = $this->doctrine->getManager();

        return $insertPagePagePart
            ->getNode()
            ->getNodeTranslation($locale, $includeOffline)
            ->getRef($em)
        ;
    }

    public function getPageByInternalName($internalName, $locale)
    {
        /** @var EntityManager $em */
        $em = $this->doctrine->getManager();
        /** @var NodeTranslation $translation */
        $translation = $em->getRepository('KunstmaanNodeBundle:NodeTranslation')
            ->getNodeTranslationByLanguageAndInternalName($locale, $internalName);

        return $translation->getRef($em);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'page_insert';
    }
}
