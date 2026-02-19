<?php

declare(strict_types=1);

namespace JR\Tracker\DataObject\Data;

class DataTableQueryParams
{
  public function __construct(
    public readonly int $start,
    public readonly int|null $length,
    public readonly string $orderBy,
    public readonly string $orderDir,
    public readonly string $searchTerm,
    public readonly int $draw
  ) {
  }
}
