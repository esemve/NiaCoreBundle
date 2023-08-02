<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Utils;

use Nia\CoreBundle\Security\Factory\ContextFactory;
use Nia\MediaBundle\Handler\ThumbnailHandler;
use Nia\MediaBundle\Provider\StorageProvider;
use Nia\MediaBundle\ValueObject\AbstractImage;

class MailSender
{
    /**
     * @var \Twig_Environment
     */
    private $twig;
    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    private $swiftmailerTransport;
    /**
     * @var string
     */
    private $defaultEmail;
    /**
     * @var string
     */
    private $defaultName;
    /**
     * @var string
     */
    private $publicRoot;
    /**
     * @var string
     */
    private $storagePath;
    /**
     * @var StorageProvider
     */
    private $storageProvider;
    /**
     * @var ThumbnailHandler
     */
    private $thumbnailHandler;
    /**
     * @var ContextFactory
     */
    private $contextFactory;

    public function __construct(string $publicRoot, string $storagePath, \Twig_Environment $twig, \Swift_Mailer $mailer, $swiftmailerTransport, string $defaultEmail, string $defaultName, StorageProvider $storageProvider, ThumbnailHandler $thumbnailHandler, ContextFactory $contextFactory)
    {
        $this->twig = $twig;
        $this->mailer = $mailer;
        $this->swiftmailerTransport = $swiftmailerTransport;
        $this->defaultEmail = $defaultEmail;
        $this->defaultName = $defaultName;
        $this->publicRoot = realpath($publicRoot);
        $this->storagePath = realpath($storagePath);
        $this->storageProvider = $storageProvider;
        $this->thumbnailHandler = $thumbnailHandler;
        $this->contextFactory = $contextFactory;
    }

    public function sendTemplate(
        string $toEmail,
        string $subject,
        string $htmlTemplate,
        string $txtTemplate,
        array $templateParameters,
        ?string $senderEmail = null,
        ?string $senderName = null,
        ?callable $messageModifier = null
    ): void {
        $this->sendMessage(
            $this->getMessageByTemplate($toEmail, $subject, $htmlTemplate, $txtTemplate, $templateParameters, $senderEmail, $senderName, $messageModifier)
        );
    }

    public function getMessageByTemplate(
        string $toEmail,
        string $subject,
        string $htmlTemplate,
        string $txtTemplate,
        array $templateParameters,
        ?string $senderEmail = null,
        ?string $senderName = null,
        ?callable $messageModifier = null
    ): \Swift_Message {
        $html = $this->twig->render($htmlTemplate, $templateParameters);

        if (!empty($txtTemplate)) {
            $txt = $this->twig->render($txtTemplate, $templateParameters);
        } else {
            $txt = '';
        }

        return $this->getMessage($toEmail, $subject, $html, $txt, $senderEmail, $senderName, $messageModifier);
    }

    public function send(
        string $toEmail,
        string $subject,
        string $html,
        string $txt,
        ?string $senderEmail = null,
        ?string $senderName = null,
        ?callable $messageModifier = null
    ): void {
        $this->sendMessage(
            $this->getMessage($toEmail, $subject, $html, $txt, $senderEmail, $senderName, $messageModifier)
        );
    }

    public function getMessage(
        string $toEmail,
        string $subject,
        string $html,
        string $txt,
        ?string $senderEmail = null,
        ?string $senderName = null,
        ?callable $messageModifier = null
    ): \Swift_Message {
        if (empty($senderEmail)) {
            $senderEmail = $this->defaultEmail;
        }

        if (empty($senderName)) {
            $senderName = $this->defaultName;
        }

        $message = (new \Swift_Message($subject))
            ->setTo($toEmail)
            ->setFrom($senderEmail, $senderName)
            ->setBody(
                $html,
                'text/html'
            );

        if (!empty($txt)) {
            $message->addPart(
                $txt,
                'text/plain'
            );
        }

        if (!empty($messageModifier)) {
            $messageModifier($message);
        }

        return $message;
    }

    public function sendMessage(\Swift_Message $message): int
    {
        $message = $this->embedImages($message);

        return $this->mailer->send($message);
    }

    /**
     * Ezt az egész taknyot itt ki kell majd faszázni, mert kiborulok tőle!
     *
     * @param \Swift_Message $message
     *
     * @return \Swift_Messag
     */
    protected function embedImages(\Swift_Message $message): \Swift_Message
    {
        $context = $this->contextFactory->createServiceContext(\get_class($this).':'.__FUNCTION__);

        $body = $message->getBody();
        $replaced = [];

        preg_match_all('/<img[^>]+>/i', $body, $result);
        foreach ($result[0] as $img_tag) {
            preg_match_all('/(src)=("[^"]*")/i', $img_tag, $src);

            if (isset($src[2])) {
                $src = str_replace(['"', "'"], ['', ''], $src[2][0]);

                if (isset($replaced[$src])) {
                    continue;
                }

                $imagePath = $this->publicRoot.'/'.$src;
                if ('/storage/images/original' === mb_substr($src, 0, 24)) {
                    $imagePath = $this->storagePath.'/'.mb_substr($src, 25);
                }

                if ('http' === mb_substr($imagePath, 0, 4)) {
                    continue;
                }

                try {
                    $imagePath = str_replace('//', '/', $imagePath);
                    if (!file_exists($imagePath)) {
                        if ('/storage/images/' === mb_substr($src, 0, 16)) {
                            $testPath = explode('/', str_replace('/storage/images/', '', $src));

                            if (3 === \count($testPath)) {
                                $crop = $testPath[0];
                                $fileId = (int) filter_var($testPath[2], FILTER_SANITIZE_NUMBER_INT);

                                $document = $this->storageProvider->provideElement($fileId, $context);
                                if ($document instanceof AbstractImage) {
                                    if ($testPath[1] === $document->getSubFolder()) {
                                        $filePath = str_replace('//', '/', $this->publicRoot.'/storage/images/'.$testPath[0].'/'.$document->getSubFolder().'/'.$document->getId().'.'.$document->getExtension());

                                        try {
                                            $this->thumbnailHandler->generate($document, $crop, false);
                                        } catch (\Throwable $ex) {
                                        }
                                        if (!file_exists($filePath)) {
                                            throw new \Exception('Not found image!!');
                                        }

                                        $file = \Swift_Image::fromPath($filePath);
                                        $replaced[$src] = $message->embed($file);
                                        $body = str_replace($src, $replaced[$src], $body);

                                        continue;
                                    }
                                }
                            }
                        }

                        throw new \Exception('Not found image!');
                    }

                    $extension = mb_substr($imagePath, -4);

                    if (!\in_array(mb_strtolower($extension), ['.jpg', '.png', '.svg', 'jpeg', '.bmp'], true)) {
                        throw new \Exception('Not valid image!');
                    }

                    $file = \Swift_Image::fromPath($imagePath);

                    $replaced[$src] = $message->embed($file);
                    $body = str_replace($src, $replaced[$src], $body);
                } catch (\Throwable $ex) {
                    $replaced[$src] = $src;
                }
            }
        }

        $message->setBody($body);

        return $message;
    }
}
