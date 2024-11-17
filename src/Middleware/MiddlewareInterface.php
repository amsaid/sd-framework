<?php

declare(strict_types=1);

namespace SdFramework\Middleware;

use SdFramework\Http\Request;
use SdFramework\Http\Response;

interface MiddlewareInterface
{
    public function process(Request $request, callable $next): Response;
}
