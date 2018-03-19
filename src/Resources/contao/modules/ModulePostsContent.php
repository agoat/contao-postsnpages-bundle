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

use Contao\CoreBundle\Exception\PageNotFoundException;
use Contao\CoreBundle\Exception\RedirectResponseException;
use Patchwork\Utf8;


/**
 * ModulePostReader class
 */
class ModulePostsContent extends ModulePosts
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'mod_post';


	/**
	 * Generate the module
	 */
	protected function compile()
	{
		/** @var PageModel $objPage */
		global $objPage;

		// Check protection (TODO)
		
		// Increase the popularity counter (TODO: check the session)
		$this->objModel->popular = ++$this->objModel->popular;
		$this->objModel->save();
		
		// Redirect to link target if setGet
		if ($this->alternativeLink)
		{
			if ($this->url)
			{
				throw new RedirectResponseException($this->replaceInsertTags(str_replace('}}', '|absolute}}', $this->url), false), 303);
			}
		}

		// Get post alias
		$strPost = \Input::get('posts');
		
		// Overwrite the page title
		if ($strPost != '' && ($strPost == $this->id || $strPost == $this->alias) && $this->title != '')
		{
			$objPage->pageTitle = strip_tags(\StringUtil::stripInsertTags($this->title));
			
			if ($this->teaser != '')
			{
				$objPage->description = $this->prepareMetaDescription($this->teaser);
			}
		}		
	
		// Set custom post template
		$this->postTemplate = $objPage->postTpl;
		
		// Set teaser image size
		$this->imgSize = $objPage->imgSize;

		// Render the post
		$this->Template->post = $this->renderPost($this->objModel, $objPage->showTeaser, true);

		// Back link
		$this->Template->backlink = 'javascript:history.go(-1)'; // see #6955
		$this->Template->back = \StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['goBack']);
	}
}
