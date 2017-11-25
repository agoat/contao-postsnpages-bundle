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
 * ModuleStatic class
 */
class ModuleStatic extends \Module
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'mod_static';

	/**
	 * No markup
	 * @var boolean
	 */
	protected $blnNoMarkup = false;


	/**
	 * Check whether the static container is published
	 *
	 * @param boolean $blnNoMarkup
	 *
	 * @return string
	 */
	public function generate($blnNoMarkup=false)
	{
		$this->type = 'static';
		$this->blnNoMarkup = $blnNoMarkup;

		$objStatic = \StaticModel::findById($this->staticContent);
		
		if (null === $objStatic)
		{
			return '';
		}
	
		// Check the visibility
		if (!static::isVisibleElement($objStatic))
		{
			return '';
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
		
		$id = 'static-' . $this->staticContent;

		// Generate the CSS ID if it is not set
		if (empty($this->cssID[0]))
		{
			$this->cssID = array($id, $this->cssID[1]);
		}

		$this->Template->noMarkup = ($this->noMarkup || $this->blnNoMarkup);

		// Add the modification date
		$this->Template->timestamp = $this->tstamp;
		$this->Template->date = \Date::parse($objPage->datimFormat, $this->tstamp);

		$arrElements = array();
		$objCte = \ContentModel::findPublishedByPidAndTable($this->staticContent, 'tl_static');

		if ($objCte !== null)
		{
			$intCount = 0;
			$intLast = $objCte->count() - 1;

			while ($objCte->next())
			{
				$arrCss = array();

				/** @var ContentModel $objRow */
				$objRow = $objCte->current();

				// Add the "first" and "last" classes (see #2583)
				if ($intCount == 0 || $intCount == $intLast)
				{
					if ($intCount == 0)
					{
						$arrCss[] = 'first';
					}

					if ($intCount == $intLast)
					{
						$arrCss[] = 'last';
					}
				}

				$objRow->classes = $arrCss;
				$arrElements[] = $this->getContentElement($objRow);
				++$intCount;
			}
		}

		$this->Template->elements = $arrElements;
	}
}
