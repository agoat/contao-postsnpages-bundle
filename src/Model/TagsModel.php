<?php


namespace Agoat\PostsnPagesBundle\Model;


use Contao\Model;
use Contao\Model\Collection;

class TagsModel extends Model
{
    /**
     * Table name
     * @var string
     */
    protected static $strTable = 'tl_tags';


    /**
     * Find tags by their label(s)
     *
     * @param array $tags    An array with labels
     * @param array $arrOptions An optional options array
     *
     * @return TagsModel|null A model or null if there are no tags
     */
    public static function findByLabels(array $tags, array $arrOptions = [])
    {
        $table = static::$strTable;

        foreach ($tags as $strTag) {
            $arrColumns = array("$table.label=?");
            $arrValues = array($strTag);
        }

        return static::findOneBy($arrColumns, $arrValues, $arrOptions);
    }


    /**
     * Find tags by their archive
     *
     * @param integer $archiveId The archive id
     * @param array   $arrOptions An optional options array
     *
     * @return Collection|TagsModel|null A collection of models or null if there are no tags in the archive
     */
    public static function findByArchive(int $archiveId, array $arrOptions = [])
    {
        $table = static::$strTable;

        $arrColumns = array("$table.archive=?");
        $arrValues = array($archiveId);

        if (!isset($arrOptions['order'])) {
            $arrOptions['order'] = "$table.label";
        }

        return static::findBy($arrColumns, $arrValues, $arrOptions);
    }


    /**
     * Find published tags by their label and archive(s)
     *
     * @param string        $label      The tag label
     * @param integer|array $archiveIds The archive id or an array of archive ids
     * @param array         $arrOptions  An optional options array
     *
     * @return Collection|TagsModel|null A collection of models or null if there are no articles in the archive
     */
    public static function findPublishedByLabelAndArchives(string $label, $archiveIds, array $arrOptions = [])
    {
        $table = static::$strTable;

        if (is_array($archiveIds)) {
            $arrColumns = array("$table.archive in ('" . implode("','", $archiveIds) . "')");

        } else {
            $arrColumns = array("$table.archive=?");
            $arrValues = array($archiveIds);
        }

        $arrColumns[] = "$table.published='1' AND $table.label=?";
        $arrValues[] = $label;

        return static::findBy($arrColumns, $arrValues, $arrOptions);
    }


    /**
     * Find and count tags by their archive
     *
     * @param array $archiveIds An array of archive ids
     * @param array $arrOptions An optional options array
     *
     * @return Collection|TagsModel|null A collection of models or null if there are no tags in archive
     */
    public static function findAndCountPublishedByArchives(array $archiveIds, array $arrOptions = [])
    {
        $table = static::$strTable;

        $strQuery = "SELECT $table.label, COUNT($table.label) as count FROM $table WHERE $table.archive IN(" . implode(",", $archiveIds) . ") AND $table.published='1' GROUP BY $table.label";

        // Having (see #6446)
        if ($arrOptions['having'] !== null) {
            $strQuery .= " HAVING " . $arrOptions['having'];
        }

        // Order by
        if ($arrOptions['order'] !== null) {
            $strQuery .= " ORDER BY " . $arrOptions['order'];
        }

        $objStatement = \Database::getInstance()->prepare($strQuery);

        // Defaults for limit and offset
        if (!isset($arrOptions['limit'])) {
            $arrOptions['limit'] = 0;
        }

        if (!isset($arrOptions['offset'])) {
            $arrOptions['offset'] = 0;
        }

        // Limit
        if ($arrOptions['limit'] > 0 || $arrOptions['offset'] > 0) {
            $objStatement->limit($arrOptions['limit'], $arrOptions['offset']);
        }

        $objStatement = static::preFind($objStatement);
        $objResult = $objStatement->execute();

        if ($objResult->numRows < 1) {
            return null;
        }

        $objResult = static::postFind($objResult);

        $arrModels = array();

        $strClass = \Model::getClassFromTable(static::$strTable);

        while ($objResult->next()) {
            $objModel = new $strClass();
            $objModel->preventSaving();
            $objModel->setRow($objResult->row());

            $arrModels[] = $objModel;
        }

        return new \Model\Collection($arrModels, static::$strTable);
    }
}
