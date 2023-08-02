<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Controller;

use Nia\CoreBundle\Form\MailSendingTestType;
use Nia\CoreBundle\Manager\QueueManager;
use Nia\CoreBundle\Manager\RunLogManager;
use Nia\CoreBundle\Utils\MailSender;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SystemHealthController extends AbstractAdminController
{
    /**
     * @Security("is_granted('ROLE_ADMIN_LOGIN') and is_granted('ROLE_DEVELOPER')")
     */
    public function downloadAction(Request $request): Response
    {
        $logFile = $request->get('file') ?? '';
        $logFile = str_replace(['/', '..'], '', $logFile);

        if ((empty($logFile) || !file_exists($this->getVarFolderPath().'/log/'.$logFile))) {
            $this->createNotFoundException();
        }

        return $this->file($this->getVarFolderPath().'/log/'.$logFile);
    }

    /**
     * @Security("is_granted('ROLE_ADMIN_LOGIN') and is_granted('ROLE_DEVELOPER')")
     */
    public function showAction(Request $request): Response
    {
        $logFile = $request->get('file') ?? '';
        $logFile = str_replace(['/', '..'], '', $logFile);

        if ((empty($logFile) || !file_exists($this->getVarFolderPath().'/log/'.$logFile))) {
            $this->createNotFoundException();
        }

        return $this->render('@NiaCore/health/show.html.twig', [
            'content' => file_get_contents($this->getVarFolderPath().'/log/'.$logFile),
        ]);
    }

    /**
     * @Security("is_granted('ROLE_ADMIN_LOGIN') and is_granted('ROLE_DEVELOPER')")
     */
    public function indexAction(Request $request): Response
    {
        $now = new \DateTimeImmutable('now');
        $queue = $this->getRunLogManager()->getTime('queue');
        $queueActive = false;

        if (!empty($queue)) {
            if ($queue->getTimestamp() > $now->getTimestamp() - 60) {
                $queueActive = true;
            }
        }

        $form = $this->createForm(MailSendingTestType::class);

        $sendsuccess = '';
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->getMailSender()->send($form->getData()['address'], 'nia test', 'Test mail', 'Test mail');
            $this->setSuccessFlash('Success!');
        }

        $bannedCount = 0;

        return $this->render('@NiaCore/health/index.html.twig', [
            'queueActive' => $queueActive,
            'phpversion' => PHP_VERSION,
            'load' => sys_getloadavg(),
            'fileWrite' => $this->isFileWrite(),
            'logs' => $this->getLogs(),
            'form' => $form->createView(),
            'sendsuccess' => $sendsuccess,
            'queueAccumulated' => $this->getQueueManager()->getCountAccumulated(),
            'queueWait' => $this->getQueueManager()->getCountNotStarted(),
            'queueFail' => $this->getQueueManager()->getCountFail(),
            'queueSuccess' => $this->getQueueManager()->getCountSuccess(),
            'times' => $this->getRunLogManager()->findAll(),
            'bannedCount' => $bannedCount,
        ]);
    }

    protected function getRunLogManager(): RunLogManager
    {
        return $this->container->get('nia.core.run_log.manager');
    }

    protected function isFileWrite(): bool
    {
        try {
            file_put_contents($this->getVarFolderPath().'/test.txt', 'a');
            if (!file_exists($this->getVarFolderPath().'/test.txt')) {
                return false;
            }

            unlink($this->getVarFolderPath().'/test.txt');

            if (file_exists($this->getVarFolderPath().'/test.txt')) {
                return false;
            }

            return true;
        } catch (\Exception $ex) {
            return false;
        }
    }

    protected function getLogs()
    {
        $output = [];

        $scan = scandir($this->getVarFolderPath().'/log');
        foreach ($scan as $file) {
            if (('.' !== $file) && ('..' !== $file) && ('.gitignore' !== $file)) {
                $size = filesize($this->getVarFolderPath().'/log') / 1024;
                $output[$file] = ['name' => $file, 'size' => $size];
            }
        }

        krsort($output);
        $output = \array_slice($output, 0, 10);

        return $output;
    }

    protected function getVarFolderPath(): string
    {
        return $this->container->getParameter('kernel.project_dir').'/var/';
    }

    protected function getQueueManager(): QueueManager
    {
        return $this->container->get('nia.core.queue.manager');
    }

    protected function getMailSender(): MailSender
    {
        return $this->container->get('nia.core.mailsender');
    }
}
