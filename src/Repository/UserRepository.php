<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry as RegistryInterface;

/**
 * @method null|User find($id, $lockMode = null, $lockVersion = null)
 * @method null|User findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * @return mixed|User
     */
    public function findUserBySlackId(string $slackId)
    {
        return $this->createQueryBuilder('u')
            ->where('u.slackId = :slackId')
            ->setParameter('slackId', $slackId)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findFaucetUsers(int $maxMriqAmount)
    {
        return $this->createQueryBuilder('u')
            ->where('u.toGive < :maxMriqAmount')
            ->setParameter('maxMriqAmount', $maxMriqAmount)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findLeaderBoard()
    {
        return $this->createQueryBuilder('u')
            ->orderBy('u.totalEarned', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findGiverBoard()
    {
        return $this->createQueryBuilder('u')
            ->orderBy('u.totalGiven', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }
}
