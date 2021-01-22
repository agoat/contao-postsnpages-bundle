<?php
/*
 * Posts'n'pages extension for Contao Open Source CMS.
 *
 * @copyright  Arne Stappen (alias aGoat) 2021
 * @package    contao-postsnpages
 * @author     Arne Stappen <mehh@agoat.xyz>
 * @link       https://agoat.xyz
 * @license    LGPL-3.0
 */

namespace Agoat\PostsnPagesBundle\Model;


use Contao\Model;
use Contao\Model\Collection;

class StaticModel extends Model
{

    /**
     * Table name
     *
     * @var string
     */
    protected static $strTable = 'tl_static';


    /**
     * Find a static container by his id
     *
     * @param  integer  $id  The static container id
     * @param  array  $arrOptions  An optional options array
     *
     * @return Collection|StaticModel|null A collection of models or null if there is no static container
     */
    public static function findOneById(int $id, array $arrOptions = [])
    {
        $table = static::$strTable;

        $arrColumns = ["$table.id=?"];
        $arrValues = [$id];

        $arrOptions['order'] = 'sorting';

        return static::findOneBy($arrColumns, $arrValues, $arrOptions);
    }

}
