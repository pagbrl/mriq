<?php

namespace App\Message;

use Symfony\Component\DependencyInjection\LazyProxy\PhpDumper\NullDumper;

final class SlackPostChatMessage
{
    /**
     *
     * @var string
     */
    private $to;

    /**
     *
     * @var string
     */
    private $text;

    /**
     *
     * @var array|null
     */
    private $attachments;

    public function __construct(string $to, string $text, array $attachments = null)
    {
        $this->to = $to;
        $this->text = $text;
        $this->attachments = $attachments;
    }

    /**
     *
     * @return string
     */
    public function getTo(): string
    {
        return $this->to;
    }

    /**
     *
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     *
     * @return array|null
     */
    public function getAttachments(): array|null
    {
        return $this->attachments;
    }
}
