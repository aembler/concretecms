<?php

declare(strict_types=1);

namespace Concrete\Core\Block\Manifest;

use Concrete\Core\Block\Manifest\Error\ManifestError;
use Concrete\Core\Block\Manifest\FormLayout\FormLayoutElementInterface;

final class BlockManifest implements \JsonSerializable
{
    /**
     * @var string
     */
    protected $handle;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var string
     */
    protected $package;

    /**
     * @var string
     */
    protected $schemaVersion;

    /**
     * @var string
     */
    protected $icon;

    /**
     * @var array<string, \Concrete\Core\Block\Manifest\FieldDefinition>
     */
    protected $fields;

    /**
     * @var array<string, \Concrete\Core\Block\Manifest\GroupDefinition>
     */
    protected $groups;

    /**
     * @var list<\Concrete\Core\Block\Manifest\FormLayout\FormLayoutElementInterface>
     */
    protected $layout;

    /**
     * @var list<\Concrete\Core\Block\Manifest\Error\ManifestError>
     */
    protected $errors;

    /**
     * @param array<string, \Concrete\Core\Block\Manifest\FieldDefinition> $fields
     * @param array<string, \Concrete\Core\Block\Manifest\GroupDefinition> $groups
     * @param list<\Concrete\Core\Block\Manifest\FormLayout\FormLayoutElementInterface> $layout
     * @param list<\Concrete\Core\Block\Manifest\Error\ManifestError> $errors
     */
    public function __construct(
        string $handle,
        string $name,
        string $description,
        string $package,
        string $schemaVersion,
        string $icon,
        array $fields,
        array $groups,
        array $layout,
        array $errors = []
    ) {
        $this->handle = $handle;
        $this->name = $name;
        $this->description = $description;
        $this->package = $package;
        $this->schemaVersion = $schemaVersion;
        $this->icon = $icon;
        $this->fields = $fields;
        $this->groups = $groups;
        $this->layout = $layout;
        $this->errors = $errors;
    }

    public function getHandle(): string
    {
        return $this->handle;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getPackage(): string
    {
        return $this->package;
    }

    public function getSchemaVersion(): string
    {
        return $this->schemaVersion;
    }

    public function getIcon(): string
    {
        return $this->icon;
    }

    /**
     * @return array<string, \Concrete\Core\Block\Manifest\FieldDefinition>
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    public function getField(string $fieldId): ?FieldDefinition
    {
        return $this->fields[$fieldId] ?? null;
    }

    /**
     * @return array<string, \Concrete\Core\Block\Manifest\GroupDefinition>
     */
    public function getGroups(): array
    {
        return $this->groups;
    }

    public function getGroup(string $groupId): ?GroupDefinition
    {
        return $this->groups[$groupId] ?? null;
    }

    /**
     * @return list<\Concrete\Core\Block\Manifest\FormLayout\FormLayoutElementInterface>
     */
    public function getLayout(): array
    {
        return $this->layout;
    }

    /**
     * @return list<\Concrete\Core\Block\Manifest\Error\ManifestError>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    public function hasErrors(): bool
    {
        return $this->errors !== [];
    }

    /**
     * @return array{
     *   handle: string,
     *   name: string,
     *   description: string,
     *   package: string,
     *   schemaVersion: string,
     *   icon: string,
     *   fields: array<string, \Concrete\Core\Block\Manifest\FieldDefinition>,
     *   groups: array<string, \Concrete\Core\Block\Manifest\GroupDefinition>,
     *   layout: list<\Concrete\Core\Block\Manifest\FormLayout\FormLayoutElementInterface>,
     *   errors: list<\Concrete\Core\Block\Manifest\Error\ManifestError>
     * }
     */
    public function jsonSerialize(): array
    {
        return [
            'handle' => $this->handle,
            'name' => $this->name,
            'description' => $this->description,
            'package' => $this->package,
            'schemaVersion' => $this->schemaVersion,
            'icon' => $this->icon,
            'fields' => $this->fields,
            'groups' => $this->groups,
            'layout' => $this->layout,
            'errors' => $this->errors,
        ];
    }
}
