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

namespace Agoat\PostsnPagesBundle\EventListener;


use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\Database;

/**
 * @Hook("listComments")
 */
class ListCommentsListener
{

    public function __invoke(array $comment): string
    {
        if ($comment['source'] === 'tl_post') {
            $db = Database::getInstance();

            $objParent = $db->prepare("SELECT id, title FROM tl_post WHERE id=?")->execute($comment['parent']);

            if ($objParent->numRows) {
                return ' (<a href="contao/main.php?do=posts&amp;table=tl_content&amp;id=' . $objParent->id . '&amp;rt=' . REQUEST_TOKEN . '">' . $objParent->title . '</a>)';
            }
        }

        return '';
    }

}
