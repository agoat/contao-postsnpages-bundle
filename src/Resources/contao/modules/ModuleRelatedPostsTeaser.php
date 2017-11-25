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

use Patchwork\Utf8;


/**
 * ModuleRelatedPostsTeaser class
 */
class ModuleRelatedPostsTeaser extends ModulePosts
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'mod_poststeaser';


	/**
	 * Display a wildcard in the back end
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
		if (!isset($_GET['posts']) && \Config::get('useAutoItem') && isset($_GET['auto_item']))
		{
			\Input::setGet('posts', \Input::get('auto_item'));
		}

		// Overwrite the item id
		if (null !== $intId)
		{
			\Input::setGet('posts', $intId);
		}
		
		return parent::generate();
	}


	/**
	 * Generate the module
	 */
	protected function compile()
	{
		// Get section and article alias
		$strPost = \Input::get('posts');

		if (!strlen($strPost))
		{
			return;
		}

		$objPosts = $this->getRelatedPosts($strPost);
		
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

		if ($this->sortRelated == 'random')
		{
			shuffle($arrPosts);
		}

		$this->Template->posts = $arrPosts;
	}
}
