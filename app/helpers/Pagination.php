<?php
/**
 * app/helpers/Pagination.php — Pagination Calculator
 * Owner: All members (common)
 *
 * Usage:
 *   $pg    = new Pagination($total, (int)($_GET["page"] ?? 1));
 *   $items = $model->getAll($pg->limit(), $pg->offset());
 *   // then include partials/pagination.php in the view
 */
class Pagination {
    public int $total, $perPage, $current, $pages;
    public function __construct(int $total, int $page = 1, int $pp = PER_PAGE) {
        $this->total   = $total;
        $this->perPage = $pp;
        $this->pages   = max(1, (int) ceil($total / $pp));
        $this->current = max(1, min($page, $this->pages));
    }
    public function offset(): int  { return ($this->current - 1) * $this->perPage; }
    public function limit():  int  { return $this->perPage; }
    public function hasPrev(): bool { return $this->current > 1; }
    public function hasNext(): bool { return $this->current < $this->pages; }
}
