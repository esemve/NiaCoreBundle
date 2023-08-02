<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Form;

use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

class HiddenDeleteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $this->buildIdBox($builder, $options);

        parent::buildForm($builder, $options);
    }

    protected function buildIdBox(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('id', HiddenType::class, [
            'constraints' => [
                new NotBlank(),
                new Type('numeric'),
            ],
        ]);
    }

    protected function callbackViewTransformer(): ?CallbackTransformer
    {
        return new CallbackTransformer(
            function ($value) {
                return $value;
            },
            function ($value) {
                return [
                    'id' => (int) $value['id'],
                ];
            }
        );
    }
}
