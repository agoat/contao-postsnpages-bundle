<?php

/*
 * Contao Extended Articles Extension
 *
 * Copyright (c) 2017 Arne Stappen
 *
 * @license LGPL-3.0+
 */
 
namespace Agoat\PostsnPages;

use Patchwork\Utf8;


/**
 * Provides methodes to handle article teaser rendering
 *
 * @property array  $news_archives
 * @property string $news_jumpToCurrent
 * @property string $news_format
 * @property int    $news_readerModule
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class ModulePostsContent extends ModulePosts
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'mod_postscontent';


	/**
	 * Do not render the module if an article is called directly
	 *
	 * @return string
	 */
	public function generate()
	{
		if (TL_MODE == 'BE')
		{
			/** @var BackendTemplate|object $objTemplate */
			$objTemplate = new \BackendTemplate('be_wildcard');

			$objTemplate->wildcard = '### ' . Utf8::strtoupper($GLOBALS['TL_LANG']['FMD']['articleteaser'][0]) . ' ###';
			$objTemplate->title = $this->headline;
			$objTemplate->id = $this->id;
			$objTemplate->link = $this->name;
			$objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

			return $objTemplate->parse();
		}

		// Don't render articles if an article is called directly
		if (isset($_GET['posts']) || (\Config::get('useAutoItem') && isset($_GET['auto_item'])))
		{
			return;
		}
		
		return parent::generate();
	}


	/**
	 * Generate the module
	 */
	protected function compile()
	{
		/** @var PageModel $objPage */
		global $objPage;

		$pageId = $objPage->id;
		$pageObj = $objPage;

		// Show the posts of a different page
		if ($this->defineRoot && $this->rootPage > 0)
		{
			if (($objTarget = $this->objModel->getRelated('rootPage')) instanceof PageModel)
			{
				$pageId = $objTarget->id;
				$pageObj = $this->objModel->getRelated('rootPage');

				/** @var PageModel $objTarget */
				$this->Template->request = $objTarget->getFrontendUrl();
			}
		}

		// Set custom post template
		$this->postTemplate = $this->postTpl;
	
		// Get published posts
		$objPosts = $this->getPosts($pageId);

		if ($objPosts === null)
		{
			return;
		}

		$arrPosts = array();
	
		if ($objPosts !== null)
		{
			while ($objPosts->next())
			{
				// Render the post content
				$arrPosts[] = $this->renderPost($objPosts->current(), $pageObj, $this->showTeaser, true);
			}
		}

		if ($this->sortPosts == 'random')
		{
			shuffle($arrPosts);
		}

		$this->Template->posts = $arrPosts;
	}
}