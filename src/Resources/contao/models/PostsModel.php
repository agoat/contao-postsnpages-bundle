<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao;


class PostsModel extends \Model
{
	
	/**
	 * Table name
	 * @var string
	 */
	protected static $strTable = 'tl_posts';


	/**
	 * Find all published articles by their parent ID, column, featured status and category
	 *
	 * @param integer $intPid     The page ID
	 * @param string  $strColumn  The column name
	 * @param array   $arrOptions An optional options array
	 *
	 * @return Model\Collection|ArticleModel[]|ArticleModel|null A collection of models or null if there are no articles in the given column
	 */
	public static function findPublishedByIdOrAlias($varId, array $arrOptions=array())
	{
		$t = static::$strTable;
		
		$arrColumns = !is_numeric($varId) ? array("$t.alias=?") : array("$t.id=?");
		$arrValues = array($varId);
			
		if (isset($arrOptions['ignoreFePreview']) || !BE_USER_LOGGED_IN)
		{
			$time = \Date::floorToMinute();
			$arrColumns[] = "($t.start='' OR $t.start<='$time') AND ($t.stop='' OR $t.stop>'" . ($time + 60) . "') AND $t.published='1'";
		}

		return static::findOneBy($arrColumns, $arrValues, $arrOptions);
	}

	
	/**
	 * Find all published articles by their parent ID, column, featured status and category
	 *
	 * @param integer $intPid     The page ID
	 * @param string  $strColumn  The column name
	 * @param array   $arrOptions An optional options array
	 *
	 * @return Model\Collection|ArticleModel[]|ArticleModel|null A collection of models or null if there are no articles in the given column
	 */
	public static function findPublishedByIds($varIds, array $arrOptions=array())
	{
		$t = static::$strTable;
		
		if (is_array($varIds))
		{
			$arrColumns = array("$t.id in ('" . implode("','", $varIds) . "')");
			$arrValues = array();
		}
		else
		{
			$arrColumns = array("$t.id=?");
			$arrValues = array($varIds);
		}
			
		if (isset($arrOptions['ignoreFePreview']) || !BE_USER_LOGGED_IN)
		{
			$time = \Date::floorToMinute();
			$arrColumns[] = "($t.start='' OR $t.start<='$time') AND ($t.stop='' OR $t.stop>'" . ($time + 60) . "') AND $t.published='1'";
		}

		return static::findBy($arrColumns, $arrValues, $arrOptions);
	}

	
	/**
	 * Find all published articles by their parent ID, column, featured status and category
	 *
	 * @param integer $intPid     The page ID
	 * @param string  $strColumn  The column name
	 * @param array   $arrOptions An optional options array
	 *
	 * @return Model\Collection|ArticleModel[]|ArticleModel|null A collection of models or null if there are no articles in the given column
	 */
	public static function findPublishedByIdsAndFeatured($varIds, $blnFeatured, array $arrOptions=array())
	{
		$t = static::$strTable;
		
		if (is_array($varIds))
		{
			$arrColumns = array("$t.id in ('" . implode("','", $varIds) . "')");
			$arrValues = array();
		}
		else
		{
			$arrColumns = array("$t.id=?");
			$arrValues = array($varIds);
		}
		
		if (null !== $blnFeatured)
		{
			$arrColumns[] = $blnFeatured === true ? "$t.featured='1'" : "$t.featured=''";
		}

		if (isset($arrOptions['ignoreFePreview']) || !BE_USER_LOGGED_IN)
		{
			$time = \Date::floorToMinute();
			$arrColumns[] = "($t.start='' OR $t.start<='$time') AND ($t.stop='' OR $t.stop>'" . ($time + 60) . "') AND $t.published='1'";
		}

		return static::findBy($arrColumns, $arrValues, $arrOptions);
	}

	
	/**
	 * Find all published articles by their parent ID, column, featured status and category
	 *
	 * @param integer $intPid     The page ID
	 * @param string  $strColumn  The column name
	 * @param array   $arrOptions An optional options array
	 *
	 * @return Model\Collection|ArticleModel[]|ArticleModel|null A collection of models or null if there are no articles in the given column
	 */
	public static function findPublishedByPidsAndFeaturedAndCategory($varPids, $blnFeatured, $strCategory, array $arrOptions=array())
	{
		$t = static::$strTable;
		
		if (is_array($varPids))
		{
			$arrColumns = array("$t.pid in ('" . implode("','", $varPids) . "')");
			$arrValues = array();
		}
		else
		{
			$arrColumns = array("$t.pid=?");
			$arrValues = array($varPids);
		}

		if (null !== $blnFeatured)
		{
			$arrColumns[] = $blnFeatured === true ? "$t.featured='1'" : "$t.featured=''";
		}

		if (isset($arrOptions['ignoreFePreview']) || !BE_USER_LOGGED_IN)
		{
			$time = \Date::floorToMinute();
			$arrColumns[] = "($t.start='' OR $t.start<='$time') AND ($t.stop='' OR $t.stop>'" . ($time + 60) . "') AND $t.published='1'";
		}

		if ($strCategory != '')
		{
			$arrColumns[] = "$t.category LIKE ?";
			$arrValues[] = '%' . $strCategory . '%';
		}
		
		return static::findBy($arrColumns, $arrValues, $arrOptions);
	}


}