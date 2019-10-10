<?php

namespace App\Form;

use App\Entity\Netgroup;
use App\Entity\People;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Form\EventListener\AddPeopleFieldSubscriber;

class NetgroupType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('people', EntityType::class, [
                'class' => People::class,
                'choice_label' => function ($people) {
                    return $people->getUid();
                },
                'by_reference' => false,
                'multiple' => true,
                'required' => false,
                'attr' => array(
                    'class' => 'selectpicker',
                    'data-live-search' => 'true', 'data-actions-box' => 'true'
                ),
            ])
            ->addEventSubscriber(new AddPeopleFieldSubscriber());
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Netgroup::class,
        ]);
    }
}
