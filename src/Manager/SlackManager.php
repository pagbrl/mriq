<?php

namespace App\Manager;

use App\Message\SlackPostChatMessage;
use RuntimeException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SlackManager
{
    /**
     * @var string
     */
    private $slackToken;

    /**
     * @var string
     */
    private $apiEndpoint = 'https://slack.com/api/';

    /**
     * @var HttpClientInterface
     */
    private $client;

    /**
     *
     * @var MessageBusInterface
     */
    private $messageBus;

    /**
     * SlackManager constructor.
     */
    public function __construct(HttpClientInterface $client, MessageBusInterface $messageBusInterface, string $slackToken)
    {
        $this->slackToken = $slackToken;
        $this->client = $client;
        $this->messageBus = $messageBusInterface;
    }

    /**
     * @param $method
     * @param array $args
     *
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function call($method, $args = [])
    {
        $body = array_merge($args, ['token' => $this->slackToken]);

        return $this->client->request(
            'POST',
            sprintf(
                '%s/%s',
                $this->apiEndpoint,
                $method
            ),
            [
                'headers' => [],
                'json' => $body,
            ]
        );
    }

    /**
     *
     * @return mixed
     * @throws TransportExceptionInterface
     */
    public function getSlackUsersList()
    {
        return $this->call('users.list');
    }

    /**
     *
     * @param string $to
     * @param string $text
     * @param string $userId
     * @return mixed
     * @throws TransportExceptionInterface
     */
    public function sendEphemeralMessage(string $to, string $text, string $userId)
    {
        return $this->call('chat.postEphemeral', [
            'channel' => $to,
            'text' => $text,
            'user' => $userId,
            'as_user' => false,
        ])->getContent();
    }

    /**
     *
     * @param string $to
     * @param string $text
     * @param array|null $attachments
     * @return void
     */
    public function sendMessage(string $to, string $text, array $attachments = null)
    {
        $message = new SlackPostChatMessage($to, $text, $attachments);
        $this->messageBus->dispatch($message);
    }

    /**
     *
     * @param string $to
     * @param string $text
     * @param array|null $attachments
     * @return string
     */
    public function sendSyncMessage(string $to, string $text, array $attachments = null)
    {
        return $this->call('chat.postMessage', [
            'channel' => $to,
            'text' => $text,
            'attachments' => json_encode($attachments),
            'as_user' => false,
        ])->getContent();
    }

    /**
     *
     * @param string $respondUrl
     * @param string $text
     * @param array|null $attachments
     * @return string
     * @throws TransportExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function respondToAction(string $respondUrl, string $text, array $attachments = null)
    {
        $json_payload = [
            'text' => $text,
            'replace_original' => true,
            'response_type' => 'in_channel',
            'attachments' => $attachments,
        ];

        return $this->client->request(
            'POST',
            $respondUrl,
            ['json' => $json_payload]
        )->getContent();
    }

    /**
     *
     * @param string $ts
     * @param string $to
     * @param string $text
     * @param array|null $attachments
     * @return mixed
     * @throws TransportExceptionInterface
     */
    public function updateChat(string $ts, string $to, string $text, array $attachments = null)
    {
        return $this->call('chat.update', [
            'ts' => $ts,
            'channel' => $to,
            'text' => $text,
            'attachments' => json_encode($attachments),
            'as_user' => false,
        ])->getContent();
    }

    /**
     *
     * @param string $channelName
     * @return mixed
     * @throws TransportExceptionInterface
     * @throws RuntimeException
     */
    public function retrieveChannel(string $channelName)
    {
        $channels = json_decode($this->call('channels.list')->getBody()->getContents(), true)['channels'];
        foreach ($channels as $channel) {
            if ($channel['name'] === $channelName) {
                return $channel['id'];
            }
        }

        return null;
    }
}
