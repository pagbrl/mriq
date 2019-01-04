<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 */
class User
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=10, unique=true, nullable=false)
     */
    private $slackId;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=false)
     */
    private $slackName;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=false)
     */
    private $slackRealName;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $email;

    /**
     * @var int
     * @ORM\Column(type="integer", nullable=false)
     */
    private $totalGiven = 0;

    /**
     * @var int
     * @ORM\Column(type="integer", nullable=false)
     */
    private $totalEarned = 0;

    /**
     * @var int
     * @ORM\Column(type="integer", nullable=false)
     */
    private $toGive = 0;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Transaction", mappedBy="giver")
     */
    private $givenTransactions;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Transaction", mappedBy="receiver")
     */
    private $receivedTransactions;

    /**
     * @return string
     */
    public function getSlackId(): string
    {
        return $this->slackId;
    }

    public function __construct()
    {
        $this->givenTransactions = new ArrayCollection();
        $this->receivedTransactions = new ArrayCollection();
    }

    /**
     * @param string $slackId
     * @return User
     */
    public function setSlackId(string $slackId): User
    {
        $this->slackId = $slackId;
        return $this;
    }

    /**
     * @return string
     */
    public function getSlackName(): string
    {
        return $this->slackName;
    }

    /**
     * @param string $slackName
     * @return User
     */
    public function setSlackName(string $slackName): User
    {
        $this->slackName = $slackName;
        return $this;
    }

    /**
     * @return string
     */
    public function getSlackRealName(): string
    {
        return $this->slackRealName;
    }

    /**
     * @param string $slackRealName
     * @return User
     */
    public function setSlackRealName(string $slackRealName): User
    {
        $this->slackRealName = $slackRealName;
        return $this;
    }

    /**
     * @return int
     */
    public function getTotalGiven(): int
    {
        return $this->totalGiven;
    }

    /**
     * @param int $totalGiven
     * @return User
     */
    public function setTotalGiven(int $totalGiven): User
    {
        $this->totalGiven = $totalGiven;
        return $this;
    }

    /**
     * @return int
     */
    public function getTotalEarned(): int
    {
        return $this->totalEarned;
    }

    /**
     * @param int $totalEarned
     * @return User
     */
    public function setTotalEarned(int $totalEarned): User
    {
        $this->totalEarned = $totalEarned;
        return $this;
    }

    /**
     * @return int
     */
    public function getToGive(): int
    {
        return $this->toGive;
    }

    /**
     * @param int $toGive
     * @return User
     */
    public function setToGive(int $toGive): User
    {
        $this->toGive = $toGive;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return User
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param int $amount
     * @return $this
     */
    public function receiveBriqs(int $amount)
    {
        $previousBalance = $this->getTotalEarned();

        $newBalance = bcadd($previousBalance, $amount);
        $this->setTotalEarned($newBalance);

        return $this;
    }

    /**
     * @param int $amount
     * @return $this
     */
    public function giveBriqs(int $amount)
    {
        $previousBalance = $this->getToGive();
        $previousGiven = $this->getTotalGiven();

        $newBalance = bcsub($previousBalance, $amount);
        $newGiven = bcadd($previousGiven, $amount);

        $this->setToGive($newBalance)
            ->setTotalGiven($newGiven);

        return $this;
    }

    /**
     * @param int $amount
     * @return $this
     */
    public function updateToGive(int $amount)
    {
        $previousToGive = $this->getToGive();
        $newToGive = bcadd($previousToGive, $amount);

        $this->setToGive($newToGive);
        return $this;
    }

    /**
     * @return string
     */
    public function getSlackMentionableName()
    {
        return sprintf(
            '<@%s>',
            $this->getSlackId()
        );
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return User
     */
    public function setEmail(string $email): User
    {
        $this->email = $email;
        return $this;
    }
}
