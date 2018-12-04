<?php

namespace App\Repository;

use App\Entity\Utilisateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Utilisateur|null find($id, $lockMode = null, $lockVersion = null)
 * @method Utilisateur|null findOneBy(array $criteria, array $orderBy = null)
 * @method Utilisateur[]    findAll()
 * @method Utilisateur[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UtilisateurRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Utilisateur::class);
    }

    /**
     * @return Utilisateur[] Returns an array of Utilisateurs objects
     */
    public function findBySearchSort($searchPhrase, $sort)
    {
        $qb = $this->createQueryBuilder('user');
        $parameters = [];
        $key_id = 0;

        if ($searchPhrase != "") {
            $qb->andWhere('user.username LIKE :search
                OR user.nom LIKE :search
                OR user.prenom LIKE :search
                OR user.roles LIKE :search
            ')
                ->setParameter('search', '%' . $searchPhrase . '%');
        }

        if ($sort) {
            foreach ($sort as $key => $value) {
                $qb->orderBy('user.' . $key, $value);
            }
        } else {
            $qb->orderBy('user.nom', 'ASC');
        }

        return $qb;
    }

    // /**
    //  * @return Parcs[] Returns an array of Parcs objects
    //  */
    // public function findBySearchSort($searchPhrase, $sort)
    // {
    //     $qb = $this->createQueryBuilder('user');
    //     $parameters = [];
    //     $key_id = 0;

    //     if ($searchPhrase != "") {
    //         $qb->leftJoin('user.groupe', 'groupe')
    //             ->andWhere('user.username LIKE :search
    //             OR user.email LIKE :search
    //             OR groupe.nom LIKE :search
    //             OR user.roles LIKE :search
    //             OR user.lastLogin LIKE :search
    //         ')
    //             ->setParameter('search', '%' . $searchPhrase . '%');
    //     }

    //     if ($sort) {
    //         foreach ($sort as $key => $value) {
    //             $qb->orderBy('user.' . $key, $value);
    //         }
    //     } else {
    //         $qb->orderBy('user.lastLogin', 'ASC');
    //     }

    //     return $qb;
    // }
    // /**
    //  * @return Utilisateur[] Returns an array of Utilisateur objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
     */

    /*
    public function findOneBySomeField($value): ?Utilisateur
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
     */
}
