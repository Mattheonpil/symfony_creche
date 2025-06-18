<?php

namespace App\Repository;

use DateTime;
use App\Entity\Child;
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
    $currentTime = new \DateTime();
    $currentHour = $currentTime->format('H:i');

    return $this->createQueryBuilder('p')
        ->where('p.date = :date')
        ->andWhere('p.absence = false')
        ->andWhere('p.start_time <= :currentTime')
        ->andWhere('p.end_time >= :currentTime')
        ->setParameter('date', $date->format('Y-m-d'))
        ->setParameter('currentTime', $currentHour)
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

public function countChildrenByDate(\DateTime $date): int
{
    return $this->createQueryBuilder('p')
        ->select('COUNT(DISTINCT p.child)')
        ->where('p.date = :date')
        ->andWhere('p.absence = false')
        ->setParameter('date', $date->format('Y-m-d'))
        ->getQuery()
        ->getSingleScalarResult();
}

public function countMealsByDate(\DateTime $date): int
{
    return $this->createQueryBuilder('p')
        ->select('COUNT(p.id)')
        ->where('p.date = :date')
        ->andWhere('p.absence = :isPresent') 
        ->andWhere('p.start_time <= :endLunch')
        ->andWhere('p.end_time >= :startLunch')
        ->setParameter('date', $date->format('Y-m-d'))
        ->setParameter('isPresent', false)     // false = 0 = présent
        ->setParameter('endLunch', '14:00')
        ->setParameter('startLunch', '12:00')
        ->getQuery()
        ->getSingleScalarResult();
}

public function findChildrenByDate(\DateTime $date): array
{
    return $this->createQueryBuilder('p')
        ->select('p', 'c')
        ->join('p.child', 'c')
        ->where('p.date = :date')
        ->andWhere('p.absence = false')
        ->setParameter('date', $date->format('Y-m-d'))
        ->orderBy('c.first_name', 'ASC')
        ->getQuery()
        ->getResult();
}

public function deletePlanningBetweenDates(Child $child, \DateTime $dateDebut, \DateTime $dateFin): void
{
    $this->createQueryBuilder('p')
        ->delete()
        ->where('p.child = :child')
        ->andWhere('p.date BETWEEN :dateDebut AND :dateFin')
        ->setParameter('child', $child)
        ->setParameter('dateDebut', $dateDebut->format('Y-m-d'))
        ->setParameter('dateFin', $dateFin->format('Y-m-d'))
        ->getQuery()
        ->execute();
}

public function findLastPlanningDate(Child $child): ?\DateTime
{
    $result = $this->createQueryBuilder('p')
        ->select('p.date')
        ->where('p.child = :child')
        ->setParameter('child', $child)
        ->orderBy('p.date', 'DESC')
        ->setMaxResults(1)
        ->getQuery()
        ->getOneOrNullResult();

    if (!$result) {
        return null;
    }

    return $result['date'] instanceof \DateTime ? $result['date'] : null;
}
}
