<?php

namespace Webtown\KunstmaanExtensionBundle\Form;

use Kunstmaan\MediaBundle\Form\Type\MediaType;
use Kunstmaan\NodeBundle\Form\Type\URLChooserType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Webtown\KunstmaanExtensionBundle\Entity\SliderImage;

class SliderImageAdminType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('media', MediaType::class, [
            'label' => 'mediapagepart.image.choosefile',
            'mediatype' => 'image',
            'required' => true
        ]);
        $builder->add('captionTitle', TextType::class, [
            'required' => false
        ]);
        $builder->add('caption', TextType::class, [
            'required' => false
        ]);
        $builder->add('altText', TextType::class, [
            'required' => false,
            'label' => 'mediapagepart.image.alttext'
        ]);
        $builder->add('link', URLChooserType::class, [
            'required' => false,
            'label' => 'mediapagepart.image.link'
        ]);
        $builder->add('openInNewWindow', CheckboxType::class, [
            'required' => false,
            'label' => 'mediapagepart.image.openinnewwindow'
        ]);
        $builder->add('displayOrder', HiddenType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SliderImage::class,
        ]);
    }

    public function getName()
    {
        return 'webtown_kunstmaan_extension_bundle_slider_image_admin_type';
    }
}
