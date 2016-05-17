<?php

namespace Webtown\KunstmaanExtensionBundle\Form\PageParts;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;
use Kunstmaan\NodeBundle\Entity\AbstractPage;
use Kunstmaan\NodeBundle\Entity\Node;
use Kunstmaan\PagePartBundle\PagePartConfigurationReader\PagePartConfigurationReaderInterface;
use Symfony\Component\Form\ChoiceList\View\ChoiceView;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Webtown\KunstmaanExtensionBundle\Entity\InsertablePageInterface;
use Webtown\KunstmaanExtensionBundle\Entity\PageParts\InsertPagePagePart;

/**
 * InsertPagePagePartAdminType
 */
class InsertPagePagePartAdminType extends \Symfony\Component\Form\AbstractType
{
    /**
     * We want to set disable the parent page to prevent infinity insert cycle. If it is a new page part, we can get this
     * data from URL.
     *
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * @var Registry
     */
    protected $doctrine;

    /**
     * @var PagePartConfigurationReaderInterface
     */
    protected $pagePartConfigReader;

    /**
     * InsertPagePagePartAdminType constructor.
     *
     * @param RequestStack                          $requestStack
     * @param Registry                              $doctrine
     * @param PagePartConfigurationReaderInterface  $pagePartConfigReader
     */
    public function __construct(RequestStack $requestStack, Registry $doctrine, PagePartConfigurationReaderInterface $pagePartConfigReader)
    {
        $this->requestStack = $requestStack;
        $this->doctrine = $doctrine;
        $this->pagePartConfigReader = $pagePartConfigReader;
    }

    /**
     * Builds the form.
     *
     * This method is called for each type in the hierarchy starting form the
     * top most type. Type extensions can further modify the form.
     *
     * @param FormBuilderInterface $builder The form builder
     * @param array                $options The options
     *
     * @see FormTypeExtensionInterface::buildForm()
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $locale = $this->requestStack->getMasterRequest()->getLocale();
        /** @var EntityManager $em */
        $em = $this->doctrine->getManager();

        parent::buildForm($builder, $options);
        $builder->add('node', 'Symfony\Bridge\Doctrine\Form\Type\EntityType', [
            'label'    => 'wt_kuma_extension.insert_page.form.label.inserted_node',
            'class'    => 'Kunstmaan\NodeBundle\Entity\Node',
            'expanded' => false,
            'multiple' => false,
            'required' => true,
            'property' => function (Node $choice) use ($locale, $em) {
                /** @var AbstractPage $page */
                $page = $choice->getNodeTranslation($locale, true)->getRef($em);

                return str_repeat('-', $choice->getLevel() * 2) . ' ' . $page->getPageTitle();
            },
        ]);
    }

    /**
     * We want to set disable the parent page to prevent infinity insert cycle. If it is an edit page, we can disable this
     * from form's data.
     *
     * @param FormView      $view
     * @param FormInterface $form
     * @param array         $options
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $data = $form->getData();
        if ($data instanceof InsertPagePagePart) {

            $this->validateParentPage();

            $reflections = [];
            $nodeFormView = $view->children['node'];
            /** @var ChoiceView $choice */
            foreach ($nodeFormView->vars['choices'] as $choice) {
                /** @var Node $currentData */
                $currentData = $choice->data;

                $entityCls = $currentData->getRefEntityName();
                if (!array_key_exists($entityCls, $reflections)) {
                    $reflections[$entityCls] = new \ReflectionClass($entityCls);
                }

                // only the pages with the right interface are selectable
                if (!$reflections[$entityCls]->implementsInterface(InsertablePageInterface::class)) {
                    $choice->attr['disabled'] = 'disabled';
                }
            }
        }
    }

    /**
     * Check if the page part is insertable into the current page.
     *
     * @throws \LogicException
     */
    protected function validateParentPage()
    {
        $request = $this->requestStack->getMasterRequest();

        // the node which the pagepart belongs to
        if ($request->query->has('pageid') && $request->query->has('pageclassname')) {
            $repo = $this->doctrine->getManager()->getRepository('KunstmaanNodeBundle:Node');
            $node = $repo->getNodeForIdAndEntityname(
              $request->query->get('pageid'),
              $request->query->get('pageclassname')
            );

            $pageReflection = new \ReflectionClass($node->getRefEntityName());

            // if the current page implements the interface, then it is forbidden to insert anything beneath
            if ($pageReflection->implementsInterface(InsertablePageInterface::class)) {
                $currentContext = $this->requestStack->getMasterRequest()->get('context');
                $page = $pageReflection->newInstanceWithoutConstructor();

                // get pagepart configurations for the current page
                $pagePartConfigs = $this->pagePartConfigReader->getPagePartAdminConfigurators($page);

                foreach ($pagePartConfigs as $config) {
                    if ($config->getContext() === $currentContext) {
                        foreach ($config->getPossiblePagePartTypes() as $pagePartType) {
                            if ($pagePartType['class'] === InsertPagePagePart::class) {
                                throw new \LogicException(
                                  sprintf(
                                    'The %s page must not allow %s as possible page part, because it implements %s! You must modify the "%s" context.',
                                    $pageReflection->getName(),
                                    InsertPagePagePart::class,
                                    InsertablePageInterface::class,
                                    $currentContext
                                  ));
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getBlockPrefix()
    {
        return 'webtown_kunstmaanextensionbundle_insertpagepageparttype';
    }

    /**
     * Sets the default options for this type.
     *
     * @param OptionsResolver $resolver The resolver for the options.
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => '\Webtown\KunstmaanExtensionBundle\Entity\PageParts\InsertPagePagePart'
        ]);
    }
}
