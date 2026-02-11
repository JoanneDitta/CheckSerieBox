<?php

namespace App\Repository;

use App\Entity\Serie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Serie>
 */
class SerieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Serie::class);
    }

    //    /**
    //     * @return Serie[] Returns an array of Serie objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('s.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Serie
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function findAllRandom(): array
    {
        return $this->getEntityManager()
            ->getConnection()
            ->executeQuery('SELECT * FROM serie ORDER BY RAND()')
            ->fetchAllAssociative();
    }

    // public function findRandomLimited(int $limit = 24): array
    // {
    //     return $this->getEntityManager()
    //         ->getConnection()
    //         ->executeQuery(
    //             'SELECT * FROM serie ORDER BY RAND() LIMIT :limit',
    //             ['limit' => $limit],
    //             ['limit' => \PDO::PARAM_INT]
    //         )
    //         ->fetchAllAssociative();
    // }

    public function findByFilters(array $filters): array
    {
        $qb = $this->createQueryBuilder('s');

        if (!empty($filters['type'])) {
            $qb->andWhere('s.type = :type')
            ->setParameter('type', $filters['type']);
        }

        if (!empty($filters['country'])) {
            $qb->andWhere('s.country = :country')
            ->setParameter('country', $filters['country']);
        }

        if (!empty($filters['release_date'])) {
            $qb->andWhere('s.release_date = :release_date')
            ->setParameter('release_date', $filters['release_date']);
        }

        if (!empty($filters['platform'])) {
            $qb->andWhere('s.platform = :platform')
            ->setParameter('platform', $filters['platform']);
        }

        if (!empty($filters['nb_season'])) {
            $qb->andWhere('s.nb_season = :nb_season')
            ->setParameter('nb_season', $filters['nb_season']);
        }

        if (!empty($filters['status'])) {
            $qb->andWhere('s.status = :status')
            ->setParameter('status', $filters['status']);
        }

        return $qb->getQuery()->getResult();
    }

    // -------------------- FIND BY DISTINCT --------------------

    // public function findDistinctTypes(): array
    // {
    //     return $this->createQueryBuilder('s')
    //         ->select('DISTINCT s.type')
    //         ->orderBy('s.type')
    //         ->getQuery()
    //         ->getSingleColumnResult();
    // }

    // public function findDistinctCountries(): array
    // {
    //     return $this->createQueryBuilder('s')
    //         ->select('DISTINCT s.country')
    //         ->orderBy('s.country')
    //         ->getQuery()
    //         ->getSingleColumnResult();
    // }

    // public function findDistinctPlatforms(): array
    // {
    //     return $this->createQueryBuilder('s')
    //         ->select('DISTINCT s.platform')
    //         ->orderBy('s.platform')
    //         ->getQuery()
    //         ->getSingleColumnResult();
    // }

    // public function findDistinctrelease_dates(): array
    // {
    //     return $this->createQueryBuilder('s')
    //         ->select('DISTINCT s.release_date')
    //         ->orderBy('s.release_date')
    //         ->getQuery()
    //         ->getSingleColumnResult();
    // }

    // public function findDistinctNb_seasons(): array
    // {
    //     return $this->createQueryBuilder('s')
    //         ->select('DISTINCT s.nb_season')
    //         ->orderBy('s.nb_season')
    //         ->getQuery()
    //         ->getSingleColumnResult();
    // }

    // public function findDistinctStatuts(): array
    // {
    //     return $this->createQueryBuilder('s')
    //         ->select('DISTINCT s.status')
    //         ->orderBy('s.status')
    //         ->getQuery()
    //         ->getSingleColumnResult();
    // }

    public function findDistinctByField(string $field): array
    {
        $allowedFields = [
            'type',
            'country',
            'platform',
            'release_date',
            'nb_season',
            'status',
        ];

        if (!in_array($field, $allowedFields, true)) {
            throw new \InvalidArgumentException('Champ non autorisé');
        }

        return $this->createQueryBuilder('s')
            ->select('DISTINCT s.' . $field)
            ->orderBy('s.' . $field)
            ->getQuery()
            ->getSingleColumnResult();
    }


}
