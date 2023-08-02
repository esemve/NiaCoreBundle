<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Form;

use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

class HiddenMoveType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $this->buildFromBox($builder, $options);
        $this->buildToBox($builder, $options);
        $this->buildTypeBox($builder, $options);

        parent::buildForm($builder, $options);
    }

    protected function buildFromBox(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('from', HiddenType::class, [
            'constraints' => [
                new NotBlank(),
                new Type('numeric'),
            ],
        ]);
    }

    protected function buildToBox(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('to', HiddenType::class, [
            'constraints' => [
                new NotBlank(),
                new Type('numeric'),
            ],
        ]);
    }

    protected function buildTypeBox(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('type', HiddenType::class);
    }

    protected function callbackViewTransformer(): ?CallbackTransformer
    {
        return new CallbackTransformer(
            function ($value) {
                return $value;
            },
            function ($value) {
                return [
                    'from' => (int) $value['from'],
                    'to' => (int) $value['to'],
                    'type' => $value['type'],
                ];
            }
        );
    }
}
