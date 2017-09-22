<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao;


class ArchiveModel extends \Model 
{

	/**
	 * Table name
	 * @var string
	 */
	protected static $strTable = 'tl_archive';

	
	/**
	 * Find all published articles by their parent ID, column, featured status and category
	 *
	 * @param integer $intPid     The page ID
	 * @param string  $strColumn  The column name
	 * @param array   $arrOptions An optional options array
	 *
	 * @return Model\Collection|ArticleModel[]|ArticleModel|null A collection of models or null if there are no articles in the given column
	 */
	public static function findByIds($varIds, array $arrOptions=array())
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

		return static::findBy($arrColumns, $arrValues, $arrOptions);
	}

	
}