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
// Dans BusinessRepository.php
    public function findWithFilters(array $filters = [])
    {
        $qb = $this->createQueryBuilder('b')
            ->leftJoin('b.category', 'c')
            ->leftJoin('b.reviews', 'r')
            ->addSelect('c')
            ->groupBy('b.id');

        // Filtre par recherche
        if (!empty($filters['search'])) {
            $qb->andWhere('b.name LIKE :search OR b.description LIKE :search')
                ->setParameter('search', '%' . $filters['search'] . '%');
        }

        // Filtre par catégorie
        if (!empty($filters['category'])) {
            $qb->andWhere('c.id = :categoryId')
                ->setParameter('categoryId', $filters['category']);
        }

        // Filtre par note minimale
        if (!empty($filters['min_rating'])) {
            $qb->andHaving('AVG(r.rating) >= :minRating')
                ->setParameter('minRating', $filters['min_rating']);
        }

        // Filtre par site web
        if (!empty($filters['has_website'])) {
            $qb->andWhere('b.website IS NOT NULL');
        }


        // Tri
        switch ($filters['sort'] ?? 'newest') {
            case 'name_asc':
                $qb->orderBy('b.name', 'ASC');
                break;
            case 'name_desc':
                $qb->orderBy('b.name', 'DESC');
                break;
        }

        return $qb->getQuery()->getResult();
    }

}
