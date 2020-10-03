<?php
/*
 * Posts'n'pages extension for Contao Open Source CMS.
 *
 * @copyright  Arne Stappen (alias aGoat) 2017
 * @package    contao-postsnpages
 * @author     Arne Stappen <mehh@agoat.xyz>
 * @link       https://agoat.xyz
 * @license    LGPL-3.0
 */

namespace Agoat\PostsnPagesBundle\EventListener;

use Agoat\PostsnPagesBundle\Model\ArchiveModel;
use Agoat\PostsnPagesBundle\Model\PostModel;
use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\Date;
use Terminal42\ChangeLanguage\PageFinder;


/**
 * @Hook("changelanguageNavigation")
 */
class ChangeLanguageNavigationListener
{
    /**
     * Translate URL parameters for posts
     *
     * @param ChangelanguageNavigationEvent $event
     */
    public function __invoke($event): void
    {
        $navigationItem = $event->getNavigationItem();

        if ($navigationItem->isCurrentPage() ||
            !$event->getUrlParameterBag()->hasUrlAttribute('posts'))
        {
            return;
        }

        $currentPost = PostModel::findByIdOrAlias($event->getUrlParameterBag()->getUrlAttribute('posts'));

        $archives = ArchiveModel::findBy(
            ['tl_archive.pid=?'],
            [$navigationItem->getTargetPage()->id]
        );

        if (null === $archives)
        {
            $navigationItem->setIsDirectFallback(false);
            $event->getUrlParameterBag()->setUrlAttribute('posts', $navigationItem->getTargetPage()->alias);
            return;
        }

        if ($currentPost->languageMain)
        {
            $languagePost = $this->findPublishedPost(
                [
                    'tl_posts.pid IN (' . implode(',', $archives->fetchEach('id')) . ')',
                    '(tl_posts.id=? OR tl_posts.languageMain=?)'
                ],
                [
                    $currentPost->languageMain,
                    $currentPost->languageMain
                ]
            );
        }

        else
        {
            $languagePost = $this->findPublishedPost(
                [
                    'tl_posts.pid IN (' . implode(',', $archives->fetchEach('id')) . ')',
                    'tl_posts.languageMain=?'
                ],
                [
                    $currentPost->id
                ]
            );
        }

        if (null === $languagePost)
        {
            $navigationItem->setIsDirectFallback(false);
            $event->getUrlParameterBag()->setUrlAttribute('posts', $navigationItem->getTargetPage()->alias);
            return;
        }

        $event->getUrlParameterBag()->setUrlAttribute('posts', $languagePost->alias);
    }


    /**
     * Find a published article with additional conditions.
     *
     * @param array $columns
     * @param array $values
     * @param array $options
     *
     * @return PostModel|null
     */
    private function findPublishedPost(array $columns, array $values = [], array $options = [])
    {
        if (true !== BE_USER_LOGGED_IN) {
            $time = Date::floorToMinute();
            $columns[] = "(tl_posts.start='' OR tl_posts.start<='$time')";
            $columns[] = "(tl_posts.stop='' OR tl_posts.stop>'".($time + 60)."')";
            $columns[] = "tl_posts.published='1'";
        }
        return PostModel::findOneBy($columns, $values, $options);
    }
}
