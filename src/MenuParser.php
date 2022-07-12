<?php

declare(strict_types=1);

namespace TwoKings\Hierarchy;

use Bolt\Menu\FrontendMenuBuilderInterface;
use Tightenco\Collect\Support\Collection;
use Twig\Environment;

class MenuParser
{
    /** @var FrontendMenuBuilderInterface */
    private $menu;

    /** @var Environment */
    private $twig;

    /** @var RouteGenerator */
    private $generator;

    /** @var RecordNode[]|null */
    private $nodes;

    public function __construct(FrontendMenuBuilderInterface $menu, Environment $twig, RouteGenerator $generator)
    {
        $this->menu = $menu;
        $this->twig = $twig;
        $this->generator = $generator;
        $this->nodes = null;
    }

    public function parse(): Collection
    {
        // todo: Make this work for multiple menus, not just the default menu
        $menu = $this->menu->buildMenu($this->twig, null);

        $nodes = collect([]);

        foreach ($menu as $item) {
            $node = $this->parseItem($item);
            $nodes[] = $node;
        }

        return $nodes;
    }

    public function getFlatNodes(): Collection
    {
        $nodes = $this->getNodes();

        $result = collect([]);
        foreach ($nodes as $node) {
            $result->add($this->flattenNode($node));
        }

        return $result;
    }

    private function flattenNode(RecordNode $node): collection
    {
        $result = collect([]);

        if (is_iterable($node->getChildren())) {
            foreach ($node->getChildren() as $child) {
                $result->add($this->flattenNode($child)->toArray());
            }
        }

        $result->add($node);

        return $result;
    }

    public function parseItem(array $item): RecordNode
    {
        $node = new RecordNode($item['link']);

        if (\array_key_exists('submenu', $item) && ! empty($item['submenu'])) {
            foreach ($item['submenu'] as $submenuItem) {
                $itemNode = $this->parseItem($submenuItem);
                $node->addChild($itemNode);
            }
        }

        // No more submenus.
        return $node;
    }

    public function getNodes(): Collection
    {
        if (! $this->nodes) {
            $this->nodes = $this->parse();
        }

        return $this->nodes;
    }

    public function findBySlug(string $slug): ?RecordNode
    {
        // todo: let's refactor how this flattened array is generated
        $nodes = $this->getFlatNodes()->flatten();

        /** @var RecordNode $node */
        foreach ($nodes as $node) {
            if ($this->generator->generateSlug($node) === $slug) {
                return $node;
            }
        }

        return null;
    }

    // TODO: same as above but using `page/123` instead.
    public function findByBoltIdentifier(string $slug): ?RecordNode
    {
        // todo: let's refactor how this flattened array is generated
        $nodes = $this->getFlatNodes()->flatten();

        /** @var RecordNode $node */
        foreach ($nodes as $node) {
            if ($node->getSlug() == $slug) {
                return $node;
            }
        }

        return null;
    }
}
