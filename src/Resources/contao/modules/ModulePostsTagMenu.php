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

namespace Agoat\PostsnPages;

use Patchwork\Utf8;


/**
 * ModulePostsTagMenu class
 */
class ModulePostsTagMenu extends \Module
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'mod_tags';


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
	
		return parent::generate();
	}


	/**
	 * Generate the module
	 */
	protected function compile()
	{
		/** @var PageModel $objPage */
		global $objPage;

		// Show the posts from particular archive(s)
		if (empty($varPids = \StringUtil::deserialize($this->archive)))
		{
			$objArchives = \ArchiveModel::findByPid($pageId);
			
			if (null === $objArchives)
			{
				return;
			}
			
			$varPids = $objArchives->fetchEach('id');
		}

		$arrOptions = array();
	
		// Handle sorting
		if ($this->sortTags != 'random')
		{
			$arrOptions['order'] = $this->sortTags . ' ' . (($this->sortOrder == 'descending') ? 'DESC' : 'ASC');
		}
		
		// Maximum number of items
		if ($this->numberOfItems > 0)
		{
			$arrOptions['limit'] = intval($this->numberOfItems);
		}
		// Get tags
		$objTags = \TagsModel::findAndCountPublishedByArchive($varPids, $arrOptions);

		if ($objTags === null)
		{
			return;
		}

		// Prepare link
		if (!$this->jumpTo || !($objTarget = $this->objModel->getRelated('jumpTo')) instanceof \PageModel)
		{
			$objTarget = $objPage;
		}
		
		$bundles = \System::getContainer()->getParameter('kernel.bundles');
		$arrTags = array();
		
		while ($objTags->next())
		{
			// Prepare tags array
			$arrTags[] = array
			(
				'label'		=> $objTags->label,
				'count'		=> $objTags->count,
				'href'		=> $objTarget->getFrontendUrl((\Config::get('useAutoItem') || isset($bundles['AgoatPermalinkBundle']) ? '/' : '/tags/') . strtolower($objTags->label))
			);
		}

		if ($this->sortTags == 'random')
		{
			shuffle($arrTags);
		}

		if (!empty($arrTags))
		{
			/** @var FrontendTemplate|object $objTemplate */
			$objTemplate = new \FrontendTemplate($this->tagsTpl);
			
			$objTemplate->pid = $pid;
			$objTemplate->type = get_class($this);
			$objTemplate->cssID = $this->cssID; // see #4897
			$objTemplate->tags = $arrTags;

			$this->Template->tags = $objTemplate->parse();
		}
	}
}
