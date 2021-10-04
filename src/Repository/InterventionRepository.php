<?php

namespace App\Repository;

use App\Entity\Intervention;
use App\Entity\Taches;
use App\Entity\FamilleTache;
use App\Entity\Projet;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Intervention|null find($id, $lockMode = null, $lockVersion = null)
 * @method Intervention|null findOneBy(array $criteria, array $orderBy = null)
 * @method Intervention[]    findAll()
 * @method Intervention[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InterventionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Intervention::class);
    }

    // /**
    //  * @return Intervention[] Returns an array of Intervention objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('i.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Intervention
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    
    public function search($valU,$valD,$valT,$valF,$valP){
        $req=$this->createQueryBuilder('i')->Join('i.tache','t')->Where('t.fini= false')->Join('t.famille',"f")->innerJoin( 'f.id_projet','p');
        if($valU!="null" && $valU!=""){
            $req=$req->andWhere('i.Le_user= :valU')->setParameter('valU', $valU);
        }
        if($valD!="null"){
            $req=$req->andWhere('i.Date= :valD')->setParameter('valD', $valD);
        }
        if($valT!="null" ){
            $req=$req->andWhere('i.tache= :valT')->setParameter('valT', $valT);
        }
        if($valF!="null" ){
            $req=$req->andWhere('f.id= :valF')->setParameter('valF', $valF);
        }
        if($valP!="null"){
            $req=$req->andWhere('p.id= :valP')->setParameter('valP', $valP);
        }
                  
        $req=$req->getQuery()->getResult();
        return($req);
    }
}
