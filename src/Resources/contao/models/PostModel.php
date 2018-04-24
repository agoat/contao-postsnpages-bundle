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

namespace Contao;


/**
 * Reads and writes posts
 */
class PostModel extends \Model
{
	
	/**
	 * Table name
	 * @var string
	 */
	protected static $strTable = 'tl_post';


	/**
	 * Find a published post by his id or alias
	 *
	 * @param integer $varId     The post id or alias
	 * @param array   $arrOptions An optional options array
	 *
	 * @return Model\Collection|PostModel|null A collection of models or null if there are no posts
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
	 * Find published posts by their ids
	 *
	 * @param integer $varIds     The post ids
	 * @param array   $arrOptions An optional options array
	 *
	 * @return Model\Collection|PostsModel|null A collection of models or null if there are no posts
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
	 * Find published posts by their pids
	 *
	 * @param integer $varPids    The post pids (archive ids)
	 * @param array   $arrOptions An optional options array
	 *
	 * @return Model\Collection|PostsModel|null A collection of models or null if there are no posts
	 */
	public static function findRecentPublishedByArchive($varArchives, array $arrOptions=array())
	{
		$t = static::$strTable;
		
		if (is_array($varArchives))
		{
			$arrColumns = array("$t.pid in ('" . implode("','", $varArchives) . "')");
			$arrValues = array();
		}
		else
		{
			$arrColumns = array("$t.pid=?");
			$arrValues = array($varArchives);
		}
			
		if (isset($arrOptions['ignoreFePreview']) || !BE_USER_LOGGED_IN)
		{
			$time = \Date::floorToMinute();
			$arrColumns[] = "($t.start='' OR $t.start<='$time') AND ($t.stop='' OR $t.stop>'" . ($time + 60) . "') AND $t.published='1'";
		}

		$arrOptions = array_merge
		(
			array
			(
				'order' => 'Date DESC'
			),
			$arrOptions
		);
		
		return static::findOneBy($arrColumns, $arrValues, $arrOptions);
	}

	
	/**
	 * Find published posts by their ids and featured status
	 *
	 * @param integer $intPid      The post id(s)
	 * @param boolean $blnFeatured True for featured posts
	 * @param array   $arrOptions  An optional options array
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
	 * Find published posts by their ids, featured status and category
	 *
	 * @param integer $intPid      The post id(s)
	 * @param boolean $blnFeatured True for featured posts
	 * @param string  $strCategory The category
	 * @param array   $arrOptions  An optional options array
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
