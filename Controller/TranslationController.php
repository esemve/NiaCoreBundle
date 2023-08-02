<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\Response;

class TranslationController extends AbstractController
{
    public function portal(string $locale): Response
    {
        return $this->createResponse($locale, false);
    }

    public function admin(string $locale): Response
    {
        return $this->createResponse($locale, true);
    }

    protected function createResponse(string $locale, bool $isAdmin = false): Response
    {
        $trans = [];

        $translator = $this->getTranslator();
        $translator->setLocale($locale);

        if ($isAdmin) {
            $parameters = $this->container->getParameter('nia.core.javascript_admin_translations');
        } else {
            $parameters = $this->container->getParameter('nia.core.javascript_portal_translations');
        }

        foreach ($parameters as $translation) {
            $trans[$translation] = $translator->trans($translation);
        }
        $trans = json_encode($trans);

        $response = new Response($this->renderView('@NiaCore/js/translation.js.twig', [
            'trans' => $trans,
        ]));

        $response->headers->set('Content-Type', 'text/javascript');

        return $response;
    }
}
