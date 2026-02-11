<?php

namespace App\Repository;

use App\Entity\UserSerie;
use App\Entity\User;
use App\Entity\Serie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserSerie>
 */
class UserSerieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserSerie::class);
    }

    //    /**
    //     * @return UserSerie[] Returns an array of UserSerie objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('u.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?UserSerie
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    // public function findAllChronologicaly(string $list): array
    // {
    //     return $this->createQueryBuilder('s')
    //         ->select('DISTINCT s.' . $list)
    //         ->andWhere('s.list = :' . $list)
    //         ->orderBy('s.added_at')
    //         ->getQuery()
    //         ->getSingleColumnResult();
    // }

    public function findOneByUserAndSerie(User $user, Serie $serie): ?UserSerie
    {
        return $this->findOneBy([
            'user' => $user,
            'serie' => $serie,
        ]);
    }

    public function findByUserAndList(User $user, string $list): array
    {
        return $this->createQueryBuilder('us')
            ->andWhere('us.user = :user')
            ->andWhere('us.list = :list')
            ->setParameter('user', $user)
            ->setParameter('list', $list)
            ->orderBy('us.addedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

}
