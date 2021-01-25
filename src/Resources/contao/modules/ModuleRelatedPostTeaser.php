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

namespace Agoat\PostsnPagesBundle\Contao;

use Contao\BackendTemplate;
use Contao\Config;
use Contao\Input;
use Contao\Pagination;
use Patchwork\Utf8;


/**
 * ModuleRelatedPostsTeaser class
 */
class ModuleRelatedPostTeaser extends ModulePost
{

    /**
     * Template
     *
     * @var string
     */
    protected $strTemplate = 'mod_postteaser';


    /**
     * Display a wildcard in the back end
     *
     * @return string
     */
    public function generate()
    {
        if (TL_MODE == 'BE') {
            /** @var BackendTemplate $objTemplate */
            $objTemplate = new BackendTemplate('be_wildcard');

            $objTemplate->wildcard = '### ' . Utf8::strtoupper($GLOBALS['TL_LANG']['FMD']['relatedpostteaser'][0]) . ' ###';
            $objTemplate->title = $this->headline;
            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name;
            $objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

            return $objTemplate->parse();
        }

        // Set the item from the auto_item parameter
        if (!isset($_GET['posts']) && Config::get('useAutoItem') && isset($_GET['auto_item'])) {
            Input::setGet('posts', Input::get('auto_item'));
        }

        return parent::generate();
    }


    /**
     * Generate the module
     */
    protected function compile()
    {
        // Get the post alias(id)
        $strPost = Input::get('posts');

        if (!strlen($strPost)) {
            return;
        }

        $objPosts = $this->getRelatedPosts($strPost);

        // Set custom post template
        $this->postTemplate = $this->teaserTpl;

        $arrPosts = [];

        if ($objPosts !== null) {
            while ($objPosts->next()) {
                // Render the teasers
                $arrPosts[] = $this->renderPost($objPosts->current(), true, false);
            }
        }

        if ($this->perPage > 0) {
            // Add the pagination menu
            $objPagination = new Pagination($this->numberOfItems ? min($this->numberOfItems - $this->skipFirst, $this->totalPosts) : $this->totalPosts, $this->perPage, Config::get('maxPaginationLinks'), $id = 'page_n' . $this->id);
            $this->Template->pagination = $objPagination->generate("\n  ");
        }

        if ($this->sortRelated == 'random') {
            shuffle($arrPosts);
        }

        $this->Template->posts = $arrPosts;
    }

}
