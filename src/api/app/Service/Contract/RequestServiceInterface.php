<?php

declare(strict_types=1);

namespace JR\Tracker\Service\Contract;

use JR\Tracker\DataObject\Data\DataTableQueryParams;
use Psr\Http\Message\ServerRequestInterface;

interface RequestServiceInterface
{
  public function getReferer(ServerRequestInterface $request): string;

  public function isXhr(ServerRequestInterface $request): bool;

  public function getDataTableQueryParameters(ServerRequestInterface $request): DataTableQueryParams;

  public function getClientIp(ServerRequestInterface $request, array $trustedProxies): ?string;
}
