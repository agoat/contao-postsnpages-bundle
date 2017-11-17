<?php

/*
 * Contao Extended Articles Extension
 *
 * Copyright (c) 2017 Arne Stappen
 *
 * @license LGPL-3.0+
 */
 
namespace Agoat\PostsnPages;

use Contao\CoreBundle\Exception\PageNotFoundException;
use Patchwork\Utf8;


/**
 * Provides methodes to handle direct article rendering
 *
 * @property array  $news_archives
 * @property string $news_jumpToCurrent
 * @property string $news_format
 * @property int    $news_readerModule
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class ModulePostReader extends ModulePosts
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'mod_postreader';


	/**
	 * Do not render directly called posts from 404 error pages
	 *
	 * @return string
	 */
	public function generate($intId=null)
	{
		global $objPage;
		
		// Don't try to render an direct called article for a 404 error page
		if ($objPage->type == 'error_404')
		{
			return;
		}
		
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
			throw new PageNotFoundException('Page not found: ' . \Environment::get('uri'));
		}
	
		// Check protection (TODO)
		
		// Increase the popularity counter (TODO: check the session)
		$objPost->popular = ++$objPost->popular;
		$objPost->save();
		
		// Redirect to link target if setGet
		if ($objPost->alternativeLink)
		{
			if ($objPost->url)
			{
				$this->redirect($this->replaceInsertTags($objPost->url));
			}
		}
		
		// Overwrite the page title (see @contao/core #2853 and #4955)
		if ($strPost != '' && ($strPost == $objPost->id || $strPost == $objPost->alias) && $objPost->title != '')
		{
			$objPage->pageTitle = strip_tags(\StringUtil::stripInsertTags($objPost->title));
			
			if ($objPost->teaser != '')
			{
				$objPage->description = $this->prepareMetaDescription($objPost->teaser);
			}
		}		

		// Set custom post template
		$this->postTemplate = $objPost->customTpl ? $objPost->customTpl : $this->postTpl;

		// Render the post
		$this->Template->post = $this->renderPost($objPost, $objPage, $this->showTeaser, true);

		// Back link
		$this->Template->backlink = 'javascript:history.go(-1)'; // see #6955
		$this->Template->back = \StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['goBack']);

		// Add related
		if ($this->addRelated && $this->relatedModule > 0)
		{
			$this->Template->related = $this->getFrontendModule($this->relatedModule);
		}
	
		// Add comments (if comments bundle installed)
		$bundles = \System::getContainer()->getParameter('kernel.bundles');

		if ($this->addComments && isset($bundles['ContaoCommentsBundle']))
		{
			$this->Template->addComments = true;
			$this->Template->noComments = ($objPost->noComments) ? true : false;
			
			// Adjust the comments headline level
			$intHl = min(intval(str_replace('h', '', $this->hl)), 5);
			$this->Template->hlc = 'h' . ($intHl + 1);

			$arrNotifies = array();

			// Notify the author
			if (($objAuthor = $objPost->getRelated('author')) instanceof UserModel && $objAuthor->email != '')
			{
				$arrNotifies[] = $objAuthor->email;
			}

			// Notify the system administrator
			if ($this->notifyAdmin)
			{
				$arrNotifies[] = $GLOBALS['TL_ADMIN_EMAIL'];
			}


			$this->import('Comments');
			$objConfig = new \stdClass();

			$objConfig->perPage = $this->perPage;
			$objConfig->order = $this->com_order;
			$objConfig->template = $this->com_template;
			$objConfig->requireLogin = $this->com_requireLogin;
			$objConfig->disableCaptcha = $this->com_disableCaptcha;
			$objConfig->bbcode = $this->com_bbcode;
			$objConfig->moderate = $this->com_moderate;

			$this->Comments->addCommentsToTemplate($this->Template, $objConfig, 'tl_posts', $objPost->id, $arrNotifies);
		}
	}
}
