<?php

namespace App\MessageHandler;

use App\Manager\SlackManager;
use App\Message\SlackPostChatMessage;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

final class SlackPostChatMessageHandler implements MessageHandlerInterface
{
    /**
     *
     * @var SlackManager
     */
    private $slackManager;

    /**
     *
     * @var LoggerInterface
     */
    private $logger;

    /**
     *
     * @param SlackManager $slackManager
     * @param LoggerInterface $logger
     * @return void
     */
    public function __construct(SlackManager $slackManager, LoggerInterface $logger)
    {
        $this->slackManager = $slackManager;
        $this->logger = $logger;
    }

    /**
     *
     * @param SlackPostChatMessage $message
     * @return mixed
     * @throws TransportExceptionInterface
     */
    public function __invoke(SlackPostChatMessage $message)
    {
        $this->logger->info(sprintf(
            'Sending message to user %s – %s',
            $message->getTo(),
            $message->getText(),
        ));

        return $this->slackManager->call('chat.postMessage', [
            'channel' => $message->getTo(),
            'text' => $message->getText(),
            'attachments' => json_encode($message->getAttachments()),
            'as_user' => false,
        ])->getContent();
    }
}
