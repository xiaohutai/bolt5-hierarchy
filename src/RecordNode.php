<?php

declare(strict_types=1);

namespace TwoKings\Hierarchy;

class RecordNode
{
    /** @var RecordNode */
    private $parent;

    /** @var RecordNode[] */
    private $children;

    private $slug;

    public function __construct(string $slug)
    {
        $this->slug = $slug;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    /**
     * @return RecordNode
     */
    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function hasParent(): bool
    {
        return $this->parent instanceof self;
    }

    /**
     * @param RecordNode $parent
     */
    public function setParent(self $parent): void
    {
        $this->parent = $parent;

        if (! \in_array($this, $parent->getChildren(), true)) {
            $this->parent->addChild($this);
        }
    }

    /**
     * @return RecordNode[]
     */
    public function getChildren(): ?array
    {
        return $this->children;
    }

    /**
     * @param RecordNode[] $children
     */
    public function setChildren(array $children): void
    {
        $this->children = $children;
    }

    public function addChild(self $child): void
    {
        $this->children[] = $child;

        if ($child->getParent() !== $this) {
            $child->setParent($this);
        }
    }

    public function getParents()
    {
        return true;
    }

    public function getSiblings()
    {
        if ($this->hasParent()) {
            return $this->getParent()->getChildren();
        }

        return [];
    }

}
