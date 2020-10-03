<?php


namespace Agoat\PostsnPagesBundle\EventListener;

use Contao\CoreBundle\ServiceAnnotation\Hook;

/**
 * @Hook("getPageStatusIcon")
 */
class GetPageStatusIconListener
{
    public function __invoke($page, string $image): string
    {
        return str_replace('post', 'bundles/agoatpostsnpages/post', $image);
    }
}
