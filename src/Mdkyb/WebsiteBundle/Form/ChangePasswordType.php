<?php

namespace Mdkyb\WebsiteBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class ChangePasswordType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('oldPassword', 'password', array(
            'label' => 'Altes Passwort:',
            'always_empty' => true,
        ));

        $builder->add('newPassword', 'repeated', array(
            'type' => 'password',
            'options' => array(
                'always_empty' => true,
                'label' => 'Neues Passwort:',
            ),
            'invalid_message' => 'Das neue Passwort hat nicht übereingestimmt!',
        ));
    }

    public function getName()
    {
        return "change_password";
    }
}
