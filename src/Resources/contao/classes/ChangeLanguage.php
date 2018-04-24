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

use Terminal42\ChangeLanguage\PageFinder;


/**
 * Methods to supoport the changelanguage extension for posts
 */
class ChangeLanguage
{

	/**
	 * Add changelanguage settings to the tl_post table
	 *
	 * @param string $table
	 */
	public static function addPostsLanguage($table)
	{
		if ('tl_post' === $table)
		{
			$GLOBALS['TL_DCA']['tl_posts']['config']['sql']['keys']['languageMain'] = 'index';

			$GLOBALS['TL_DCA']['tl_posts']['config']['onload_callback'][] = array('Agoat\\PostsnPagesBundle\\Contao\\ChangeLanguage', 'showLanguageMain'); 
			
			$GLOBALS['TL_DCA']['tl_posts']['fields']['languageMain'] = array
			(
				'label'                   => &$GLOBALS['TL_LANG']['tl_posts']['languageMain'],
				'exclude'                 => true,
				'inputType'               => 'postTree',
				'eval'                    => array('fieldType'=>'radio', 'multiple'=>false, 'rootNodes'=>[0], 'doNotCopy'=>true, 'tl_class'=>'w50 clr'),
				'sql'                     => "int(10) unsigned NOT NULL default '0'",
				'load_callback'           => [['Agoat\\PostsnPagesBundle\\Contao\\ChangeLanguage', 'onLoadLanguageMain']],
				'save_callback'           => [['Agoat\\PostsnPagesBundle\\Contao\\ChangeLanguage', 'onSaveLanguageMain']],
			);
			
		}
	}	
	
	
	/**
	 * Show the languageMain field depending on the current posts root node
	 *
	 * @param ChangelanguageNavigationEvent $event
	 */
	function showLanguageMain($dc)
	{
		$post = \PostModel::findById($dc->id);
		
		if (null === $post) {
            return;
        }
		
		$archive = \ArchiveModel::findById($post->pid);
		$page = \PageModel::findWithDetails($archive->pid);

		if (!$page->rootIsFallback) {
			$GLOBALS['TL_DCA']['tl_posts']['palettes']['default'] = preg_replace(
				'/({title_legend}.*?);/',
				'$0{language_legend},languageMain;',
				$GLOBALS['TL_DCA']['tl_posts']['palettes']['default']);
		}
	}
	
	
    /**
     * Sets rootNodes when initializing the languageMain field.
     *
     * @param mixed         $value
     * @param DataContainer $dc
     *
     * @return mixed
     */
	public function onLoadLanguageMain($value, $dc)
	{
		if (!$dc->id || 'posts' !== \Input::get('do')) {
            return $value;
        }

		$post = \PostModel::findById($dc->id);
		
		if (null === $post) {
            return $value;
        }
		
		$archive = \ArchiveModel::findById($post->pid);
		$page = \PageModel::findById($archive->pid);

		$pageFinder = new PageFinder();
        $associated = $pageFinder->findAssociatedInMaster($page);
	
		$GLOBALS['TL_DCA']['tl_posts']['fields']['languageMain']['eval']['rootNodes'] = [$associated->id];
		
		return $value;
	}
	
	
    /**
     * Validate input value when saving tl_page.languageMain field.
     *
     * @param mixed         $value
     * @param DataContainer $dc
     *
     * @throws \RuntimeException
     *
     * @return mixed
     */
	public function onSaveLanguageMain($value, $dc)
	{
		if ($value > 0) {
			$post = \PostModel::findById($dc->id);
			
			if (null === $post) {
				return $value;
			}
			
			$archive = \ArchiveModel::findById($post->pid);
			$archives = \ArchiveModel::findByPid($archive->pid)->fetchEach('id');

			$duplicates = \PostModel::countBy(
				['tl_posts.pid IN (' . implode(',', $archives) . ')', 'tl_posts.languageMain=?','tl_posts.id!=?'],
				[$value, $dc->id]
			);
	
			if ($duplicates > 0) {
                throw new \RuntimeException($GLOBALS['TL_LANG']['MSC']['duplicateMainLanguage']);
            }
		}
		return $value;
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
	
		$currentPost = \PostModel::findByIdOrAlias($event->getUrlParameterBag()->getUrlAttribute('posts'));
		
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
     * @return PostModel|null
     */
    private function findPublishedPost(array $columns, array $values = [], array $options = [])
    {
        if (true !== BE_USER_LOGGED_IN) {
            $time = \Date::floorToMinute();
            $columns[] = "(tl_posts.start='' OR tl_posts.start<='$time')";
            $columns[] = "(tl_posts.stop='' OR tl_posts.stop>'".($time + 60)."')";
            $columns[] = "tl_posts.published='1'";
        }
        return \PostModel::findOneBy($columns, $values, $options);
    }


}
