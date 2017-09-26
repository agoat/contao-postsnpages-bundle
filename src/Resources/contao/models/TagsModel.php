<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao;


class TagsModel extends \Model 
{

	/**
	 * Table name
	 * @var string
	 */
	protected static $strTable = 'tl_tags';

	
	/**
	 * Find all published articles by their parent ID, column and featured status
	 *
	 * @param integer $intPid     The page ID
	 * @param string  $strColumn  The column name
	 * @param array   $arrOptions An optional options array
	 *
	 * @return Model\Collection|ArticleModel[]|ArticleModel|null A collection of models or null if there are no articles in the given column
	 */
	public static function findByLabels($arrTags, array $arrOptions=array())
	{
		$t = static::$strTable;
		
		foreach ($arrTags as $strTag)
		{
			$arrColumns = array("$t.label=?");
			$arrValues = array($strTag);
		}
		
		return static::findOneBy($arrColumns, $arrValues, $arrOptions);
	}


	/**
	 * Find all published articles by their parent ID, column and featured status
	 *
	 * @param integer $intPid     The page ID
	 * @param string  $strColumn  The column name
	 * @param array   $arrOptions An optional options array
	 *
	 * @return Model\Collection|ArticleModel[]|ArticleModel|null A collection of models or null if there are no articles in the given column
	 */
	public static function findByArchive($intArchive, array $arrOptions=array())
	{
		$t = static::$strTable;
		
		$arrColumns = array("$t.archive=?");
		$arrValues = array($intArchive);
		
		if (!isset($arrOptions['order']))
		{
			$arrOptions['order'] = "$t.label";
		}
		
		return static::findBy($arrColumns, $arrValues, $arrOptions);
	}


	/**
	 * Find all published articles by their parent ID, column and featured status
	 *
	 * @param integer $intPid     The page ID
	 * @param string  $strColumn  The column name
	 * @param array   $arrOptions An optional options array
	 *
	 * @return Model\Collection|ArticleModel[]|ArticleModel|null A collection of models or null if there are no articles in the given column
	 */
	public static function findPublishedByLabelAndArchives($strTag, $varArchives, array $arrOptions=array())
	{
		$t = static::$strTable;
		
		if (is_array($varArchives))
		{
			$arrColumns = array("$t.archive in ('" . implode("','", $varArchives) . "')");
		}
		else
		{
			$arrColumns = array("$t.archive=?");
			$arrValues = array($varArchives);
		}

		$arrColumns[] = "$t.published='1' AND $t.label=?";
		$arrValues[] = $strTag;
		
		return static::findBy($arrColumns, $arrValues, $arrOptions);
	}


	/**
	 * Find all published tags by their posts archive
	 *
	 * @param integer $intArchive The archive ID
	 * @param array   $arrOptions An optional options array
	 *
	 * @return Model\Collection|ArticleModel[]|ArticleModel|null A collection of models or null if there are no tags in the given column
	 */
	public static function findAndCountPublishedByArchive($intArchive, array $arrOptions=array())
	{
		$t = static::$strTable;
			
		$strQuery = "SELECT $t.label, COUNT($t.label) as count FROM $t WHERE $t.archive=? AND $t.published='1' GROUP BY $t.label";
		
		// Having (see #6446)
		if ($arrOptions['having'] !== null)
		{
			$strQuery .= " HAVING " . $arrOptions['having'];
		}

		// Order by
		if ($arrOptions['order'] !== null)
		{
			$strQuery .= " ORDER BY " . $arrOptions['order'];
		}

		$objStatement = \Database::getInstance()->prepare($strQuery);
		
		// Defaults for limit and offset
		if (!isset($arrOptions['limit']))
		{
			$arrOptions['limit'] = 0;
		}
		
		if (!isset($arrOptions['offset']))
		{
			$arrOptions['offset'] = 0;
		}
		
		// Limit
		if ($arrOptions['limit'] > 0 || $arrOptions['offset'] > 0)
		{
			$objStatement->limit($arrOptions['limit'], $arrOptions['offset']);
		}
	
		$objStatement = static::preFind($objStatement);
		$objResult = $objStatement->execute($intArchive);
	
		if ($objResult->numRows < 1)
		{
			return null;
		}
		
		$objResult = static::postFind($objResult);		
	
		$arrModels = array();
	
		$strClass = \Model::getClassFromTable(static::$strTable);
	
		while ($objResult->next())
		{
			$objModel = new $strClass();
			$objModel->preventSaving();
			$objModel->setRow($objResult->row());
			
			$arrModels[] = $objModel;
		}
		
		return new \Model\Collection($arrModels, static::$strTable);
	}
}