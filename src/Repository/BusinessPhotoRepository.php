<?php

namespace App\Repository;

use App\Entity\BusinessPhoto;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class BusinessPhotoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BusinessPhoto::class);
    }

    // Méthode pour compter les nouvelles photos
    public function countNew(\DateTimeInterface $since): int
    {
        return (int) $this->createQueryBuilder('bp')
            ->select('COUNT(bp.id)')
            ->where('bp.createdAt > :since')
            ->setParameter('since', $since)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
