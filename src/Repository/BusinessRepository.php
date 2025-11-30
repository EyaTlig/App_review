<?php


namespace App\Repository;

use App\Entity\Business;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class BusinessRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Business::class);
    }

    // Exemple : récupérer les derniers businesses ajoutés
    public function findLatest(int $limit = 5)
    {
        return $this->createQueryBuilder('b')
            ->orderBy('b.id', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    // src/Repository/BusinessRepository.php
    public function findBySearchAndCategory(?string $search, ?int $categoryId)
    {
        $qb = $this->createQueryBuilder('b')
            ->leftJoin('b.category', 'c')
            ->addSelect('c');

        if ($search) {
            $qb->andWhere('b.name LIKE :search OR c.name LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        if ($categoryId) {
            $qb->andWhere('c.id = :catId')
                ->setParameter('catId', $categoryId);
        }

        return $qb->getQuery()->getResult();
    }
// src/Repository/BusinessRepository.php
    public function findByOwnerAndSearch(\App\Entity\User $owner, ?string $search)
    {
        $qb = $this->createQueryBuilder('b')
            ->leftJoin('b.category', 'c')   // join sur la relation category
            ->addSelect('c')
            ->where('b.owner = :owner')
            ->setParameter('owner', $owner);

        if ($search) {
            $qb->andWhere('b.name LIKE :s OR c.name LIKE :s')
                ->setParameter('s', '%'.$search.'%');
        }

        return $qb->getQuery()->getResult();
    }


}
