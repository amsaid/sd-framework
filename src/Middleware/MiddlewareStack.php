<?php

declare(strict_types=1);

namespace SdFramework\Middleware;

use SdFramework\Http\Request;
use SdFramework\Http\Response;

class MiddlewareStack
{
    /** @var MiddlewareInterface[] */
    private array $middleware = [];

    public function add(MiddlewareInterface $middleware): void
    {
        $this->middleware[] = $middleware;
    }

    public function handle(Request $request, callable $handler): Response
    {
        return $this->process($request, $this->middleware, $handler);
    }

    private function process(Request $request, array $middleware, callable $handler): Response
    {
        if (empty($middleware)) {
            return $handler($request);
        }

        $next = array_shift($middleware);
        return $next->process($request, function (Request $request) use ($middleware, $handler) {
            return $this->process($request, $middleware, $handler);
        });
    }
}
