<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Agoat\ContentManager;


/**
 * Provides methodes to handle articles.
 *
 * @property integer $tstamp
 * @property string  $title
 * @property string  $alias
 * @property string  $inColumn
 * @property boolean $showTeaser
 * @property boolean $multiMode
 * @property string  $teaser
 * @property string  $teaserCssID
 * @property string  $classes
 * @property string  $keywords
 * @property boolean $printable
 * @property boolean $published
 * @property integer $start
 * @property integer $stop
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class ModuleContainer extends \Module
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'mod_article';

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
		if (TL_MODE == 'FE' && !BE_USER_LOGGED_IN && (!$this->published || ($this->start != '' && $this->start > time()) || ($this->stop != '' && $this->stop < time())))
		{
			return '';
		}

		$this->type = 'section';
		$this->blnNoMarkup = $blnNoMarkup;

		return parent::generate();
	}


	/**
	 * Generate the module
	 */
	protected function compile()
	{
		/** @var PageModel $objPage */
		global $objPage;

		$id = 'section-' . $this->id;

		// Generate the CSS ID if it is not set
		if (empty($this->cssID[0]))
		{
			$this->cssID = array($id, $this->cssID[1]);
		}

		$this->Template->column = $this->inColumn;
		$this->Template->noMarkup = $this->blnNoMarkup;

		// Add the modification date
		$this->Template->timestamp = $this->tstamp;
		$this->Template->date = \Date::parse($objPage->datimFormat, $this->tstamp);

		$arrElements = array();
		$objCte = \ContentModel::findPublishedByPidAndTable($this->id, 'tl_container');

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
				$arrElements[] = $this->getContentElement($objRow, $this->strColumn);
				++$intCount;
			}
		}

		$this->Template->elements = $arrElements;

		if ($this->keywords != '')
		{
			$GLOBALS['TL_KEYWORDS'] .= (($GLOBALS['TL_KEYWORDS'] != '') ? ', ' : '') . $this->keywords;
		}

		// HOOK: add custom logic
		if (isset($GLOBALS['TL_HOOKS']['compileArticle']) && is_array($GLOBALS['TL_HOOKS']['compileArticle']))
		{
			foreach ($GLOBALS['TL_HOOKS']['compileArticle'] as $callback)
			{
				$this->import($callback[0]);
				$this->{$callback[0]}->{$callback[1]}($this->Template, $this->arrData, $this);
			}
		}
	}
}
