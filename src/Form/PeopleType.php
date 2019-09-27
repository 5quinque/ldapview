<?php

namespace App\Form;

use App\Entity\People;
use App\Entity\Netgroup;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class PeopleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type')
            ->add('uid')
            ->add('gecos')
            ->add('uidNumber')
            ->add('gidNumber')
            ->add('homeDirectory')
            ->add('netgroup', EntityType::class, [
                'class' => Netgroup::class,
                'choice_label' => function ($netgroup) {
                    return $netgroup->getName();
                },
                'multiple' => true,
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => People::class,
        ]);
    }
}
