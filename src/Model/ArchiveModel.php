<?php

namespace Agoat\PostsnPagesBundle\Model;


use Contao\Model;
use Contao\Model\Collection;

class ArchiveModel extends Model
{
    /**
     * Table name
     * @var string
     */
    protected static $strTable = 'tl_archive';


    /**
     * Find all published archives by their id(s)
     *
     * @param integer|array $ids     The archive id(s)
     * @param array   $arrOptions An optional options array
     *
     * @return Collection|ArchiveModel|null A collection of models or null if there are no archives
     */
    public static function findByIds($ids, array $arrOptions = [])
    {
        $table = static::$strTable;

        if (is_array($ids)) {
            $arrColumns = array("$table.id in ('" . implode("','", $ids) . "')");
            $arrValues = array();

        } else {
            $arrColumns = array("$table.id=?");
            $arrValues = array($ids);
        }

        return static::findBy($arrColumns, $arrValues, $arrOptions);
    }

   /**
     * Find all published archives by their id(s)
     *
     * @param integer $pid        The page id
     * @param array   $arrOptions An optional options array
     *
     * @return Collection|ArchiveModel|null A collection of models or null if there are no archives
     */
    public static function findByPid(int $pid, array $arrOptions = [])
    {
        $table = static::$strTable;

        $arrColumns = array("$table.pid=?");
        $arrValues = array($pid);

        return static::findBy($arrColumns, $arrValues, $arrOptions);
    }
}
