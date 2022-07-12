<?php

declare(strict_types=1);

namespace TwoKings\Hierarchy;

use Bolt\Configuration\Config;
use Bolt\Configuration\Content\ContentType;
use Bolt\Entity\Content;
use Bolt\Repository\ContentRepository;
use Bolt\Twig\ContentExtension;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class RouteGenerator
{
    /** @var Config */
    private $config;

    /** @var ContentRepository */
    private $contentRepository;

    /** @var ContentExtension */
    private $contentExtension;

    /** @var UrlGeneratorInterface */
    private $urlGenerator;

    public function __construct(Config $config, ContentRepository $contentRepository, ContentExtension $contentExtension, UrlGeneratorInterface $urlGenerator)
    {
        $this->config = $config;
        $this->contentRepository = $contentRepository;
        $this->contentExtension = $contentExtension;
        $this->urlGenerator = $urlGenerator;
    }

    public function generateSlug(RecordNode $node, $absolute = false): ?string
    {
        $content = $this->getContent($node->getSlug());

        if (! $content instanceof Content) {
            return null;
        }

        // Add a full absolute base-URL if there are no parents anymore.
        if (! $node->hasParent() && $absolute !== false) {
            $baseUrl = $this->urlGenerator->generate('homepage', [], $absolute);
            return $baseUrl . $content->getSlug();
        } else {
            $slug = '/' . $content->getSlug();
        }

        return $node->hasParent() ? $this->generateSlug($node->getParent(), $absolute) . $slug : $slug;
    }

    private function getContent(string $link): ?Content
    {
        // @todo: This does not work for the homepage just yet.
        [$contentTypeSlug, $slug] = @explode('/', $link);

        // First, try to get it if the id is numeric.
        if (is_numeric($slug)) {
            return $this->contentRepository->findOneById((int) $slug);
        }

        /** @var ContentType $contentType */
        $contentType = $this->config->getContentType($contentTypeSlug);

        return $this->contentRepository->findOneBySlug($slug, $contentType);
    }
}
