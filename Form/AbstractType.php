<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Form;

use Esemve\Collection\CollectionFactoryInterface;
use Nia\CoreBundle\Enum\Factory\AbstractEnumFactory;
use Nia\CoreBundle\Event\Listener\AbstractListener;
use Nia\CoreBundle\Manager\PersistableManager;
use Nia\CoreBundle\Security\Context;
use Nia\CoreBundle\Security\Factory\ContextFactory;
use Symfony\Component\Form\AbstractType as BaseAbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;

abstract class AbstractType extends BaseAbstractType
{
    /**
     * @var TranslatorInterface|null
     */
    private $translator;

    /**
     * @var PersistableManager|null
     */
    private $manager;

    /**
     * @var AbstractEnumFactory
     */
    private $enumFactory;

    /**
     * @var DataTransformerInterface|null
     */
    private $modelTransformer;

    /**
     * @var DataTransformerInterface|null
     */
    private $viewTransformer;

    /**
     * @var CollectionFactoryInterface
     */
    private $collectionFactory;

    /**
     * @var ContextFactory
     */
    private $contextFactory;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        if (!empty($this->modelTransformer)) {
            $builder->addModelTransformer($this->modelTransformer);
        }

        if (!empty($this->viewTransformer)) {
            $builder->addViewTransformer($this->modelTransformer);
        }

        $callbackModelTransformer = $this->callbackModelTransformer();
        if ($callbackModelTransformer instanceof CallbackTransformer) {
            $builder->addModelTransformer($callbackModelTransformer);
        }

        $callbackViewTransformer = $this->callbackViewTransformer();
        if ($callbackViewTransformer instanceof CallbackTransformer) {
            $builder->addViewTransformer($callbackViewTransformer);
        }

        if ($this instanceof SelfDataMapperInterface) {
            $builder->setDataMapper($this);
        }
    }

    public function setContextFactory(ContextFactory $contextFactory): void
    {
        $this->contextFactory = $contextFactory;
    }

    public function setModelTransformer(DataTransformerInterface $modelTransformer): void
    {
        $this->modelTransformer = $modelTransformer;
    }

    protected function getModelTransformer(): ?DataTransformerInterface
    {
        return $this->modelTransformer;
    }

    public function setViewTransformer(DataTransformerInterface $viewTransformer): void
    {
        $this->viewTransformer = $viewTransformer;
    }

    protected function getViewTransformer(): ?DataTransformerInterface
    {
        return $this->viewTransformer;
    }

    public function setEnumFactory(AbstractEnumFactory $enumFactory): void
    {
        $this->enumFactory = $enumFactory;
    }

    protected function getEnumFactory(): AbstractEnumFactory
    {
        return $this->enumFactory;
    }

    public function setManager(PersistableManager $manager): void
    {
        $this->manager = $manager;
    }

    protected function getManager(): ?PersistableManager
    {
        return $this->manager;
    }

    public function setTranslator(TranslatorInterface $translator): void
    {
        $this->translator = $translator;
    }

    protected function getTranslator(): ?TranslatorInterface
    {
        return $this->translator;
    }

    public function setCollectionFactory(CollectionFactoryInterface $collectionFactory): void
    {
        $this->collectionFactory = $collectionFactory;
    }

    protected function getCollectionFactory(): CollectionFactoryInterface
    {
        return $this->collectionFactory;
    }

    protected function callbackModelTransformer(): ?CallbackTransformer
    {
        return null;
    }

    protected function callbackViewTransformer(): ?CallbackTransformer
    {
        return null;
    }

    protected function buildSubmitBox(FormBuilderInterface $builder, array $options): void
    {
        $passedOptions = [
            'label' => $options['label'] ?? 'NiaCoreBundle@form.AbstractType.submit',
        ];

        if (isset($options['translation_domain'])) {
            $passedOptions['translation_domain'] = $options['translation_domain'];
        }

        $builder->add('submit', SubmitType::class, $passedOptions);
    }

    protected function buildSaveBox(FormBuilderInterface $builder, array $options): void
    {
        $passedOptions = [
            'label' => $options['label'] ?? 'NiaCoreBundle@form.AbstractType.save',
        ];

        if (isset($options['translation_domain'])) {
            $passedOptions['translation_domain'] = $options['translation_domain'];
        }

        $builder->add('save', SubmitType::class, $passedOptions);
    }

    protected function merge(array $array, array $array2): array
    {
        return array_merge($array, $array2);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);
    }

    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        parent::finishView($view, $form, $options);

        if (!empty($view->vars['attr'])) {
            foreach ($view->vars['attr'] as $key => $value) {
                if (\is_array($value)) {
                    $view->vars['attr'][$key] = json_encode($value);
                }
            }
        }
    }

    public function getContext(): Context
    {
        return $this->contextFactory->create();
    }

    public function getServiceContext(AbstractListener $caller): Context
    {
        return $this->contextFactory->createServiceContext(\get_class($caller).':'.debug_backtrace()[1]['function']);
    }
}
