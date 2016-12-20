<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2016.03.08.
 * Time: 10:43
 */

namespace Webtown\KunstmaanExtensionBundle\Form\PageParts;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Webtown\KunstmaanExtensionBundle\Entity\PageParts\SliderPagePart;
use Webtown\KunstmaanExtensionBundle\Form\SliderImageAdminType;

class SliderPagePartAdminType extends AbstractType
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

        $builder->add('images', CollectionType::class, [
            'label'     => 'wt_kuma_extension.slider.form.images.label',
            'entry_type'      => SliderImageAdminType::class,
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false,
            'attr' => [
                'nested_form'           => true,
                'nested_form_min'       => 1,
                'nested_form_max'       => 4,
                'nested_sortable'       => true,
                'nested_sortable_field' => 'displayOrder',
            ],
        ]);
    }

    /**
     * Sets the default options for this type.
     *
     * @param OptionsResolver $resolver The resolver for the options.
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SliderPagePart::class,
        ]);
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'webtown_kunstmaan_extension_slider_admin_type';
    }
}
