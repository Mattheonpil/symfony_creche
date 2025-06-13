<?php

namespace App\Repository;

use DateTime;
use App\Entity\Planning;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Planning>
 */
class PlanningRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Planning::class);
    }

    //    /**
    //     * @return Planning[] Returns an array of Planning objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Planning
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }


public function findByDate(\DateTime $date): array
{
    return $this->createQueryBuilder('p')
        ->where('p.date >= :start')
        ->andWhere('p.date < :end')
        ->setParameter('start', $date->format('Y-m-d').' 00:00:00')
        ->setParameter('end', $date->format('Y-m-d').' 23:59:59')
        ->getQuery()
        ->getResult();
}


public function findDayPlanningsWithChildren(\DateTime $date): array
{
    return $this->createQueryBuilder('p')
        ->select('p', 'c')
        ->join('p.child', 'c')
        ->where('p.date = :date')
        // Changez cette ligne pour inclure les présences
        // ->andWhere('p.absence = :absence')
        ->setParameter('date', $date->format('Y-m-d'))
        // ->setParameter('absence', false)  // Changez en true pour voir les absences
        ->orderBy('p.start_time', 'ASC')
        ->addOrderBy('c.first_name', 'ASC')
        ->getQuery()
        ->getResult();
}


}
