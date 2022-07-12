<?php

declare(strict_types=1);

namespace TwoKings\Hierarchy;

use TwoKings\Hierarchy\MenuParser;
use Bolt\Canonical;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class HierarchicalCanonical extends Canonical
{
    /** @var RequestStack */
    private $requests;

    /** @var MenuParser */
    private $parser;

    /** @var RouteGenerator */
    private $generator;

    /**
     * @required
     */
    public function initialize(RequestStack $requests, MenuParser $parser, RouteGenerator $generator): void
    {
        $this->requests = $requests;
        $this->parser = $parser;
        $this->generator = $generator;
    }

    public function get(?string $route = null, array $params = [], bool $absolute = true): ?string
    {
        // Note: This `_canonical` part may not be used anymore since a piece of code was removed
        //       from `HierarchicalRoutes\Controller`. Since it is minimal code I will leave it
        //       here just in case (or for future features).

        $request = $this->requests->getCurrentRequest();

        if ($request->attributes->has('_canonical')) {
            return $request->attributes->get('_canonical');
        }

        $contentTypeSlug = $request->attributes->get('contentTypeSlug');
        $slugOrId = $request->attributes->get('slugOrId');

        if ($contentTypeSlug && $slugOrId) {
            $node = $this->parser->findByBoltIdentifier("$contentTypeSlug/$slugOrId");
            if ($node) {
                return $this->generator->generateSlug($node, UrlGeneratorInterface::ABSOLUTE_URL);
            }
        }

        return parent::get($route, $params, $absolute);
    }
}
