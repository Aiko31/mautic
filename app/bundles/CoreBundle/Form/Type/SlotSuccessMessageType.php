<?php

namespace Mautic\CoreBundle\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;

class SlotSuccessMessageType extends SlotType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'successmessage',
            TextareaType::class,
            [
                'label'      => false,
                'label_attr' => ['class' => 'control-label'],
                'required'   => false,
                'attr'       => [
                    'class'           => 'form-control editor',
                    'data-slot-param' => 'content',
                ],
            ]
        );

        parent::buildForm($builder, $options);
    }

    public function getBlockPrefix(): string
    {
        return 'slot_successmessage';
    }
}
