<?php

namespace Webtown\KunstmaanExtensionBundle\Form\PageParts;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;
use Kunstmaan\NodeBundle\Entity\AbstractPage;
use Kunstmaan\NodeBundle\Entity\Node;
use Symfony\Component\Form\ChoiceList\View\ChoiceView;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
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
     * InsertPagePagePartAdminType constructor.
     *
     * @param RequestStack $requestStack
     * @param Registry     $doctrine
     */
    public function __construct(RequestStack $requestStack, Registry $doctrine)
    {
        $this->requestStack = $requestStack;
        $this->doctrine = $doctrine;
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
            'class' => 'Kunstmaan\NodeBundle\Entity\Node',
            'expanded' => false,
            'multiple' => false,
            'required' => true,
            'property' => function (Node $choice) use ($locale, $em) {
                /** @var AbstractPage $page */
                $page = $choice->getNodeTranslation($locale)->getRef($em);

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
            if ($data->getNode()) {
                /** @var Node $parent */
                $parent = $data->getNode()->getParent();
                $parentPageId        = $parent ? $parent->getSequenceNumber() : null;
                $parentPageClassName = $parent ? $parent->getRefEntityName() : null;
            } else {
                // We want to set disable the parent page to prevent infinity insert cycle. If it is a new page part, we can get this data from URL.
                $parentPageId        = $this->requestStack->getMasterRequest()->get('pageid');
                $parentPageClassName = $this->requestStack->getMasterRequest()->get('pageclassname');
            }

            $nodeFormView = $view->children['node'];
            /** @var ChoiceView $choice */
            foreach ($nodeFormView->vars['choices'] as $choice) {
                /** @var Node $currentData */
                $currentData = $choice->data;
                if ($currentData->getRefEntityName() == $parentPageClassName && $currentData->getSequenceNumber() == $parentPageId) {
                    $choice->attr['disabled'] = 'disabled';
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
