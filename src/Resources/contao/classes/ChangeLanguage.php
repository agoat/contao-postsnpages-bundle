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

namespace Agoat\PostsnPagesBundle\Contao;


/**
 * Methods to supoport the changelanguage extension for posts
 */
class ChangeLanguage
{

	/**
	 * Add changelanguage settings to the tl_posts table
	 *
	 * @param string $table
	 */
	public static function addPostsLanguage($table)
	{
		if ('tl_posts' === $table)
		{
			$GLOBALS['TL_DCA']['tl_posts']['config']['sql']['keys']['languageMain'] = 'index';

			$pattern = '/({title_legend}.*?);/';
			$replace = '$0{language_legend},languageMain;';
			
			$GLOBALS['TL_DCA']['tl_posts']['palettes']['default'] = preg_replace($pattern, $replace, $GLOBALS['TL_DCA']['tl_posts']['palettes']['default']);
				
			$GLOBALS['TL_DCA']['tl_posts']['fields']['languageMain'] = array
			(
				'label'                   => &$GLOBALS['TL_LANG']['tl_posts']['languageMain'],
				'exclude'                 => true,
				'inputType'               => 'postTree',
				'eval'                    => array('fieldType'=>'radio', 'multiple'=>false, 'rootNodes'=>[0], 'tl_class'=>'w50 clr'),
				'sql'                     => "int(10) unsigned NOT NULL default '0'",
				//'load_callback'           => [['Terminal42\ChangeLanguage\EventListener\DataContainer\PageFieldsListener', 'onLoadLanguageMain']],
				//'save_callback'           => [['Terminal42\ChangeLanguage\EventListener\DataContainer\PageFieldsListener', 'onSaveLanguageMain']],
			);
			
		}
	}	
	
	
	/**
	 * Translate URL parameters for posts
	 *
	 * @param ChangelanguageNavigationEvent $event
	 */
	function getPostsNavigation($event)
	{
		$navigationItem = $event->getNavigationItem();

        if ($navigationItem->isCurrentPage() ||
			!$event->getUrlParameterBag()->hasUrlAttribute('posts'))
		{
            return;
        }
	
		$currentPost = \PostsModel::findById($event->getUrlParameterBag()->getUrlAttribute('posts'));
		
		$archives = \ArchiveModel::findBy(
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
     * @return \PostsModel|null
     */
    private function findPublishedPost(array $columns, array $values = [], array $options = [])
    {
        if (true !== BE_USER_LOGGED_IN) {
            $time = \Date::floorToMinute();
            $columns[] = "(tl_posts.start='' OR tl_posts.start<='$time')";
            $columns[] = "(tl_posts.stop='' OR tl_posts.stop>'".($time + 60)."')";
            $columns[] = "tl_posts.published='1'";
        }
        return \PostsModel::findOneBy($columns, $values, $options);
    }


}
