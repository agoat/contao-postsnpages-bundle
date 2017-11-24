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
 * Reads and writes static container and groups
 */
class StaticModel extends \Model 
{

	/**
	 * Table name
	 * @var string
	 */
	protected static $strTable = 'tl_static';

	
	/**
	 * Find a static container by his id
	 *
	 * @param integer $intPid     The static container id
	 * @param array   $arrOptions An optional options array
	 *
	 * @return Model\Collection|StaticModel|null A collection of models or null if there is no static container
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
