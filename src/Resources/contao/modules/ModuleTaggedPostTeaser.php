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

use Contao\BackendTemplate;
use Contao\Config;
use Contao\Input;
use Contao\PageModel;
use Contao\System;
use Patchwork\Utf8;


/**
 * ModuleTaggedPostsTeaser class
 */
class ModuleTaggedPostTeaser extends ModulePost
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'mod_postteaser';


	/**
	 * Display a wildcard in the back end
	 *
	 * @return string
	 */
	public function generate()
	{
		if (TL_MODE == 'BE')
		{
			/** @var BackendTemplate $objTemplate */
			$objTemplate = new BackendTemplate('be_wildcard');

			$objTemplate->wildcard = '### ' . Utf8::strtoupper($GLOBALS['TL_LANG']['FMD']['articleteaser'][0]) . ' ###';
			$objTemplate->title = $this->headline;
			$objTemplate->id = $this->id;
			$objTemplate->link = $this->name;
			$objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

			return $objTemplate->parse();
		}

		// Set the item from the auto_item parameter
		if (!isset($_GET['tags']) && Config::get('useAutoItem') && isset($_GET['auto_item']))
		{
			Input::setGet('tags', Input::get('auto_item'));
		}

		// Set the tags input from the permalink bundle fragment handling
		$bundles = System::getContainer()->getParameter('kernel.bundles');

		if (isset($bundles['AgoatPermalinkBundle']) && isset($_GET[0]))
		{
			Input::setGet('tags', Input::get(0));
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
		$strTag = Input::get('tags');

		if (!strlen($strTag))
		{
			Input::setUnusedGet('tags', $strTag);
			return;
		}

		// Get published posts
		$objPosts = $this->getTaggedPosts($strTag);

		if ($objPosts === null)
		{
			Input::setUnusedGet('tags', $strTag);
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
