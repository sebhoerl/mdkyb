<?php

namespace Mdkyb\WebsiteBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class MembershipApplicationType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('supporting', 'choice', array(
            'choices' => array(
                false => 'Ordentliches Mitglied',
                true => 'Förderndes Mitglied',
            ),
            //'expanded' => true,
            'label' => 'Mitgliedschaft',
        ));

        $builder->add('student', 'choice', array(
            'choices' => array(
                true => 'Ja',
                false => 'Nein',
            ),
            //'expanded' => true,
            'label' => 'Student',
        ));

        $builder->add('male', 'choice', array(
            'choices' => array(
                true => 'Herr',
                false => 'Frau',
            ),
            //'expanded' => true,
            'label' => 'Anrede',
        ));

        $builder->add('title', 'text', array(
            'label' => 'Titel',
            'required' => false,
        ));

        $builder->add('surename', 'text', array(
            'label' => 'Vorname',
        ));

        $builder->add('lastname', 'text', array(
            'label' => 'Nachname',
        ));

        $builder->add('street', 'text', array(
            'label' => 'Straße/Hausnummer',
        ));

        $builder->add('postalCode', 'text', array(
            'label' => 'Postleitzahl',
        ));

        $builder->add('city', 'text', array(
            'label' => 'Ort',
        ));

        $builder->add('email', 'email', array(
            'label' => 'E-Mail',
        ));

        $builder->add('phone', 'text', array(
            'label' => 'Telefonnummer',
            'required' => false,
        ));

        $builder->add('comment', 'textarea', array(
            'label' => 'Kommentar',
            'required' => false,
        ));
    }

    public function getName()
    {
        return "membership_application";
    }
}
