<?php

namespace App\Repository;

use App\Entity\Planning;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Planning|null find($id, $lockMode = null, $lockVersion = null)
 * @method Planning|null findOneBy(array $criteria, array $orderBy = null)
 * @method Planning[]    findAll()
 * @method Planning[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PlanningRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Planning::class);
    }

    // /**
    //  * @return Planning[] Returns an array of Planning objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Planning
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function findUser($val)
    {
        return $this->createQueryBuilder('pl')
            ->Where('pl.user = :val')
            ->setParameter('val', $val)
            ->OrderBy('pl.semaine', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findSemaineUser($valS, $valU)
    {
        return $this->createQueryBuilder('pl')
            ->Where('pl.user = :valU')
            ->setParameter('valU', $valU)
            ->andWhere('pl.semaine = :valS')
            ->setParameter('valS', $valS)
            ->getQuery()
            ->getResult()
        ;
    }


    public function findOne($val){
        return $this->createQueryBuilder('pl')
            ->andWhere('pl.id = :val')
            ->setParameter('val', $val)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findWhere($valU, $valT, $valS)
    {
        return $this->createQueryBuilder('pl')
            ->Where('pl.user = :valU')
            ->andWhere('pl.tache = :valT')
            ->andWhere('pl.semaine = :valS')
            ->setParameter('valU', $valU)
            ->setParameter('valT', $valT)
            ->setParameter('valS', $valS)
            ->getQuery()
            ->getResult()
        ;
    }
    
}
