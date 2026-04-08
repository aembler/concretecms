<?php

declare(strict_types=1);

namespace Concrete\Core\Block\Manifest\Error;

final class ManifestError implements \JsonSerializable
{
    /**
     * @var string
     */
    protected $code;

    /**
     * @var string
     */
    protected $message;

    /**
     * @var array<string, mixed>
     */
    protected $context;

    /**
     * @param array<string, mixed> $context
     */
    public function __construct(string $code, string $message, array $context = [])
    {
        $this->code = $code;
        $this->message = $message;
        $this->context = $context;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return array<string, mixed>
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * @return array{code: string, message: string, context: array<string, mixed>}
     */
    public function jsonSerialize(): array
    {
        return [
            'code' => $this->code,
            'message' => $this->message,
            'context' => $this->context,
        ];
    }
}
