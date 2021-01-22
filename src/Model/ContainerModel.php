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


use Contao\Date;
use Contao\Model;

class ContainerModel extends Model
{

    /**
     * Table name
     *
     * @var string
     */
    protected static $strTable = 'tl_container';


    /**
     * Find a page container by his id or alias and his parent page
     *
     * @param  mixed  $idOrAlias  The numeric ID or alias name
     * @param  integer  $pageId  The page ID
     * @param  array  $arrOptions  An optional options array
     *
     * @return ContainerModel|null The model or null if there is no page container
     */
    public static function findByIdOrAliasAndPid($idOrAlias, ?int $pageId = null, array $arrOptions = [])
    {
        $table = static::$strTable;

        $arrColumns = is_numeric($idOrAlias) ? ["$table.id=?"] : ["$table.alias=?"];
        $arrValues = [$idOrAlias];

        if ($pageId) {
            $arrColumns[] = "$table.pid=?";
            $arrValues[] = $pageId;
        }

        return static::findOneBy($arrColumns, $arrValues, $arrOptions);
    }


    /**
     * Find a published page container by his ids or alias and his parent page
     *
     * @param  mixed  $idOrAlias  The numeric ID or alias name
     * @param ?integer  $pageId  The page ID
     * @param  array  $arrOptions  An optional options array
     *
     * @return ContainerModel|null The model or null if there is no page container
     */
    public static function findPublishedByIdOrAliasAndPid($idOrAlias, ?int $pageId = null, array $arrOptions = [])
    {
        $table = static::$strTable;

        $arrColumns = is_numeric($idOrAlias) ? ["$table.id=?"] : ["$table.alias=?"];
        $arrValues = [$idOrAlias];

        if ($pageId) {
            $arrColumns[] = "$table.pid=?";
            $arrValues[] = $pageId;
        }

        if (isset($arrOptions['ignoreFePreview']) || !BE_USER_LOGGED_IN) {
            $time = \Date::floorToMinute();
            $arrColumns[] =
                "($table.start='' OR $table.start<='$time') AND ($table.stop='' OR $table.stop>'" . ($time + 60) . "') AND $table.published='1'";
        }

        return static::findOneBy($arrColumns, $arrValues, $arrOptions);
    }


    /**
     * Find a published page container his id
     *
     * @param  integer  $id  The article ID
     * @param  array  $arrOptions  An optional options array
     *
     * @return ContainerModel|null The model or null if there is no published page container
     */
    public static function findPublishedById(int $id, array $arrOptions = [])
    {
        $table = static::$strTable;

        $arrColumns = ["$table.id=?"];

        if (isset($arrOptions['ignoreFePreview']) || !BE_USER_LOGGED_IN) {
            $time = Date::floorToMinute();
            $arrColumns[] =
                "($table.start='' OR $table.start<='$time') AND ($table.stop='' OR $table.stop>'" . ($time + 60) . "') AND $table.published='1'";
        }

        return static::findOneBy($arrColumns, $id, $arrOptions);
    }


    /**
     * Find all published page container by their parent ids and section
     *
     * @param  integer  $pageId  The page ID
     * @param  string  $section  The section name
     * @param  array  $arrOptions  An optional options array
     *
     * @return Model\Collection|ContainerModel|null A collection of models or null if there are no page containers in
     *     the given column
     */
    public static function findPublishedByPidAndSection(int $pageId, string $section, array $arrOptions = [])
    {
        $table = static::$strTable;

        $arrColumns = ["$table.pid=? AND $table.section=?"];
        $arrValues = [$pageId, $section];

        if (isset($arrOptions['ignoreFePreview']) || !BE_USER_LOGGED_IN) {
            $time = Date::floorToMinute();
            $arrColumns[] =
                "($table.start='' OR $table.start<='$time') AND ($table.stop='' OR $table.stop>'" . ($time + 60) . "') AND $table.published='1'";
        }

        if (!isset($arrOptions['order'])) {
            $arrOptions['order'] = "$table.sorting";
        }

        return static::findBy($arrColumns, $arrValues, $arrOptions);
    }

}
