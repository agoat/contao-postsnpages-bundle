<?php


namespace Agoat\PostsnPagesBundle\EventListener;

use Agoat\PostsnPagesBundle\Model\ArchiveModel;
use Agoat\PostsnPagesBundle\Model\ContainerModel;
use Agoat\PostsnPagesBundle\Model\PostModel;
use Agoat\PostsnPagesBundle\Model\StaticModel;
use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\PageModel;


/**
 * @Hook("getRootPageId")
 */
class GetRootPageListener
{
    public function __invoke(string $table, int $id): ?int
    {
        if ('tl_post' == $table) {
            $objPost = PostModel::findByPk($id);

            if ($objPost === null) {
                return null;
            }

            $objArchive = ArchiveModel::findByPk($objPost->pid);

            if ($objArchive === null) {
                return null;
            }

            $objPage = \PageModel::findWithDetails($objArchive->pid);

            if ($objPage === null) {
                return null;
            }

            return $objPage->rootId;

        } elseif ('tl_container' == $table) {
            $objContainer = ContainerModel::findByPk($id);

            if ($objContainer === null) {
                return null;
            }

            $objPage = PageModel::findWithDetails($objContainer->pid);

            if ($objPage === null) {
                return null;
            }

            return $objPage->rootId;

        } elseif ('tl_static' == $table) {
            $objStatic = StaticModel::findByPk($id);

            if ($objStatic === null) {
                return null;
            }

            return $objStatic->rootId;
        }
    }
}
