<?php

namespace Mautic\LeadBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotEqualTo;

/**
 * @extends AbstractType<mixed>
 */
class CompanyChangeScoreActionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'score',
            NumberType::class,
            [
                'label'       => 'mautic.lead.lead.events.changecompanyscore',
                'attr'        => ['class' => 'form-control'],
                'label_attr'  => ['class' => 'control-label'],
                'scale'       => 0,
                'data'        => $options['data']['score'] ?? 0,
                'constraints' => [
                    new NotEqualTo(
                        [
                            'value'   => 0,
                            'message' => 'mautic.core.value.required',
                        ]
                    ),
                ],
            ]
        );
    }

    public function getBlockPrefix(): string
    {
        return 'scorecontactscompanies_action';
    }
}
