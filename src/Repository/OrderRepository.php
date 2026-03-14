<?php

namespace App\Repository;

use App\Entity\Order;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Order>
 *
 * @method Order|null find($id, $lockMode = null, $lockVersion = null)
 * @method Order|null findOneBy(array $criteria, array $orderBy = null)
 * @method Order[]    findAll()
 * @method Order[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    /**
     * Récupère les commandes triées par date (plus récentes en premier)
     *
     * @return Order[]
     */
    public function findAllSorted(): array
    {
        return $this->createQueryBuilder('o')
            ->orderBy('o.created_at', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère les commandes d'un client spécifique
     *
     * @return Order[]
     */
    public function findByCustomerId(int $customerId): array
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.customer = :customer_id')
            ->setParameter('customer_id', $customerId)
            ->orderBy('o.created_at', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère les commandes par statut
     *
     * @return Order[]
     */
    public function findByStatus(string $status): array
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.status = :status')
            ->setParameter('status', $status)
            ->orderBy('o.created_at', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère le total des ventes par mois (pour reporting)
     *
     * @return array
     */
    public function findMonthlySalesTotal(): array
    {
        return $this->createQueryBuilder('o')
            ->select('MONTH(o.created_at) as month, SUM(o.total) as total')
            ->andWhere('o.status != :cancelled')
            ->setParameter('cancelled', 'cancelled')
            ->groupBy('MONTH(o.created_at)')
            ->orderBy('month', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
