<?php

namespace Mdkyb\WebsiteBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class ChangeProfileType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('info', 'textarea', array('required' => false, 'label' => 'Profil'));
        $builder->add('image.file', 'file', array('required' => false, 'label' => 'Profilbild'));
    }

    public function getName()
    {
        return "change_profile";
    }
}
