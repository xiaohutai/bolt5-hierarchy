<?php

declare(strict_types=1);

namespace TwoKings\Hierarchy\Twig;

use Bolt\Entity\Content;
use Bolt\Storage\Query;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use TwoKings\Hierarchy\HierarchicalMenuBuilder;
use TwoKings\Hierarchy\MenuParser;
use TwoKings\Hierarchy\RecordNode;
use TwoKings\Hierarchy\RouteGenerator;

class TwigExtension extends AbstractExtension
{
    /** @var HierarchicalMenuBuilder */
    private $builder;

    /** @var MenuParser */
    private $parser;

    /** @var Environment */
    private $twig;

    /** @var Query */
    private $query;

    /** @var RouteGenerator */
    private $generator;

    public function __construct(HierarchicalMenuBuilder $builder, MenuParser $parser, Query $query, Environment $twig, RouteGenerator $generator)
    {
        $this->builder = $builder;
        $this->parser = $parser;
        $this->query = $query;
        $this->twig = $twig;

        $this->generator = $generator;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('hMenu', [$this, 'getMenu']),

            // NOTE: Unprefixed helper functions based on the Bolt 3 extension.
            new TwigFunction('getChildren' , [$this, 'getChildren']),
            new TwigFunction('getSiblings' , [$this, 'getSiblings']),
            new TwigFunction('getParent'   , [$this, 'getParent']),
            new TwigFunction('getParents'  , [$this, 'getParents']),
        ];
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('hLink' , [$this, 'getLink']),
        ];
    }

    public function getLink($record)
    {
        $contentTypeSlug = $record->getDefinition()['singular_slug'];
        $id = $record->getId();
        $boltId = $contentTypeSlug . '/' . $id;
        $node = $this->parser->findByBoltIdentifier($boltId);

        if (!empty($node)) {
            $result = $this->generator->generateSlug($node, UrlGeneratorInterface::ABSOLUTE_PATH);

            if (!empty($result)) {
                return $result;
            }
        }

        return '/' . $boltId;
    }

    public function getMenu(string $name = 'main'): array
    {
        return $this->builder->buildMenu($this->twig, $name);
    }

    public function getChildren($record): array
    {
        $children = $this->getNode($record)->getChildren();

        return $this->convertNodesToRecords($children);
    }

    public function getSiblings($record): array
    {
        $siblings = $this->getNode($record)->getParent()->getChildren();

        return $this->convertNodesToRecords($siblings);
    }

    public function getParent($record): ?Content
    {
        $record = $this->getNode($record)->getParent();

        return $this->convertNodeToRecord($record);
    }

    public function getParents($record): array
    {
        $parents = [];

        $current = $this->getNode($record);
        while ($current && $current->hasParent()) {
            $parent = $current->getParent();
            $parents[] = $parent;
            $current = $parent;
        }

        return $this->convertNodesToRecords($parents);
    }

    private function getNode($record): ?RecordNode
    {
        $slug = $record->getDefinition()['singular_slug'] . '/' . $record->getId();
        $node = $this->parser->findByBoltIdentifier($slug);

        return $node;
    }

    private function convertNodesToRecords(array $nodes): array
    {
        $records = array_map([$this, 'convertNodeToRecord'], $nodes);
        $records = array_filter($records);

        return $records;
    }

    private function convertNodeToRecord(?RecordNode $node): ?Content
    {
        if ($node) {
            return $this->query->getContentForTwig($node->getSlug());
        }

        return null;
    }
}
