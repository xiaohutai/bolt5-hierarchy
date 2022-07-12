<?php

declare(strict_types=1);

namespace TwoKings\Hierarchy;

use Bolt\Menu\FrontendMenuBuilderInterface;
use Twig\Environment;

class HierarchicalMenuBuilder
{
    /** @var FrontendMenuBuilderInterface */
    private $frontendMenuBuilder;

    public function __construct(
        FrontendMenuBuilderInterface $frontendMenuBuilder,
        RouteGenerator $generator,
        MenuParser $parser)
    {
        $this->frontendMenuBuilder = $frontendMenuBuilder;
        $this->parser = $parser;
        $this->generator = $generator;
    }

    public function buildMenu(Environment $twig, ?string $name): array
    {
        $menu = $this->frontendMenuBuilder->buildMenu($twig, $name);

        $nodes = $this->parser->getNodes();

        foreach ($menu as $key => $item) {
            // todo: refactor it before making a public extension
            $this->parseItem($item, $nodes[$key]);
            $menu[$key] = $item;
        }

        return $menu;
    }

    private function parseItem(array &$item, RecordNode $node): void
    {
        $item['uri'] = $this->generator->generateSlug($node);

        if (\array_key_exists('submenu', $item) && ! empty($item['submenu'])) {
            foreach ($item['submenu'] as $key => $submenuItem) {
                // todo: refactor before making it a public extension
                $this->parseItem($submenuItem, $node->getChildren()[$key]);
                $item['submenu'][$key] = $submenuItem;
            }
        }
    }
}
