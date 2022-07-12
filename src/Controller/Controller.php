<?php

declare(strict_types=1);

namespace TwoKings\Hierarchy\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use TwoKings\Hierarchy\RecordNode;

class Controller extends AbstractController
{
   /**
    * @Route("/{slug}", requirements={"slug"=".+"}, name="hierarchical_route")
    * @ParamConverter(name="hierarchical_route")
    */
    public function handleRoute(Request $request, RecordNode $node): Response
    {
        // $this->requestStack->getCurrentRequest()->get('_route_params')
        //dd($request , $request->get('_route_params'));

        [$contentTypeSlug, $slugOrId] = explode('/', $node->getSlug());

        $routeParams = [
            'contentTypeSlug' => $contentTypeSlug,
            'slugOrId'        => $slugOrId,
        ];

        // Attempt to mimick a normal request. Some caveats:
        // - See `Bolt\Twig\ContentExtension` where the `pager()` function requires
        //   both `_route_params` and `_route` to be set.
        // - Query-params are removed by forwarding, so add these manually as the last
        //   parameter.
        $response = $this->forward('Bolt\Controller\Frontend\DetailController::record', [
            'slugOrId'         => $slugOrId,
            'contentTypeSlug'  => $contentTypeSlug,
            'requirePublished' => false,
            '_locale'          => $request->getLocale(),
            '_route_params'    => $routeParams,
            '_route'           => 'record',
            // '_firewall_context' => 'security.firewall.map.context.main',
            // '_security_firewall_run' => '_security_main',
        ], $request->query->all());

        return $response;

        // --- [NOTE] ---
        // Xiao: Here used to be code that I do not have an idea what it was supposed to do.
        //       It tried to have the kernel handle the request, but that would hide the
        //       Symfony developer tool from showing up. Plus I could not get it to work.
        //
        //       Forwarding seems to be a nice and elegant solution. However, a side-effect
        //       is that we get the following part twice.
        //
        //           <link rel="canonical" href="{{ canonical }}">
        //           <meta name="generator" content="Bolt">
        //
        //       Not sure how to intercept/prevent this at the moment.
    }
}
