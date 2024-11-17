<?php

declare(strict_types=1);

namespace SdFramework\Error;

class AuthorizationException extends HttpException
{
    protected ?string $action;
    protected ?string $resource;

    public function __construct(
        string $message = 'This action is unauthorized.',
        ?string $action = null,
        ?string $resource = null,
        array $headers = []
    ) {
        parent::__construct($message, 403, $headers);
        $this->action = $action;
        $this->resource = $resource;
    }

    public function getAction(): ?string
    {
        return $this->action;
    }

    public function getResource(): ?string
    {
        return $this->resource;
    }
}
