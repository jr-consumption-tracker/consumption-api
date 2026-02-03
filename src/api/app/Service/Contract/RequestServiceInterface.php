<?php

declare(strict_types=1);

namespace JR\Tracker\Service\Contract;

use Psr\Http\Message\ServerRequestInterface;
use JR\Tracker\DataObject\Data\DataTableQueryParams;

interface RequestServiceInterface
{


    public function getReferer(ServerRequestInterface $request): string;
    public function isXhr(ServerRequestInterface $request): bool;
    public function getDataTableQueryParameters(ServerRequestInterface $request): DataTableQueryParams;
    public function getClientIp(ServerRequestInterface $request, array $trustedProxies): ?string;
}