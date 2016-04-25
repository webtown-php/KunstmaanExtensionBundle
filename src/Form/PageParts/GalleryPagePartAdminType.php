<?php

namespace Webtown\KunstmaanExtensionBundle\Form\PageParts;

use Kunstmaan\MediaBundle\Repository\FolderRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * GalleryPagePartAdminType
 */
class GalleryPagePartAdminType extends AbstractType
{

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
        parent::buildForm($builder, $options);

        $builder->add('folder', 'entity', [
            'required' => true,
            'label' => 'Media könyvtár',
            'class' => 'KunstmaanMediaBundle:Folder',
            'attr' => ['class' => 'col-sm-8'],
            'empty_value' => 'Válasszon könyvtárat',
            'property' => 'optionLabel',
            'multiple' => false,
            'expanded' => false,
            'query_builder' => function (FolderRepository $r) {
                return $r->getChildrenQueryBuilder();
            }
        ])
        ;
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'webtown_kunstmaan_extension_gallery_admin_type';
    }

    /**
     * Sets the default options for this type.
     *
     * @param OptionsResolver $resolver The resolver for the options.
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => '\Webtown\KunstmaanExtensionBundle\Entity\PageParts\GalleryPagePart'
        ]);
    }
}
