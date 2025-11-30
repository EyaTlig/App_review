<?php

namespace App\Repository;

use App\Entity\ReviewPhoto;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class

ReviewPhotoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ReviewPhoto::class);
    }

    // Méthode pour compter les nouvelles photos
    public function countNew(\DateTimeInterface $since): int
    {
        return (int) $this->createQueryBuilder('rp')
            ->select('COUNT(rp.id)')
            ->where('rp.createdAt > :since')
            ->setParameter('since', $since)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
