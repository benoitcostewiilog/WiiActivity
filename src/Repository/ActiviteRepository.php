<?php

namespace App\Repository;

use App\Entity\Activite;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Activite|null find($id, $lockMode = null, $lockVersion = null)
 * @method Activite|null findOneBy(array $criteria, array $orderBy = null)
 * @method Activite[]    findAll()
 * @method Activite[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ActiviteRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Activite::class);
    }

    public function compte()
    {
        return $this->createQueryBuilder('a')
                    ->select('COUNT(a)')
                    ->getQuery()
                    ->getSingleScalarResult();
    }

    public function findByFilter($utilisateur, $site, $projet, $datedebut, $datefin, $sort, $searchPhrase)
    {
        $qb = $this->createQueryBuilder('activite');
        $parameters = [];
        $key_id = 0;

    
        if ($site) {
            $query = "";
            $qb->leftJoin("activite.site", 'site');
            foreach ($site as $key => $value) {

                $query = $query . "site.name = ?" . $key_id . " OR ";
                $parameters[$key_id] = $value;
                $key_id += 1;
            }
            $query = substr($query, 0, -4);
            $qb
                ->andWhere($query)
                ->setParameters($parameters);
        }

        if ($utilisateur) {
            $query = "";
            $qb->leftJoin("activite.utilisateur", 'u');
            foreach ($utilisateur as $key => $value) {
                $query = $query . "u.nom = ?" . $key_id . " OR ";
                $parameters[$key_id] = $value;
                $key_id += 1;
            }
            $query = substr($query, 0, -4);
            $qb->andWhere($query)
            ->setParameters($parameters);
        }

        if ($projet) {
            $query = "";
            $qb->leftJoin("activite.projet", 'p');
            foreach ($projet as $key => $value) {
                $query = $query . "p.name = ?" . $key_id . " OR ";
                $parameters[$key_id] = $value;
                $key_id += 1;
            }
            $query = substr($query, 0, -4);
            $qb->andWhere($query)
            ->setParameters($parameters);
        }

        if ($datedebut && $datefin) {
            $qb->andWhere('activite.date >= :from')
			->andWhere('activite.date <= :to')
			->setParameter('from', $datedebut)
			->setParameter('to', $datefin);
        }

        if ($searchPhrase != "" || $projet) {
            $qb->leftJoin('activite.projet', 'projet');
        }

        if ($searchPhrase != "") {
            $qb->leftJoin("activite.site", 'site')
                 ->andWhere('site.name LIKE :search
                OR activite.temps LIKE :search
                OR activite.tache LIKE :search
                OR projet.name LIKE :search
                OR activite.date LIKE :search
            ')
                ->setParameter('search', '%' . $searchPhrase . '%');
        }

        if ($sort) {
            foreach ($sort as $key => $value) {
                $qb->orderBy('activite.' . $key, $value);
            }
        } else {
            $qb->orderBy('activite.date', 'ASC');
        }

        return $qb;
    }

    // /**
    //  * @return Activite[] Returns an array of Activite objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Activite
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
