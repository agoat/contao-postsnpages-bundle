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


use Agoat\PostsnPagesBundle\Contao\ModulePostContent;
use Agoat\PostsnPagesBundle\Contao\Pages;
use Agoat\PostsnPagesBundle\Contao\Posts;
use Agoat\PostsnPagesBundle\Model\ArchiveModel;
use Agoat\PostsnPagesBundle\Model\ContainerModel;
use Agoat\PostsnPagesBundle\Model\PostModel;
use Contao\ArticleModel;
use Contao\Controller;
use Contao\CoreBundle\Exception\PageNotFoundException;
use Contao\CoreBundle\Exception\RedirectResponseException;
use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\Environment;
use Contao\Input;
use Contao\PageModel;


/**
 * @Hook("getArticles")
 */
class RenderPostsAndPagesListener
{

    public function __construct()
    {
    }


    /**
     * Render Post or Page content elements for a specific column
     *
     * @param  int  $pageId
     * @param  string  $column
     *
     * @return string|null
     */
    public function __invoke(int $pageId, string $column): ?string
    {
        /** @var PageModel $objPage */ global $objPage;

        if ('post' === $objPage->type) {
            return $this->renderPost($pageId, $column);
        } else {
            return $this->renderPage($pageId, $column);
        }
    }


    /**
     * Render post content
     *
     * @param  mixed  $pageId  The page id
     * @param  string  $section  The name of the section
     *
     * @return string The module HTML markup
     */
    protected function renderPost($pageId, $section = 'main')
    {
        /** @var PageModel $objPage */ global $objPage;

        // Set the item from the auto_item parameter
        if (!isset($_GET['posts']) && \Config::get('useAutoItem') && isset($_GET['auto_item'])) {
            Input::setGet('posts', Input::get('auto_item'));
        }

        // Get post id/alias
        $strPost = \Input::get('posts');

        if (!strlen($strPost)) {
            switch ($objPage->emptyPost) {
                case 'nothing':
                    return;

                case 'recent':
                    $objArchives = ArchiveModel::findByPid($objPage->id);

                    if (null === $objArchives) {
                        break;
                    }

                    $objRecent = PostModel::findRecentPublishedByArchives($objArchives->fetchEach('id'));

                    if (null === $objRecent) {
                        break;
                    }

                    throw new RedirectResponseException(Posts::generatePostUrl($objRecent, false, true));

                case 'page':
                    if ($objPage->jumpTo && ($objTarget = $objPage->getRelated('jumpTo')) instanceof PageModel) {
                        /** @var PageModel $objTarget */
                        throw new RedirectResponseException($objTarget->getAbsoluteUrl());
                    }

                case 'notfound':
                default:
            }

            throw new PageNotFoundException('Page not found: ' . Environment::get('uri'));
        }

        // Get published post
        $objPost = PostModel::findPublishedByIdOrAlias($strPost);

        if (null === $objPost) {
            throw new PageNotFoundException('Page not found: ' . \Environment::get('uri'));
        }

        // Check the visibility
        if (!Controller::isVisibleElement($objPost)) {
            return '';
        }

        $objPostContent = new ModulePostContent($objPost, $section);

        $strBuffer = $objPostContent->generate();

        // Disable indexing if protected
        if ($objPostContent->protected && !preg_match('/^\s*<!-- indexer::stop/', $strBuffer)) {
            $strBuffer = "\n<!-- indexer::stop -->" . $strBuffer . "<!-- indexer::continue -->\n";
        }

        return $strBuffer;
    }


    /**
     * Render page content
     *
     * @param  mixed  $pageId  The page id
     * @param  string  $section  The name of the section
     *
     * @return string The module HTML markup
     */
    protected function renderPage($pageId, $section = 'main')
    {
        $objContainer = ContainerModel::findPublishedByPidAndSection($pageId, $section);

        if (null === $objContainer) {
            return '';
        }

        $return = '';
        $intCount = 0;
        $intLast = $objContainer->count() - 1;

        while ($objContainer->next()) {
            /** @var ArticleModel $objRow */
            $objRow = $objContainer->current();

            // Add the "first" and "last" classes
            if ($intCount == 0 || $intCount == $intLast) {
                $arrCss = [];

                if ($intCount == 0) {
                    $arrCss[] = 'first';
                }

                if ($intCount == $intLast) {
                    $arrCss[] = 'last';
                }

                $objRow->classes = $arrCss;
            }

            $return .= Pages::renderContainer($objRow, false, $section);
            ++$intCount;
        }

        return $return;
    }

}
