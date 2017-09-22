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
class ModuleTaggedTeaser extends ModulePosts
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'mod_poststeaser';


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

		// Set the item from the auto_item parameter
		if (!isset($_GET['tags']) && \Config::get('useAutoItem') && isset($_GET['auto_item']))
		{
			\Input::setGet('tags', \Input::get('auto_item'));
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

		// Get section and article alias
		$strTag = \Input::get('tags');

		if (!strlen($strTag))
		{
			\Input::setUnusedGet('tags', $strTag);
			return;
		}

		// Get published posts
		$objPosts = $this->getTaggedPosts($strTag);

		if ($objPosts === null)
		{
			\Input::setUnusedGet('tags', $strTag);
			return;
		}
		
		// Set custom post template
		$this->postTemplate = $this->teaserTpl;
		
		$arrPosts = array();
		
		if ($objPosts !== null)
		{
			while ($objPosts->next())
			{
				// Render the teasers
				$arrPosts[] = $this->renderPost($objPosts->current(), true, false);
			}
		}

		if ($this->sortPosts == 'random')
		{
			shuffle($arrPosts);
		}

		$this->Template->posts = $arrPosts;
	}
}