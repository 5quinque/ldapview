<?php

namespace App\Form\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class AddPeopleFieldSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        // Tells the dispatcher that you want to listen on the form.pre_set_data
        // event and that the preSetData method should be called.
        return [FormEvents::PRE_SET_DATA => 'preSetData'];
    }

    public function preSetData(FormEvent $event)
    {
        $netgroup = $event->getData();
        $form = $event->getForm();

        $hiddenPeople = [];
        foreach ($netgroup->getPeople() as $person) {
            $hiddenPeople[] = $person->getId();
        }

        // checks if the netgroup object is "new"
        // If no data is passed to the form, the data is "null".
        // This should be considered a new "Netgroup"
        if ($netgroup && null !== $netgroup->getId()) {
            $form->add('people_hidden', HiddenType::class, [
                'data' => serialize($hiddenPeople),
                'mapped' => false
            ]);
        }
    }
}
