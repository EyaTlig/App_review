<?php

namespace App\Repository;

use App\Entity\Review;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ReviewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Review::class);
    }

    // Ex : méthode pour compter les nouvelles reviews
    public function countNewReviews(): int
    {
        return $this->createQueryBuilder('r')
            ->select('count(r.id)')
            ->where('r.createdAt > :since')
            ->setParameter('since', new \DateTime('-1 day'))
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countReviewsByOwner(User $owner): int
    {
        return $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->join('r.business', 'b')
            ->where('b.owner = :owner')
            ->setParameter('owner', $owner)
            ->getQuery()
            ->getSingleScalarResult();
    }

}
