<?php

namespace App\Command;

use App\Manager\MriqManager;
use App\Manager\SlackManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class UsersUpdateCommand extends Command
{
    protected static $defaultName = 'users:update';

    /**
     * @var MriqManager
     */
    private $mriqManager;

    public function __construct(SlackManager $slackManager, MriqManager $mriqManager)
    {
        parent::__construct();
        $this->mriqManager = $mriqManager;
    }

    protected function configure()
    {
        $this
            ->setDescription('Updates the list of users using Slack API')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $results = $this->mriqManager->updateUsersList();

        $io->text(sprintf(
            'There are %s users in this list, (%s users added)',
            count($results['known']) + count($results['added']),
            count($results['added'])
        ));

        $added = array_map(function ($name) {
            return sprintf('<info>NEW! %s</info>', $name);
        }, $results['added']);

        $display = array_merge($results['known'], $added);

        $io->listing($display);
    }
}
