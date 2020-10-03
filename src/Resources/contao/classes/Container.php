<?php


namespace Agoat\PostsnPagesBundle\Contao;

use Contao\ContainerModel;
use Contao\Controller;
use Contao\Frontend;

class Container extends Frontend
{
    /**
     * Generate the content of a container and return it as html
     *
     * @param mixed   $container         The ModelContainer object
     * @param boolean $isInsertTag If true, there will be no page relation
     * @param string  $column     The name of the section
     *
     * @return string The container HTML markup or false
     */
    public static function renderContainer(ContainerModel $container, $isInsertTag = false, $column='main')
    {
        // Check the visibility
        if (! Controller::isVisibleElement($container))
        {
            return '';
        }

        $container->headline = $container->title;

        // HOOK: add custom logic
        if (isset($GLOBALS['TL_HOOKS']['getArticle']) && is_array($GLOBALS['TL_HOOKS']['getArticle']))
        {
            foreach ($GLOBALS['TL_HOOKS']['getArticle'] as $callback)
            {
                static::importStatic($callback[0])->{$callback[1]}($container);
            }
        }

        $objContainer = new ModuleContainer($container, $column);
        $strBuffer = $objContainer->generate($isInsertTag);

        // Disable indexing if protected
        if ($objContainer->protected && !preg_match('/^\s*<!-- indexer::stop/', $strBuffer))
        {
            $strBuffer = "\n<!-- indexer::stop -->". $strBuffer ."<!-- indexer::continue -->\n";
        }

        return $strBuffer;
    }
}
