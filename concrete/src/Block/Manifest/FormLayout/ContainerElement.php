<?php

declare(strict_types=1);

namespace Concrete\Core\Block\Manifest\FormLayout;

abstract class ContainerElement implements FormLayoutElementInterface
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var list<\Concrete\Core\Block\Manifest\FormLayout\FormLayoutElementInterface>
     */
    protected $children;

    /**
     * @param list<\Concrete\Core\Block\Manifest\FormLayout\FormLayoutElementInterface> $children
     */
    public function __construct(string $id, string $name, array $children = [])
    {
        $this->id = $id;
        $this->name = $name;
        $this->children = $children;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return list<\Concrete\Core\Block\Manifest\FormLayout\FormLayoutElementInterface>
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    /**
     * @return array{type: string, id: string, name: string, children: list<\Concrete\Core\Block\Manifest\FormLayout\FormLayoutElementInterface>}
     */
    public function jsonSerialize(): array
    {
        return [
            'type' => $this->getType(),
            'id' => $this->id,
            'name' => $this->name,
            'children' => $this->children,
        ];
    }
}
