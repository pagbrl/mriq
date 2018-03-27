<?php

namespace App\Command;

use App\Entity\User;
use App\Manager\MriqManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MriqFaucetCommand extends Command
{
    protected static $defaultName = 'mriq:faucet';

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var int
     */
    private $maxMriqAmount;

    /**
     * @var int
     */
    private $faucetAmount;

    /**
     * MriqFaucetCommand constructor.
     * @param EntityManagerInterface $em
     * @param int $maxMriqAmount
     */
    public function __construct(EntityManagerInterface $em, int $maxMriqAmount, int $faucetAmount)
    {
        parent::__construct();
        $this->em = $em;
        $this->maxMriqAmount = $maxMriqAmount;
        $this->faucetAmount = $faucetAmount;
    }

    protected function configure()
    {
        $this
            ->setDescription('Gives one mriq every 4 hours to all the users that have not reached the max mriq amount.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $users = $this->em->getRepository(User::class)->findFaucetUsers($this->maxMriqAmount);
        $count = 0;

        /** @var User $user */
        foreach ($users as $user) {
            $user->updateToGive($this->faucetAmount);
            $this->em->persist($user);
            $count++;
        }

        $io->success(sprintf(
            'Gave %s mriq to %s user(s)',
            $this->faucetAmount,
            $count
        ));

        $this->em->flush();
    }
}
