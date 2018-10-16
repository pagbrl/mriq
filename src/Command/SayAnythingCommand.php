<?php

namespace App\Command;

use App\Entity\User;
use App\Manager\MriqManager;
use App\Manager\SlackManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SayAnythingCommand extends Command
{
    protected static $defaultName = 'say:anything';

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var SlackManager
     */
    private $slackManager;

    /**
     * MriqFaucetCommand constructor.
     * @param EntityManagerInterface $em
     * @param int $maxMriqAmount
     */
    public function __construct(EntityManagerInterface $em, SlackManager $slackManager)
    {
        parent::__construct();
        $this->em = $em;
        $this->slackManager = $slackManager;
    }

    protected function configure()
    {
        $this
            ->setDescription('Says things')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $channel = 'C605WUD09';

        /** @var User $user */
        $user = $this->em->getRepository(User::class)->findOneById(51);

        $this->slackManager->sendMessage(
            $channel,
            sprintf('%s, I am perfect.', $user->getSlackMentionableName())
        );
        $this->em->flush();
    }
}
