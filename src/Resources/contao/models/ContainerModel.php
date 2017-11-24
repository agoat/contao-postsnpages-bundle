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
 * Reads and writes page container
 */
class ContainerModel extends \Model 
{

	/**
	 * Table name
	 * @var string
	 */
	protected static $strTable = 'tl_container';

	
	/**
	 * Find a page container by his id or alias and his parent page
	 *
	 * @param mixed   $varId      The numeric ID or alias name
	 * @param integer $intPid     The page ID
	 * @param array   $arrOptions An optional options array
	 *
	 * @return ContainerModel|null The model or null if there is no page container
	 */
	public static function findByIdOrAliasAndPid($varId, $intPid, array $arrOptions=array())
	{
		$t = static::$strTable;
		$arrColumns = !is_numeric($varId) ? array("$t.alias=?") : array("$t.id=?");
		$arrValues = array($varId);

		if ($intPid)
		{
			$arrColumns[] = "$t.pid=?";
			$arrValues[] = $intPid;
		}

		return static::findOneBy($arrColumns, $arrValues, $arrOptions);
	}


	/**
	 * Find a published page container by his ids or alias and his parent page
	 *
	 * @param mixed   $varId      The numeric ID or alias name
	 * @param integer $intPid     The page ID
	 * @param array   $arrOptions An optional options array
	 *
	 * @return ContainerModel|null The model or null if there is no page container
	 */
	public static function findPublishedByIdOrAliasAndPid($varId, $intPid, array $arrOptions=array())
	{
		$t = static::$strTable;
		$arrColumns = !is_numeric($varId) ? array("$t.alias=?") : array("$t.id=?");
		$arrValues = array($varId);

		if ($intPid)
		{
			$arrColumns[] = "$t.pid=?";
			$arrValues[] = $intPid;
		}

		if (isset($arrOptions['ignoreFePreview']) || !BE_USER_LOGGED_IN)
		{
			$time = \Date::floorToMinute();
			$arrColumns[] = "($t.start='' OR $t.start<='$time') AND ($t.stop='' OR $t.stop>'" . ($time + 60) . "') AND $t.published='1'";
		}

		return static::findOneBy($arrColumns, $arrValues, $arrOptions);
	}


	/**
	 * Find a published page container his id
	 *
	 * @param integer $intId      The article ID
	 * @param array   $arrOptions An optional options array
	 *
	 * @return ContainerModel|null The model or null if there is no published page container
	 */
	public static function findPublishedById($intId, array $arrOptions=array())
	{
		$t = static::$strTable;
		$arrColumns = array("$t.id=?");

		if (isset($arrOptions['ignoreFePreview']) || !BE_USER_LOGGED_IN)
		{
			$time = \Date::floorToMinute();
			$arrColumns[] = "($t.start='' OR $t.start<='$time') AND ($t.stop='' OR $t.stop>'" . ($time + 60) . "') AND $t.published='1'";
		}

		return static::findOneBy($arrColumns, $intId, $arrOptions);
	}


	/**
	 * Find all published page container by their parent ids and column
	 *
	 * @param integer $intPid     The page ID
	 * @param string  $strColumn  The column name
	 * @param array   $arrOptions An optional options array
	 *
	 * @return Model\Collection|ContainerModel|null A collection of models or null if there are no page containers in the given column
	 */
	public static function findPublishedByPidAndColumn($intPid, $strColumn, array $arrOptions=array())
	{
		$t = static::$strTable;
		$arrColumns = array("$t.pid=? AND $t.inColumn=?");
		$arrValues = array($intPid, $strColumn);

		if (isset($arrOptions['ignoreFePreview']) || !BE_USER_LOGGED_IN)
		{
			$time = \Date::floorToMinute();
			$arrColumns[] = "($t.start='' OR $t.start<='$time') AND ($t.stop='' OR $t.stop>'" . ($time + 60) . "') AND $t.published='1'";
		}

		if (!isset($arrOptions['order']))
		{
			$arrOptions['order'] = "$t.sorting";
		}

		return static::findBy($arrColumns, $arrValues, $arrOptions);
	}
}
