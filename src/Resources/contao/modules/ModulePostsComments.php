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
 * ModulePostsTeaser class
 */
class ModulePostsComments extends ModulePosts
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'mod_postscomments';


	/**
	 * Do not render the module if a post is called directly
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

		// Add comments (if comments bundle installed)
		$bundles = \System::getContainer()->getParameter('kernel.bundles');

		if (!isset($bundles['ContaoCommentsBundle']))
		{
			return;
		}
		
		// Set the item from the auto_item parameter
		if (!isset($_GET['posts']) && \Config::get('useAutoItem') && isset($_GET['auto_item']))
		{
			\Input::setGet('posts', \Input::get('auto_item'));
		}

		return parent::generate();
	}


	/**
	 * Generate the module
	 */
	protected function compile()
	{
		global $objPage;

		// Get section and article alias
		$strPost = \Input::get('posts');

		if (!strlen($strPost))
		{
			return;
		}
		
		// Get published post
		$objPost = \PostsModel::findPublishedByIdOrAlias($strPost);

		if (null === $objPost)
		{
			return;
		}
	
		$this->Template->addComments = true;
		$this->Template->noComments = ($objPost->noComments) ? true : false;
		
		// Adjust the comments headline level
		$intHl = min(intval(str_replace('h', '', $this->hl)), 5);
		$this->Template->hlc = 'h' . ($intHl + 1);

		$arrNotifies = array();

		// Notify the author
		if (($objAuthor = $objPost->getRelated('author')) instanceof \UserModel && $objAuthor->email != '')
		{
			$arrNotifies[] = $objAuthor->email;
		}

		$this->import('Comments');

		$objConfig = new \stdClass();
		$objConfig->perPage = $this->com_perPage;
		$objConfig->order = $this->com_order;
		$objConfig->template = $this->com_template;
		$objConfig->requireLogin = $this->com_requireLogin;
		$objConfig->disableCaptcha = $this->com_disableCaptcha;
		$objConfig->bbcode = $this->com_bbcode;
		$objConfig->moderate = $this->com_moderate;

		$this->Comments->addCommentsToTemplate($this->Template, $objConfig, 'tl_posts', $objPost->id, $arrNotifies);
	}
}
