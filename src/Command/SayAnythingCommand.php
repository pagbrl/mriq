<?php

namespace App\Command;

use App\Entity\User;
use App\Manager\MriqManager;
use App\Manager\SlackManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\Input;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

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
            ->addArgument('channel', InputArgument::REQUIRED)
            ->addArgument('users', InputArgument::IS_ARRAY | InputArgument::OPTIONAL)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $inputString = $io->ask('Text to say ?');

        $channel = $this->slackManager->retrieveChannel($input->getArgument('channel'));
        if (null === $channel) {
            throw new BadRequestHttpException('No such channel was found.');
        }

        if (!empty($input->getArgument('users'))) {
            $users = [];
            $rawUsers = $input->getArgument('users');
            foreach ($rawUsers as $rawUser) {
                /** @var User $user */
                $user = $this->em->getRepository(User::class)->findOneBySlackName($rawUser);
                if (null !== $user) {
                    $users[] = $user->getSlackMentionableName();
                }
            }

            if (substr_count($inputString, '%s') === count($users)) {
                $rawString = vsprintf(
                    $inputString,
                    $users
                );
            } else {
                throw new BadRequestHttpException('Users number didn\'t match expected');
            }
        } else {
            $rawString = $inputString;
        }

        $this->slackManager->sendMessage(
            $channel,
            $rawString
        );
    }
}
