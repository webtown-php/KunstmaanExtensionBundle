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
            'label'     => 'wt_kuma_extension.slider.form.image.label',
            'mediatype' => 'image',
            'required'  => true,
        ]);
        $builder->add('captionTitle', TextType::class, [
            'label'     => 'wt_kuma_extension.slider.form.caption_title.label',
            'required'  => false,
        ]);
        $builder->add('caption', TextType::class, [
            'label'     => 'wt_kuma_extension.slider.form.caption.label',
            'required'  => false,
        ]);
        $builder->add('altText', TextType::class, [
            'label'     => 'wt_kuma_extension.slider.form.alt_text.label',
            'required'  => false,
        ]);
        $builder->add('link', URLChooserType::class, [
            'label'     => 'wt_kuma_extension.slider.form.link.label',
            'required'  => false,
        ]);
        $builder->add('openInNewWindow', CheckboxType::class, [
            'label'     => 'wt_kuma_extension.slider.form.open_in_new_window.label',
            'required'  => false,
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
