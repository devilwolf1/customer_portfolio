<?php

namespace App\Repository;

use App\Entity\OrderItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<OrderItem>
 *
 * @method OrderItem|null find($id, $lockMode = null, $lockVersion = null)
 * @method OrderItem|null findOneBy(array $criteria, array $orderBy = null)
 * @method OrderItem[]    findAll()
 * @method OrderItem[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OrderItem::class);
    }

    /**
     * Récupère les items d'une commande
     *
     * @return OrderItem[]
     */
    public function findByOrderId(int $orderId): array
    {
        return $this->createQueryBuilder('oi')
            ->andWhere('oi.order = :order_id')
            ->setParameter('order_id', $orderId)
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère les produits les plus vendus
     *
     * @return OrderItem[]
     */
    public function findTopSellingProducts(int $limit = 10): array
    {
        return $this->createQueryBuilder('oi')
            ->select('p.id, p.name, SUM(oi.quantity) as total_quantity')
            ->innerJoin('oi.product', 'p')
            ->groupBy('p.id, p.name')
            ->orderBy('total_quantity', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
