<?php

namespace Agoat\PostsnPagesBundle\Model;


use Contao\Model;
use Contao\Model\Collection;

class StaticModel extends Model
{
    /**
     * Table name
     * @var string
     */
    protected static $strTable = 'tl_static';


    /**
     * Find a static container by his id
     *
     * @param integer $id     The static container id
     * @param array   $arrOptions An optional options array
     *
     * @return Collection|StaticModel|null A collection of models or null if there is no static container
     */
    public static function findOneById(int $id, array $arrOptions = [])
    {
        $table = static::$strTable;

        $arrColumns = array("$table.id=?");
        $arrValues = array($id);

        $arrOptions['order'] = 'sorting';

        return static::findOneBy($arrColumns, $arrValues, $arrOptions);
    }
}
