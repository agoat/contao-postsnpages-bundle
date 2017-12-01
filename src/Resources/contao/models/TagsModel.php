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
 * Reads and writes tags
 */
class TagsModel extends \Model 
{

	/**
	 * Table name
	 * @var string
	 */
	protected static $strTable = 'tl_tags';

	
	/**
	 * Find tags by their label(s)
	 *
	 * @param array $arrTags    An array with labels
	 * @param array $arrOptions An optional options array
	 *
	 * @return Model|TagsModel|null A model or null if there are no tags
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
	 * Find tags by their archive
	 *
	 * @param integer $intArchive The archive id
	 * @param array   $arrOptions An optional options array
	 *
	 * @return Model\Collection|TagsModel|null A collection of models or null if there are no tags in the archive
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
	 * Find published tags by their label and archive(s)
	 *
	 * @param integer       $strTag      The tag label
	 * @param integer|array $varArchives The archive id or an array of archive ids
	 * @param array         $arrOptions  An optional options array
	 *
	 * @return Model\Collection|TagsModel|null A collection of models or null if there are no articles in the archive
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
	 * Find and count tags by their archive
	 *
	 * @param array $varArchive An array of archive ids
	 * @param array $arrOptions An optional options array
	 *
	 * @return Model\Collection|TagsModel|null A collection of models or null if there are no tags in archive
	 */
	public static function findAndCountPublishedByArchives(array $arrArchives, array $arrOptions=array())
	{
		$t = static::$strTable;
			
		$strQuery = "SELECT $t.label, COUNT($t.label) as count FROM $t WHERE $t.archive IN(" . implode(",", $arrArchives) . ") AND $t.published='1' GROUP BY $t.label";
		
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
		$objResult = $objStatement->execute();
	
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
