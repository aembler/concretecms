<?php

declare(strict_types=1);

namespace Concrete\Core\Block\Manifest\Field;

use Concrete\Core\Block\Manifest\FieldDefinition;
use Concrete\Core\Application\UserInterface\Component\ComponentInterface;
use Concrete\Core\Http\Request;

interface FieldInterface extends ComponentInterface
{
    public function getHandle(): string;

    /**
     * @param array<string, mixed> $definition
     *
     * @return array<string, mixed>
     */
    public function normalizeDefinition(array $definition): array;

    /**
     * @return mixed
     */
    public function extractValueFromRequest(array $requestArgs, FieldDefinition $field, ?Request $request = null);

    /**
     * @param array<string, mixed> $payload
     *
     * @return mixed
     */
    public function extractValueFromStorage(array $payload, FieldDefinition $field);

    /**
     * @param mixed $submittedValue
     *
     * @return mixed
     */
    public function serializeValue($submittedValue, array $definition, ?Request $request = null);

    /**
     * @param mixed $storedValue
     *
     * @return mixed
     */
    public function createViewValue($storedValue, FieldDefinition $field);
}
