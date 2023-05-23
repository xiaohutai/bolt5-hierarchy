<?php

declare(strict_types=1);

namespace TwoKings\Hierarchy;

use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class ParamConverter implements ParamConverterInterface
{
    private MenuParser $parser;

    private Stopwatch $stopwatch;

    private TagAwareCacheInterface $cache;

    public function __construct(MenuParser $parser, Stopwatch $stopwatch, TagAwareCacheInterface $cache)
    {
        $this->parser = $parser;
        $this->stopwatch = $stopwatch;
        $this->cache = $cache;
    }

    public function apply(Request $request, \Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter $configuration)
    {
        $this->stopwatch->start('hier.ParamConv');

        $node = $this->getNode($request);

        $this->stopwatch->stop('hier.ParamConv');
    }

    private function getNode(Request $request)
    {
        $slug = $request->attributes->get('slug', null);

        if ($slug === null) {
            throw new NotFoundHttpException();
        }

        $key = 'hier.ParamConv_' . md5($slug);

        $node = $this->cache->get($key, function (ItemInterface $item) use ($slug) {
            return $this->parser->findBySlug('/' . $slug);
        });

        if ($node === null) {
            throw new NotFoundHttpException();
        }

        $request->attributes->set('node', $node);

        return $node;
    }

    public function supports(\Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter $configuration): bool
    {
        return $configuration->getName() === 'hierarchical_route';
    }
}
