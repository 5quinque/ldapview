<?php

namespace App\Form\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class AddUidFieldSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        // Tells the dispatcher that you want to listen on the form.pre_set_data
        // event and that the preSetData method should be called.
        return [FormEvents::PRE_SET_DATA => 'preSetData'];
    }

    public function preSetData(FormEvent $event)
    {
        $person = $event->getData();
        $form = $event->getForm();

        $hiddenNetgroups = [];
        foreach ($person->getNetgroup() as $netgroup) {
            $hiddenNetgroups[] = $netgroup->getId();
        }

        // checks if the person object is "new"
        // If no data is passed to the form, the data is "null".
        // This should be considered a new "Person"
        if (!$person || null === $person->getId()) {
            $form->add('type')->add('uid', null);
        } else {
            $form->add('uid', null, ['disabled' => true])
                ->add('netgroups_hidden', HiddenType::class, [
                    'data' => serialize($hiddenNetgroups),
                    'mapped' => false
                ]);
        }
    }
}
