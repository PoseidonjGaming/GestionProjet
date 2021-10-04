<?php

namespace App\Repository;

use App\Entity\FamilleTache;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method FamilleTache|null find($id, $lockMode = null, $lockVersion = null)
 * @method FamilleTache|null findOneBy(array $criteria, array $orderBy = null)
 * @method FamilleTache[]    findAll()
 * @method FamilleTache[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FamilleTacheRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FamilleTache::class);
    }

    // /**
    //  * @return FamilleTache[] Returns an array of FamilleTache objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('f.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?FamilleTache
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
    public function findOne($val){
        return $this->createQueryBuilder('f')
            ->andWhere('f.id = :val')
            ->setParameter('val', $val)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function OrderASCNull($val){
        return $this->createQueryBuilder('p')
        ->Where('p.id_projet = :val' )
        ->orderBy('p.nom','ASC')
        ->setParameter('val', $val)
        ->getQuery()
        ->getResult();
    }
    public function findWhere($val){
        return $this->createQueryBuilder('p')
        ->Where('p.id_projet = :val' )
        ->setParameter('val', $val)
        ->orderBy('p.nom','ASC')        
        ->getQuery()
        ->getResult();
    }

    public function findNameProjet($val, $valP){
        return $this->createQueryBuilder('p')
        ->Where('p.nom = :val' )
        ->andWhere('p.id_projet = :valP')
        ->setParameter('val', $val)
        ->setParameter('valP', $valP)    
        ->getQuery()
        ->getOneOrNullResult();
    }
    
}
