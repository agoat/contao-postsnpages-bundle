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
 * ModuleContainer class
 */
class ModuleContainerList extends \Module
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'mod_containerlist';

	/**
	 * No markup
	 * @var boolean
	 */
	protected $blnNoMarkup = false;


	/**
	 * Check whether the article is published
	 *
	 * @param boolean $blnNoMarkup
	 *
	 * @return string
	 */
	public function generate($blnNoMarkup=false)
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

		$strBuffer = parent::generate();
		
		return !empty($this->Template->containers) ? $strBuffer : '';
	}


	/**
	 * Generate the module
	 */
	protected function compile()
	{
		/** @var PageModel $objPage */
		global $objPage;

		if (!strlen($this->section))
		{
			$this->section = 'main';
		}

		$intCount = 0;
		$containers = array();
		$id = $objPage->id;
		$objTarget = null;

		$this->Template->request = \Environment::get('request');

		// Show the container of a different page
		if ($this->defineRoot && $this->rootPage > 0)
		{
			if (($objTarget = $this->objModel->getRelated('rootPage')) instanceof \PageModel)
			{
				$id = $objTarget->id;

				/** @var PageModel $objTarget */
				$this->Template->request = $objTarget->getFrontendUrl();
			}
		}

		// Get published container
		$objContainer = \ContainerModel::findPublishedByPidAndSection($id, $this->section);

		if ($objContainer === null)
		{
			return;
		}

		$objHelper = $objTarget ?: $objPage;

		while ($objContainer->next())
		{
			// Skip first article
			if (++$intCount <= intval($this->skipFirst))
			{
				continue;
			}

			$cssID = \StringUtil::deserialize($objContainer->cssID, true);

			$containers[] = array
			(
				'link' => $objContainer->title,
				'title' => \StringUtil::specialchars($objContainer->title),
				'id' => $cssID[0] ?: 'container-' . $objContainer->id,
				'containerId' => $objContainer->id
			);
		}

		$this->Template->containers = $containers;
	}
}
