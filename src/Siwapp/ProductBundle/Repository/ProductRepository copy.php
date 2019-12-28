<?php

namespace Siwapp\ProductBundle\Repository;

use Knp\Component\Pager\PaginatorInterface;
use Siwapp\InvoiceBundle\Entity\Invoice;
use Siwapp\CategoryBundle\Entity\Category;
use Siwapp\ProductBundle\Entity\Product;
use Doctrine\ORM\Query\ResultSetMapping;

/**
 * ProductRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ProductRepository extends \Doctrine\ORM\EntityRepository
{

    public function validateStock(string $product): array
    {
        return $this->getEntityManager()
        ->createQueryBuilder()
        ->select('p')
        ->from(Product::class, 'p')
        ->where('p.reference = :product')
        ->setParameter('product', $product)
        ->getQuery()
        ->getResult();
    }
    
    public function findLikeReference(string $term): array
    {
        // return $this->getEntityManager()
        //     ->createQueryBuilder()
        //     ->select('p')
        //     ->from(Product::class, 'p')
        //     ->leftJoin('p.items', 'i')
        //     ->where('p.reference LIKE :term')
        //     ->andWhere('i.stock > 0')
        //     ->setParameter('term', '%'. $term .'%')
        //     ->getQuery()
        //     ->getResult();

            // $q = $this->getEntityManager()
            // ->createQueryBuilder()
            // ->select('p')
            // ->from(Product::class, 'p')
            // ->leftJoin('p.items', 'i')
            // ->where('p.reference LIKE :term')
            // ->andWhere('i.stock > 0')
            // ->setParameter('term', '%'. $term .'%');

            //  $query = $q->getQuery();
            //  die('DQL ' . $query->getSQL());

             $rsm = new ResultSetMapping();
            // build rsm here

            $query = $this->getEntityManager()->createNativeQuery('SELECT * FROM product p 
                                                            left join item i on p.id = i.product_id  
                                                             WHERE p.reference LIKE ? and i.stock > 0 ', $rsm);
            $query->setParameter(1, "%.$term.%");

            return $users = $query->getResult();
        //    die('DQL ' . $users->getSQL());
    }

    public function paginatedSearch(array $params, $limit = 50, $page = 1)
    {
        if (!$this->paginator) {
            throw new \RuntimeException('You have to set a paginator first using setPaginator() method');
        }

        $qb = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('p')
            ->from(Product::class, 'p');
            foreach ($params as $field => $value) {
                if ($value === null) {
                    continue;
                }
                if ($field == 'terms') {
                    $terms = $qb->expr()->literal("%$value%");
                    $qb->andWhere($qb->expr()->orX(
                        $qb->expr()->like('p.reference', $terms),
                        $qb->expr()->like('p.description', $terms)
                    ));
                }
            }

                $qb->leftJoin('p.items', 'i');
                $qb->innerJoin('p.category_id', 'c');
                $qb->leftJoin('i.invoice', 'ii', 'WITH', 'ii.status <> ?1')
                    ->setParameter(1, Invoice::DRAFT);
                // $qb->leftJoin('i.items', 'invo_i', 'WITH', 'ii.status <> ?1')
                $qb->addSelect('SUM(CASE WHEN i.unitary_cost IS NULL OR ii.id IS NULL THEN 0 ELSE i.unitary_cost END * CASE WHEN i.quantity IS NULL THEN 0 ELSE i.quantity END) AS revenue');
                $qb->addSelect('c.categoryName');
                $qb->addSelect('SUM(CASE WHEN i.quantity IS NULL OR ii.id IS NULL THEN 0 ELSE i.quantity END) AS num_sold');
                $qb->addSelect('SUM(i.stock) AS stock_item');
                // $qb->andWhere('i.invoice', 'invo_i', 'with', 'i.id == invo_i.invoice_id');
                $qb->groupBy('p.category_id,p.id');
                // $query = $qb->getQuery();
                // die('DQL ' . $query->getSQL());
        return $this->paginator->paginate($qb->getQuery(), $page, $limit);



        // $qb = $this->getEntityManager()
        //     ->createQueryBuilder()
        //     ->select('p')
        //     ->from(Product::class, 'p');
        //     foreach ($params as $field => $value) {
        //         if ($value === null) {
        //             continue;
        //         }
        //         if ($field == 'terms') {
        //             $terms = $qb->expr()->literal("%$value%");
        //             $qb->andWhere($qb->expr()->orX(
        //                 $qb->expr()->like('p.reference', $terms),
        //                 $qb->expr()->like('p.description', $terms)
        //             ));
        //         }
        //     }

        //         $qb->leftJoin('p.items', 'i');
        //         $qb->innerJoin('p.category_id', 'c');
        //         $qb->leftJoin('i.invoice', 'ii', 'WITH', 'ii.status <> ?1')
        //             ->setParameter(1, InvoiceProvider::DRAFT);
        //         // $qb->leftJoin('i.items', 'invo_i', 'WITH', 'ii.status <> ?1')
        //         $qb->addSelect('SUM(CASE WHEN i.unitary_cost IS NULL OR ii.id IS NULL THEN 0 ELSE i.unitary_cost END * CASE WHEN i.quantity IS NULL THEN 0 ELSE i.quantity END) AS revenue');
        //         $qb->addSelect('c.categoryName');
        //         $qb->addSelect('SUM(CASE WHEN i.quantity IS NULL OR ii.id IS NULL THEN 0 ELSE i.quantity END) AS num_sold');
        //         $qb->addSelect('SUM(i.stock) AS stock_item');
        //         // $qb->andWhere('i.invoice', 'invo_i', 'with', 'i.id == invo_i.invoice_id');
        //         $qb->groupBy('p.category_id,p.id');
        //         // $query = $qb->getQuery();
        //         // die('DQL ' . $query->getSQL());
        // return $this->paginator->paginate($qb->getQuery(), $page, $limit);
    }

    /**
     * There is no easy way to inject things into repositories yet.
     */
    public function setPaginator(PaginatorInterface $paginator)
    {
        $this->paginator = $paginator;
    }
}
