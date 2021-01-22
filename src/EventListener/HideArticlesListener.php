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
 * @Hook("initializeSystem")
 */
class HideArticlesListener
{

    /**
     * Hide the Articles Backend module (We do not want to use Articles anymore)
     */
    public function __invoke(): void
    {
        // Remove articles from the backend module array
        unset($GLOBALS['BE_MOD']['content']['article']);

        // Remove article related modules
        unset($GLOBALS['FE_MOD']['navigationMenu']['articlenav']);
        unset($GLOBALS['FE_MOD']['miscellaneous']['articlelist']);

        // Remove article related content elements
        unset($GLOBALS['TL_CTE']['includes']['article']);
        unset($GLOBALS['TL_CTE']['includes']['teaser']);
    }

}
