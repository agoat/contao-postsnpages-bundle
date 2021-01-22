<?php
/*
 * Posts'n'pages extension for Contao Open Source CMS.
 *
 * @copyright  Arne Stappen (alias aGoat) 2021
 * @package    contao-postsnpages
 * @author     Arne Stappen <mehh@agoat.xyz>
 * @link       https://agoat.xyz
 * @license    LGPL-3.0
 */

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
