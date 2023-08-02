<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Controller;

use Doctrine\Common\Persistence\ManagerRegistry;
use Esemve\Collection\CollectionFactoryInterface;
use Esemve\Collection\IntegerCollection;
use Nia\CoreBundle\Driver\AbstractCacheDriver;
use Nia\CoreBundle\Entity\Factory\EntityFactoryInterface;
use Nia\CoreBundle\Enum\Factory\AbstractEnumFactory;
use Nia\CoreBundle\Exception\NotFoundException;
use Nia\CoreBundle\Provider\AbstractLocaleProvider;
use Nia\CoreBundle\Security\Context;
use Nia\CoreBundle\ValueObject\Locale;
use Nia\UserBundle\Collections\UserGroupCollection;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Csrf\CsrfToken;

abstract class AbstractController implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var Context
     */
    private $context;

    public function setContainer(ContainerInterface $container = null): void
    {
        $this->container = $container;
    }

    protected function getEntityFactory(): EntityFactoryInterface
    {
        return $this->container->get('nia.core.entity.factory');
    }

    protected function getCollectionFactory(): CollectionFactoryInterface
    {
        return $this->container->get('nia.core.collection.factory');
    }

    protected function setLocale(Request $request, string $locale): void
    {
        try {
            $locale = $this->getLocaleProvider()->provideByKey($locale);
            $request->setLocale($locale->getCode());
        } catch (NotFoundException $ex) {
        }
    }

    protected function getActualLocale(Request $request): Locale
    {
        return $this->getLocaleProvider()->provideByKey($request->getLocale());
    }

    protected function getLocaleProvider(): AbstractLocaleProvider
    {
        return $this->container->get('nia.core.locale.provider');
    }

    protected function getEnumFactory(): AbstractEnumFactory
    {
        return $this->container->get('nia.core.enum.factory');
    }

    protected function getTranslator()
    {
        return $this->container->get('translator');
    }

    protected function getSecurity(): AuthorizationCheckerInterface
    {
        return $this->container->get('security.authorization_checker');
    }

    public function getRouter(): RouterInterface
    {
        return $this->container->get('router');
    }

    public function getCache(): AbstractCacheDriver
    {
        return $this->container->get('nia.core.cache.provider')->provide();
    }

    protected function setFlashMessage(string $type, string $text): void
    {
        $this->getRequest()->getSession()->getFlashBag()->add($type, $text);
    }

    protected function checkAccess(string ...$roles): void
    {
        $found = 0;

        foreach ($roles as $role) {
            if ($this->getSecurity()->isGranted($role)) {
                ++$found;
            }
        }

        if (0 === $found) {
            throw new AccessDeniedException();
        }
    }

    protected function redirectBack(): RedirectResponse
    {
        return $this->redirect(
            $this->getRequest()->headers->get('referer')
        );
    }

    protected function getRequest(): Request
    {
        $stack = $this->container->get('request_stack');

        return $stack->getCurrentRequest();
    }

    protected function createNamedFormBuilder($name, $type = 'Symfony\Component\Form\Extension\Core\Type\FormType', $data = null, array $options = []): FormBuilderInterface
    {
        return $this->container->get('form.factory')->createNamedBuilder($name, $type, $data, $options);
    }

    public function isUserInAnyGroup(IntegerCollection $groupIds): bool
    {
        /** @var UserGroupCollection $groups */
        $groups = $this->getUser()->getManager()->getUserGroupManager()->getGroupsByUser($this->getUser());

        foreach ($groups as $group) {
            if (false !== $groupIds->search($group->getId(), true)) {
                return true;
            }
        }

        return false;
    }

    public function getNumericParameter(Request $request, string $propertyName, $defaultValue = null): int
    {
        $prop = $request->get($propertyName);
        if (!is_numeric($prop)) {
            $prop = $defaultValue;
        }

        if (\is_int($defaultValue)) {
            return (int) $prop;
        }

        return $prop;
    }

    protected function forward(string $controller, array $path = [], array $query = [], array $attributes = []): Response
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $path['_forwarded'] = $request->attributes;
        $path['_controller'] = $controller;
        $subRequest = $request->duplicate($query, null, $path);

        foreach ($request->attributes->all() as $key => $value) {
            if (!$subRequest->attributes->has($key)) {
                $subRequest->attributes->add([$key => $value]);
            }
        }

        return $this->container->get('http_kernel')->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
    }

    protected function refresh(): RedirectResponse
    {
        return $this->redirect($this->getRequest()->getRequestUri());
    }

    protected function createEmptyResponse(): Response
    {
        $response = new Response();
        $response->setStatusCode(Response::HTTP_NO_CONTENT);

        return $response;
    }

    protected function generateUrl(string $route, array $parameters = [], int $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH): string
    {
        return $this->container->get('router')->generate($route, $parameters, $referenceType);
    }

    protected function redirect(string $url, int $status = 302): RedirectResponse
    {
        return new RedirectResponse($url, $status);
    }

    protected function redirectToRoute(string $route, array $parameters = [], int $status = 302): RedirectResponse
    {
        return $this->redirect($this->generateUrl($route, $parameters), $status);
    }

    protected function json($data, int $status = 200, array $headers = [], array $context = []): JsonResponse
    {
        if ($this->container->has('serializer')) {
            $json = $this->container->get('serializer')->serialize($data, 'json', array_merge([
                'json_encode_options' => JsonResponse::DEFAULT_ENCODING_OPTIONS,
            ], $context));

            return new JsonResponse($json, $status, $headers, true);
        }

        return new JsonResponse($data, $status, $headers);
    }

    protected function file($file, string $fileName = null, string $disposition = ResponseHeaderBag::DISPOSITION_ATTACHMENT): BinaryFileResponse
    {
        $response = new BinaryFileResponse($file);
        $response->setContentDisposition($disposition, null === $fileName ? $response->getFile()->getFilename() : $fileName);

        return $response;
    }

    protected function addFlash(string $type, string $message)
    {
        if (!$this->container->has('session')) {
            throw new \LogicException('You can not use the addFlash method if sessions are disabled. Enable them in "config/packages/framework.yaml".');
        }

        $this->container->get('session')->getFlashBag()->add($type, $message);
    }

    protected function isGranted($attributes, $subject = null): bool
    {
        if (!$this->container->has('security.authorization_checker')) {
            throw new \LogicException('The SecurityBundle is not registered in your application. Try running "composer require symfony/security-bundle".');
        }

        return $this->container->get('security.authorization_checker')->isGranted($attributes, $subject);
    }

    protected function denyAccessUnlessGranted($attributes, $subject = null, string $message = 'Access Denied.')
    {
        if (!$this->isGranted($attributes, $subject)) {
            $exception = $this->createAccessDeniedException($message);
            $exception->setAttributes($attributes);
            $exception->setSubject($subject);

            throw $exception;
        }
    }

    protected function renderView(string $view, array $parameters = []): string
    {
        if (!$this->container->has('twig')) {
            throw new \LogicException('You can not use the "renderView" method if the Templating Component or the Twig Bundle are not available. Try running "composer require symfony/twig-bundle".');
        }

        return $this->container->get('twig')->render($view, $parameters);
    }

    protected function render(string $view, array $parameters = [], Response $response = null): Response
    {
        if ($this->container->has('twig')) {
            $content = $this->container->get('twig')->render($view, $parameters);
        } else {
            throw new \LogicException('You can not use the "render" method if the Templating Component or the Twig Bundle are not available. Try running "composer require symfony/twig-bundle".');
        }

        if (null === $response) {
            $response = new Response();
        }

        $response->setContent($content);

        return $response;
    }

    protected function stream(string $view, array $parameters = [], StreamedResponse $response = null): StreamedResponse
    {
        if ($this->container->has('twig')) {
            $twig = $this->container->get('twig');

            $callback = function () use ($twig, $view, $parameters) {
                $twig->display($view, $parameters);
            };
        } else {
            throw new \LogicException('You can not use the "stream" method if the Templating Component or the Twig Bundle are not available. Try running "composer require symfony/twig-bundle".');
        }

        if (null === $response) {
            return new StreamedResponse($callback);
        }

        $response->setCallback($callback);

        return $response;
    }

    protected function createNotFoundException(string $message = 'Not Found', \Exception $previous = null): NotFoundHttpException
    {
        return new NotFoundHttpException($message, $previous);
    }

    protected function createAccessDeniedException(string $message = 'Access Denied.', \Exception $previous = null): AccessDeniedException
    {
        if (!class_exists(AccessDeniedException::class)) {
            throw new \LogicException('You can not use the "createAccessDeniedException" method if the Security component is not available. Try running "composer require symfony/security-bundle".');
        }

        return new AccessDeniedException($message, $previous);
    }

    protected function createForm(string $type, $data = null, array $options = []): FormInterface
    {
        return $this->container->get('form.factory')->create($type, $data, $options);
    }

    protected function createFormBuilder($data = null, array $options = []): FormBuilderInterface
    {
        return $this->container->get('form.factory')->createBuilder(FormType::class, $data, $options);
    }

    protected function getDoctrine(): ManagerRegistry
    {
        if (!$this->container->has('doctrine')) {
            throw new \LogicException('The DoctrineBundle is not registered in your application. Try running "composer require symfony/orm-pack".');
        }

        return $this->container->get('doctrine');
    }

    protected function getUser()
    {
        if (!$this->container->has('security.token_storage')) {
            throw new \LogicException('The SecurityBundle is not registered in your application. Try running "composer require symfony/security-bundle".');
        }

        if (null === $token = $this->container->get('security.token_storage')->getToken()) {
            return;
        }

        if (!\is_object($user = $token->getUser())) {
            // e.g. anonymous authentication
            return;
        }

        return $user;
    }

    protected function isCsrfTokenValid(string $id, ?string $token): bool
    {
        if (!$this->container->has('security.csrf.token_manager')) {
            throw new \LogicException('CSRF protection is not enabled in your application. Enable it with the "csrf_protection" key in "config/packages/framework.yaml".');
        }

        return $this->container->get('security.csrf.token_manager')->isTokenValid(new CsrfToken($id, $token));
    }

    protected function getFormErrors(FormInterface $form): array
    {
        $errors = [];
        foreach ($form->getErrors(10) as $error) {
            $errors[] = $error->getMessage();
        }

        return $errors;
    }

    public function getContext(): Context
    {
        if (null === $this->context) {
            $this->context = $this->container->get('nia.core.security_context.factory')->create();
        }

        return $this->context;
    }

    public function getServiceContext(self $caller): Context
    {
        return $this->container->get('nia.core.security_context.factory')->createServiceContext(\get_class($caller).':'.debug_backtrace()[1]['function']);
    }
}
