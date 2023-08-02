<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Form\Mediator;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

class FormMediator
{
    private $session;

    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    public function set(string $key, Request $request)
    {
        $this->session->getFlashBag()->add('form_mediator_'.$key, $request);
    }

    public function handle(string $key, FormInterface $form)
    {
        if ($this->session->getFlashBag()->has('form_mediator_'.$key)) {
            $form->handleRequest($this->session->getFlashBag()->get('form_mediator_'.$key)[0]);
        }
    }

    public function has(string $key)
    {
        if ($this->session->getFlashBag()->has('form_mediator_'.$key)) {
            return true;
        }

        return false;
    }
}
