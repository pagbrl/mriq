<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity(repositoryClass: "App\Repository\TransactionRepository")]
class Transaction
{

    const AVAILABLE_REACTIONS = array(
        self::REACTION_JOY,
        self::REACTION_THUMBSUP,
        self::REACTION_HEART,
        self::REACTION_POOP
    );

    const REACTION_HEART = 'heart';
    const REACTION_JOY = 'joy';
    const REACTION_THUMBSUP = 'thumbsup';
    const REACTION_POOP = 'poop';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private $id;

    /**
     * @var User
     * Many Features have One Product.
     */
    #[ORM\ManyToOne(targetEntity: "App\Entity\User", inversedBy: "givenTransactions")]
    #[ORM\JoinColumn(name: "giver_id", referencedColumnName: "id")]
    private $giver;

    /**
     * @var User
     * Many Features have One Product.
     */
    #[ORM\ManyToOne(targetEntity: "App\Entity\User", inversedBy: "receivedTransactions")]
    #[ORM\JoinColumn(name: "receiver_id", referencedColumnName: "id")]
    private $receiver;

    /**
     * @var int
     */
    #[ORM\Column(type: "integer", nullable: false)]
    private $amount;

    /**
     * @var string
     */
    #[ORM\Column(type: "string", nullable: false)]
    private $reason;

    /**
     * @var bool
     */
    #[ORM\Column(type: "boolean", nullable: false)]
    private $wereLastMriqs = false;

    /**
     * @var string
     */
    #[ORM\Column(type: "string", nullable: true)]
    private $reaction;

    /**
     * @var string
     */
    #[ORM\Column(type: "string", nullable: true)]
    private $mriqChannelMessageTs;

    /**
     * @var string
     */
    #[ORM\Column(type: "string", nullable: true)]
    private $mriqSlackbotMessageTs;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return Transaction
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return User
     */
    public function getGiver(): User
    {
        return $this->giver;
    }

    /**
     * @param User $giver
     * @return Transaction
     */
    public function setGiver(User $giver): Transaction
    {
        $this->giver = $giver;
        return $this;
    }

    /**
     * @return User
     */
    public function getReceiver(): User
    {
        return $this->receiver;
    }

    /**
     * @param User $receiver
     * @return Transaction
     */
    public function setReceiver(User $receiver): Transaction
    {
        $this->receiver = $receiver;
        return $this;
    }

    /**
     * @return int
     */
    public function getAmount(): int
    {
        return $this->amount;
    }

    /**
     * @param int $amount
     * @return Transaction
     */
    public function setAmount(int $amount): Transaction
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * @return string
     */
    public function getReason(): string
    {
        return $this->reason;
    }

    /**
     * @param string $reason
     * @return Transaction
     */
    public function setReason(string $reason): Transaction
    {
        $this->reason = $reason;
        return $this;
    }

    /**
     * @return bool
     */
    public function getWereLastMriqs(): bool
    {
        return $this->wereLastMriqs;
    }

    /**
     * @param bool $wereLastBriqs
     * @return Transaction
     */
    public function setWereLastMriqs(bool $wereLastMriqs): Transaction
    {
        $this->wereLastMriqs = $wereLastMriqs;
        return $this;
    }

    /**
     * @return string
     */
    public function getMriqChannelMessageTs()
    {
        return $this->mriqChannelMessageTs;
    }

    /**
     * @param string $mriqChannelMessageTs
     * @return Transaction
     */
    public function setMriqChannelMessageTs(string $mriqChannelMessageTs): Transaction
    {
        $this->mriqChannelMessageTs = $mriqChannelMessageTs;
        return $this;
    }

    /**
     * @return string
     */
    public function getReaction()
    {
        return $this->reaction;
    }

    /**
     * @param string $reaction
     * @return Transaction
     */
    public function setReaction(string $reaction): Transaction
    {
        $this->reaction = $reaction;
        return $this;
    }

    /**
     * @return string
     */
    public function getMriqSlackbotMessageTs()
    {
        return $this->mriqSlackbotMessageTs;
    }

    /**
     * @param string $mriqSlackbotMessageTs
     * @return Transaction
     */
    public function setMriqSlackbotMessageTs(string $mriqSlackbotMessageTs): Transaction
    {
        $this->mriqSlackbotMessageTs = $mriqSlackbotMessageTs;
        return $this;
    }
}
