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

use Contao\Backend;
use Contao\BackendUser;
use Contao\DataContainer;
use Contao\Image;
use Contao\StringUtil;
use Contao\System;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface;


// Set new driver
$GLOBALS['TL_DCA']['tl_page']['config']['dataContainer'] = 'TableExtended';

// Set sorting icon
$GLOBALS['TL_DCA']['tl_page']['list']['sorting']['icon'] = 'NA';
$GLOBALS['TL_DCA']['tl_page']['list']['sorting']['folders'] = 'root';

// Set new child tables
array_push($GLOBALS['TL_DCA']['tl_page']['config']['ctable'], 'tl_container', 'tl_archive');

// Unset the article child table
//$GLOBALS['TL_DCA']['tl_page']['config']['ctable'] = array_diff($GLOBALS['TL_DCA']['tl_page']['config']['ctable'], ['tl_article']);


// Replace the generateArticle callback
foreach ($GLOBALS['TL_DCA']['tl_page']['config']['onsubmit_callback'] as &$callback) {
    if ($callback[0] == 'tl_page' && $callback[1] == 'generateArticle') {
        $callback = ['tl_page_postsnpages', 'generateContainer'];
    }
}
unset($callback);

// Change the articles edit button
unset($GLOBALS['TL_DCA']['tl_page']['list']['operations']['articles']);
$GLOBALS['TL_DCA']['tl_page']['list']['operations']['content'] = [
    'label'           => &$GLOBALS['TL_LANG']['tl_page']['content'],
    'href'            => 'do=pages',
    'icon'            => 'articles.svg',
    'button_callback' => ['tl_page_postsnpages', 'editContent'],
];


System::loadLanguageFile('tl_content');

$bundles = \System::getContainer()->getParameter('kernel.bundles');

$GLOBALS['TL_DCA']['tl_page']['palettes']['post'] =
    '{title_legend},title,alias,type;{meta_legend},pageTitle,robots,description;{posts_legend},showTeaser;{empty_legend},emptyPost;{template_legend:hide},postTpl;' . (isset($bundles['ContaoCommentsBundle']) ? '{comment_legend},;' : '') . '{layout_legend:hide},includeLayout;{cache_legend:hide},includeCache;{chmod_legend:hide},includeChmod;{expert_legend:hide},cssClass,sitemap,hide,noSearch;{publish_legend},published,start,stop';

$GLOBALS['TL_DCA']['tl_page']['palettes']['__selector__'] =
    array_merge($GLOBALS['TL_DCA']['tl_page']['palettes']['__selector__'],
        ['showTeaser', 'emptyPost']
    );
$GLOBALS['TL_DCA']['tl_page']['subpalettes']['showTeaser'] = 'imgSize';
$GLOBALS['TL_DCA']['tl_page']['subpalettes']['emptyPost_page'] = 'jumpTo';


/**
 * Fields
 */
$GLOBALS['TL_DCA']['tl_page']['fields']['showTeaser'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_page']['showTeaser'],
    'exclude'   => true,
    'inputType' => 'checkbox',
    'eval'      => ['submitOnChange' => true, 'tl_class' => 'w50'],
    'sql'       => "char(1) NOT NULL default ''",
];
$GLOBALS['TL_DCA']['tl_page']['fields']['imgSize'] = [
    'label'            => &$GLOBALS['TL_LANG']['tl_content']['size'],
    'exclude'          => true,
    'inputType'        => 'imageSize',
    'reference'        => &$GLOBALS['TL_LANG']['MSC'],
    'eval'             => [
        'rgxp'               => 'natural',
        'includeBlankOption' => true,
        'nospace'            => true,
        'helpwizard'         => true,
        'tl_class'           => 'w50',
    ],
    'options_callback' => function () {
        return System::getContainer()->get('contao.image.image_sizes')->getOptionsForUser(BackendUser::getInstance());
    },
    'sql'              => "varchar(64) NOT NULL default ''",
];
$GLOBALS['TL_DCA']['tl_page']['fields']['postTpl'] = [
    'label'            => &$GLOBALS['TL_LANG']['tl_page']['postTpl'],
    'exclude'          => true,
    'inputType'        => 'select',
    'options_callback' => function () {
        return $this->getTemplateGroup('post_');
    },
    'eval'             => ['tl_class' => 'w50'],
    'sql'              => "varchar(64) NOT NULL default ''",
];
$GLOBALS['TL_DCA']['tl_page']['fields']['emptyPost'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_page']['emptyPost'],
    'exclude'   => true,
    'inputType' => 'select',
    'options'   => ['nothing', 'recent', 'page', 'notfound'],
    'reference' => &$GLOBALS['TL_LANG']['tl_page']['emptyPost'],
    'eval'      => ['submitOnChange' => true, 'tl_class' => 'w50'],
    'sql'       => "char(16) NOT NULL default ''",
];

/**
 * Provide miscellaneous methods that are used by the data configuration array.
 */
class tl_page_postsnpages extends Backend
{

    /**
     * Automatically create an container in the main column for new pages
     *
     * @param  DataContainer  $dc
     */
    public function generateContainer(DataContainer $dc)
    {
        // Return if there is no active record (override all)
        if (!$dc->activeRecord) {
            return;
        }

        // No title or not a regular page
        if ($dc->activeRecord->title == '' || !in_array($dc->activeRecord->type,
                ['regular', 'error_403', 'error_404']
            )) {
            return;
        }

        /** @var AttributeBagInterface $objSessionBag */
        $objSessionBag = System::getContainer()->get('session')->getBag('contao_backend');

        $new_records = $objSessionBag->get('new_records');

        // Not a new page
        if (!$new_records || !is_array($new_records[$dc->table]) || !in_array($dc->id, $new_records[$dc->table])) {
            return;
        }

        // Check whether there are containers (e.g. on copied pages)
        $objTotal =
            $this->Database->prepare("SELECT COUNT(*) AS count FROM tl_container WHERE pid=?")->execute($dc->id);

        if ($objTotal->count > 0) {
            return;
        }

        // Create container
        $arrSet['pid'] = $dc->id;
        $arrSet['sorting'] = 128;
        $arrSet['tstamp'] = time();
        $arrSet['section'] = 'main';
        $arrSet['title'] = $dc->activeRecord->title;
        $arrSet['published'] = $dc->activeRecord->published;

        $this->Database->prepare("INSERT INTO tl_container %s")->set($arrSet)->execute();
    }


    /**
     * Generate an "edit content" button and return it as string
     *
     * @param  array  $row
     * @param  string  $href
     * @param  string  $label
     * @param  string  $title
     * @param  string  $icon
     *
     * @return string
     */
    public function editContent($row, $href, $label, $title, $icon)
    {
        switch ($row['type']) {
            case 'post':
                return '<a href="' . $this->addToUrl('do=posts&amp;pn=' . $row['id']
                    ) . '" title="' . StringUtil::specialchars($title
                    ) . '">' . Image::getHtml('bundles/agoatpostsnpages/archive.svg', $label) . '</a> ';

                break;

            case 'regular':
            case 'error_403':
            case 'error_404':
                return '<a href="' . $this->addToUrl('do=pages&amp;pn=' . $row['id']
                    ) . '" title="' . StringUtil::specialchars($title) . '">' . Image::getHtml($icon, $label) . '</a> ';

                break;

            default:
                return Image::getHtml(preg_replace('/\.svg$/i', '_.svg', $icon)) . ' ';
        }
    }

}
