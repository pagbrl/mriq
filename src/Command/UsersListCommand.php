<?php

namespace App\Command;

use App\Entity\User;
use App\Manager\SlackManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class UsersListCommand extends Command
{
    protected static $defaultName = 'users:list';

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * UsersUpdateCommand constructor.
     * @param SlackManager $slackManager
     * @param EntityManagerInterface $em
     */
    public function __construct(SlackManager $slackManager, EntityManagerInterface $em)
    {
        parent::__construct();
        $this->em = $em;
    }

    protected function configure()
    {
        $this
            ->setDescription('Lists users')
            ->addOption('--email')
            ->addOption('--leader')
            ->addOption('--giver')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        if ($input->getOption('leader')) {
            $users = $this->em->getRepository(User::class)->findLeaderBoard();
        } elseif ($input->getOption('giver')) {
            $users = $this->em->getRepository(User::class)->findGiverBoard();
        } else {
            $users = $this->em->getRepository(User::class)->findAll();
        }

        $usersListHeaders = [
            'Rank',
            'Id',
            'Slack id',
            'Slack name',
            'Slack real name',
            'Mriqs left',
            'Mriqs given',
            'Mriqs received',
        ];
        $usersList = [];
        /** @var User $user */
        foreach ($users as $index => $user) {
            $usersList[] = [
                sprintf('#%s', bcadd($index, 1)),
                $user->getId(),
                $user->getSlackId(),
                $user->getSlackName(),
                $user->getSlackRealName(),
                $user->getToGive(),
                $user->getTotalGiven(),
                $user->getTotalEarned(),
            ];

            if ($input->getOption('email')) {
                echo $user->getEmail().PHP_EOL;
            }
        }

        if ($input->getOption('email')) {
            return;
        }

        $io->text(sprintf(
            'There are <info>%s</info> items in this list',
            count($usersList)
        ));
        $io->table($usersListHeaders, $usersList);
    }
}
