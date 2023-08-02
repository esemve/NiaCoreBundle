<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Form;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class MailSendingTestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $this->buildToBox($builder, $options);
        $this->buildSubmitBox($builder, $options);

        parent::buildForm($builder, $options);
    }

    protected function buildToBox(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('address', TextType::class, [
            'constraints' => [
                new NotBlank(),
            ],
        ]);
    }
}
