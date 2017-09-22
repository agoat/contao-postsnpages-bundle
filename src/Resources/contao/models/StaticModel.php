<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao;


class StaticModel extends \Model 
{

	/**
	 * Table name
	 * @var string
	 */
	protected static $strTable = 'tl_static';

	
	/**
	 * Find all published articles by their parent ID, column and featured status
	 *
	 * @param integer $intPid     The page ID
	 * @param string  $strColumn  The column name
	 * @param array   $arrOptions An optional options array
	 *
	 * @return Model\Collection|ArticleModel[]|ArticleModel|null A collection of models or null if there are no articles in the given column
	 */
	public static function findFirstByPid($intPid, array $arrOptions=array())
	{
		$t = static::$strTable;
		
		$arrColumns = array("$t.pid=?");
		$arrValues = array($intPid);
		
		$arrOptions['order'] = 'sorting';
		
		return static::findOneBy($arrColumns, $arrValues, $arrOptions);
	}
	
}