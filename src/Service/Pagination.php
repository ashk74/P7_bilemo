<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Manage pagination system
 */
class Pagination
{
    private int $limit;
    private string $dql;
    private int $lastPage;
    private array $paramsToBind = [];
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function paginate(string $dql, int $page, int $limit, ?array $paramsToBind)
    {
        $this->setDql($dql);
        $this->setParamsToBind($paramsToBind);

        $items = $this->initPagination($page, $limit);
        $result = [
            'items' => $items->getIterator(),
            'meta' => [
                'limit' => $this->getLimit(),
                'current_page' => $page,
                'total_pages' => $this->getLastPage(),
                'total_items' => $items->count()
            ]
        ];

        if ($page > $this->getLastPage()) {
            throw new NotFoundHttpException(sprintf('Page parameter can\'t be superior to the last page : %s', $this->getLastPage()));
        }

        return $result;
    }

    private function initPagination(int $page, int $limit): Paginator
    {
        $this->setLimit($limit);

        // Use DQL for data queries
        $query = $this->entityManager->createQuery($this->dql)
            ->setFirstResult($this->limit * ($page - 1))
            ->setMaxResults($this->limit);

        // If DQL contain params to bind add $query->setParameter
        if ($this->paramsToBind != null) {
            foreach ($this->paramsToBind as $key => $value) {
                $query->setParameter($key, $value);
            }
        }

        // Initialize Doctrine Paginator
        $paginator = new Paginator($query);

        // Set the last page
        $this->setLastPage($paginator->count(), $this->limit);

        return $paginator;
    }

    public function getLimit()
    {
        return $this->limit;
    }

    private function setLimit(int $limit)
    {
        $this->limit = $limit;
    }

    public function getLastPage()
    {
        return $this->lastPage;
    }

    private function setLastPage(int $totalItems)
    {
        $this->lastPage = ceil($totalItems / $this->limit);
    }

    private function setDql(string $dql): void
    {
        $this->dql = $dql;
    }

    private function setParamsToBind(array $paramsToBind): void
    {
        $this->paramsToBind = $paramsToBind;
    }
}
