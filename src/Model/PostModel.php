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
use Contao\Model\Collection;

class PostModel extends Model
{

    /**
     * Table name
     *
     * @var string
     */
    protected static $strTable = 'tl_post';


    /**
     * Find a published post by his id or alias
     *
     * @param  integer|string  $idOrAlias  The post id or alias
     * @param  array  $arrOptions  An optional options array
     *
     * @return Collection|PostModel|null A collection of models or null if there are no posts
     */
    public static function findPublishedByIdOrAlias($idOrAlias, array $arrOptions = [])
    {
        $table = static::$strTable;

        $arrColumns = is_numeric($idOrAlias) ? ["$table.id=?"] : ["$table.alias=?"];
        $arrValues = [$idOrAlias];

        if (isset($arrOptions['ignoreFePreview']) || !BE_USER_LOGGED_IN) {
            $time = Date::floorToMinute();
            $arrColumns[] =
                "($table.start='' OR $table.start<='$time') AND ($table.stop='' OR $table.stop>'" . ($time + 60) . "') AND $table.published='1'";
        }

        return static::findOneBy($arrColumns, $arrValues, $arrOptions);
    }


    /**
     * Find published posts by their ids
     *
     * @param  integer|array  $ids  The post ids
     * @param  array  $arrOptions  An optional options array
     *
     * @return Collection|PostModel|null A collection of models or null if there are no posts
     */
    public static function findPublishedByIds($ids, array $arrOptions = [])
    {
        $table = static::$strTable;

        if (is_array($ids)) {
            $arrColumns = ["$table.id in ('" . implode("','", $ids) . "')"];
            $arrValues = [];
        } else {
            $arrColumns = ["$table.id=?"];
            $arrValues = [$ids];
        }

        if (isset($arrOptions['ignoreFePreview']) || !BE_USER_LOGGED_IN) {
            $time = Date::floorToMinute();
            $arrColumns[] =
                "($table.start='' OR $table.start<='$time') AND ($table.stop='' OR $table.stop>'" . ($time + 60) . "') AND $table.published='1'";
        }

        return static::findBy($arrColumns, $arrValues, $arrOptions);
    }


    /**
     * Find published posts by their pids
     *
     * @param  integer|array  $archiveIds  The post pids (archive ids)
     * @param  array  $arrOptions  An optional options array
     *
     * @return Collection|PostModel|null A collection of models or null if there are no posts
     */
    public static function findRecentPublishedByArchives($archiveIds, array $arrOptions = [])
    {
        $table = static::$strTable;

        if (is_array($archiveIds)) {
            $arrColumns = ["$table.pid in ('" . implode("','", $archiveIds) . "')"];
            $arrValues = [];
        } else {
            $arrColumns = ["$table.pid=?"];
            $arrValues = [$archiveIds];
        }

        if (isset($arrOptions['ignoreFePreview']) || !BE_USER_LOGGED_IN) {
            $time = Date::floorToMinute();
            $arrColumns[] =
                "($table.start='' OR $table.start<='$time') AND ($table.stop='' OR $table.stop>'" . ($time + 60) . "') AND $table.published='1'";
        }

        $arrOptions = array_merge([
            'order' => 'Date DESC',
        ],
            $arrOptions
        );

        return static::findOneBy($arrColumns, $arrValues, $arrOptions);
    }


    /**
     * Find published posts by their ids and featured status
     *
     * @param  integer|array  $ids  The post id(s)
     * @param  boolean  $featured  True for featured posts
     * @param  array  $arrOptions  An optional options array
     *
     * @return Collection|PostModel|null A collection of models or null if there are no articles in the given column
     */
    public static function findPublishedByIdsAndFeatured($ids, bool $featured, array $arrOptions = [])
    {
        $table = static::$strTable;

        if (is_array($ids)) {
            $arrColumns = ["$table.id in ('" . implode("','", $ids) . "')"];
            $arrValues = [];
        } else {
            $arrColumns = ["$table.id=?"];
            $arrValues = [$ids];
        }

        if (null !== $featured) {
            $arrColumns[] = $featured === true ? "$table.featured='1'" : "$table.featured=''";
        }

        if (isset($arrOptions['ignoreFePreview']) || !BE_USER_LOGGED_IN) {
            $time = Date::floorToMinute();
            $arrColumns[] =
                "($table.start='' OR $table.start<='$time') AND ($table.stop='' OR $table.stop>'" . ($time + 60) . "') AND $table.published='1'";
        }

        return static::findBy($arrColumns, $arrValues, $arrOptions);
    }


    /**
     * Find published posts by their ids, featured status and category
     *
     * @param  integer|array  $ids  The post id(s)
     * @param  boolean|null  $featured  True for featured posts
     * @param  string|null  $category  The category
     * @param  array  $arrOptions  An optional options array
     *
     * @return Collection|PostModel|null A collection of models or null if there are no articles in the given column
     */
    public static function findPublishedByIdsAndFeaturedAndCategory(
        $ids,
        ?bool $featured,
        ?string $category,
        array $arrOptions = []
    ) {
        $table = static::$strTable;

        if (is_array($ids)) {
            $arrColumns = ["$table.pid in ('" . implode("','", $ids) . "')"];
            $arrValues = [];
        } else {
            $arrColumns = ["$table.pid=?"];
            $arrValues = [$ids];
        }

        if (null !== $featured) {
            $arrColumns[] = $featured === true ? "$table.featured='1'" : "$table.featured=''";
        }

        if (isset($arrOptions['ignoreFePreview']) || !BE_USER_LOGGED_IN) {
            $time = Date::floorToMinute();
            $arrColumns[] =
                "($table.start='' OR $table.start<='$time') AND ($table.stop='' OR $table.stop>'" . ($time + 60) . "') AND $table.published='1'";
        }

        if ($category != '') {
            $arrColumns[] = "$table.category LIKE ?";
            $arrValues[] = '%' . $category . '%';
        }

        return static::findBy($arrColumns, $arrValues, $arrOptions);
    }

}
