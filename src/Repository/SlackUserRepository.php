<?php

namespace App\Repository;

use App\Entity\SlackUser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method SlackUser|null find($id, $lockMode = null, $lockVersion = null)
 * @method SlackUser|null findOneBy(array $criteria, array $orderBy = null)
 * @method SlackUser[]    findAll()
 * @method SlackUser[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SlackUserRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, SlackUser::class);
    }

    /*
    public function findBySomething($value)
    {
        return $this->createQueryBuilder('s')
            ->where('s.something = :value')->setParameter('value', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */
}
