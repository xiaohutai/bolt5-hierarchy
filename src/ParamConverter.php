<?php

declare(strict_types=1);

namespace TwoKings\Hierarchy;

use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ParamConverter implements ParamConverterInterface
{
    /** @var MenuParser */
    private $parser;

    public function __construct(MenuParser $parser)
    {
        $this->parser = $parser;
    }

    public function apply(Request $request, \Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter $configuration)
    {
        $slug = $request->attributes->get('slug', null);

        if ($slug === null) {
            throw new NotFoundHttpException();
        }

        $node = $this->parser->findBySlug('/' . $slug);

        if ($node === null) {
            throw new NotFoundHttpException();
        }

        $request->attributes->set('node', $node);
    }

    public function supports(\Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter $configuration): bool
    {
        return $configuration->getName() === 'hierarchical_route';
    }
}
