<?php

namespace App\Support;

/**
 * Pagination Helper
 * Generates pagination metadata and SQL LIMIT/OFFSET
 */
class Paginator
{
    private int $page;
    private int $perPage;
    private int $total;
    private int $totalPages;

    public function __construct(int $total, int $perPage = 15, int $page = 1)
    {
        $this->total = $total;
        $this->perPage = $perPage;
        $this->page = max(1, $page);
        $this->totalPages = (int)ceil($total / $perPage);
        
        // Ensure page is within bounds
        if ($this->page > $this->totalPages && $this->totalPages > 0) {
            $this->page = $this->totalPages;
        }
    }

    /**
     * Get current page number
     */
    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * Get items per page
     */
    public function getPerPage(): int
    {
        return $this->perPage;
    }

    /**
     * Get total items
     */
    public function getTotal(): int
    {
        return $this->total;
    }

    /**
     * Get total pages
     */
    public function getTotalPages(): int
    {
        return $this->totalPages;
    }

    /**
     * Get LIMIT and OFFSET for SQL query
     */
    public function getLimitOffset(): array
    {
        $offset = ($this->page - 1) * $this->perPage;
        return [
            'limit' => $this->perPage,
            'offset' => $offset,
        ];
    }

    /**
     * Get SQL LIMIT clause
     */
    public function getLimitClause(): string
    {
        $offset = ($this->page - 1) * $this->perPage;
        return "LIMIT {$this->perPage} OFFSET {$offset}";
    }

    /**
     * Check if has previous page
     */
    public function hasPrevious(): bool
    {
        return $this->page > 1;
    }

    /**
     * Check if has next page
     */
    public function hasNext(): bool
    {
        return $this->page < $this->totalPages;
    }

    /**
     * Get previous page number
     */
    public function getPreviousPage(): int
    {
        return max(1, $this->page - 1);
    }

    /**
     * Get next page number
     */
    public function getNextPage(): int
    {
        return min($this->totalPages, $this->page + 1);
    }

    /**
     * Get range of pages for display (e.g., 1 2 3 4 5)
     */
    public function getPageRange(int $range = 5): array
    {
        $start = max(1, $this->page - (int)($range / 2));
        $end = min($this->totalPages, $start + $range - 1);
        $start = max(1, $end - $range + 1);

        return range($start, $end);
    }

    /**
     * Get first item number on current page
     */
    public function getFirstItemNumber(): int
    {
        if ($this->total === 0) {
            return 0;
        }
        return (($this->page - 1) * $this->perPage) + 1;
    }

    /**
     * Get last item number on current page
     */
    public function getLastItemNumber(): int
    {
        if ($this->total === 0) {
            return 0;
        }
        return min($this->page * $this->perPage, $this->total);
    }

    /**
     * Build URL query string (for links)
     */
    public function buildQueryString(string $baseUrl, array $extraParams = []): string
    {
        $params = array_merge(['page' => $this->page], $extraParams);
        return $baseUrl . '?' . http_build_query($params);
    }
}
