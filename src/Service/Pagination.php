<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Manage pagination system
 */
class Pagination
{
    private int $currentPage;
    private int $limit;
    private string $dql;
    private int $lastPage;
    private array $paramsToBind = [];
    private EntityManagerInterface $entityManager;
    private RequestStack $requestStack;

    public function __construct(EntityManagerInterface $entityManager, RequestStack $requestStack)
    {
        $this->entityManager = $entityManager;
        $this->requestStack = $requestStack;
        $this->setCurrentPage();
        $this->setLimit();
    }

    public function paginate(string $dql, array $paramsToBind = null)
    {
        $this->setDql($dql);
        $this->setParamsToBind($paramsToBind);

        $items = $this->initPagination();
        $result = [
            'items' => $items->getIterator(),
            'meta' => [
                'limit' => $this->limit,
                'current_page' => $this->currentPage,
                'total_pages' => $this->lastPage,
                'total_items' => $items->count()
            ]
        ];

        if ($this->currentPage > $this->getLastPage()) {
            throw new NotFoundHttpException(sprintf('Page parameter can\'t be superior to the last page : %s', $this->getLastPage()));
        }

        return $result;
    }

    private function initPagination(): Paginator
    {
        // Use DQL for data queries
        $query = $this->entityManager->createQuery($this->dql)
            ->setFirstResult($this->limit * ($this->currentPage - 1))
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

    private function setLimit()
    {
        $this->limit = $this->requestStack->getMainRequest()->get('limit', 10);
    }

    public function getCurrentPage()
    {
        return $this->currentPage;
    }

    private function setCurrentPage()
    {
        $this->currentPage = $this->requestStack->getMainRequest()->get('page', 1);
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

    private function setParamsToBind(?array $paramsToBind): void
    {
        if ($paramsToBind != null) {
            $this->paramsToBind = $paramsToBind;
        }
    }
}
