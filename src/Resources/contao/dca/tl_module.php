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

use Agoat\PostsnPagesBundle\Model\PostModel;
use Contao\Backend;
use Contao\DataContainer;
use Contao\Image;
use Contao\StringUtil;
use Contao\System;


$bundles = System::getContainer()->getParameter('kernel.bundles');

$GLOBALS['TL_DCA']['tl_module']['palettes']['postcontent']  = '{title_legend},name,headline,type;{config_legend},featured,numberOfItems,skipFirst,perPage,showTeaser;{archive_legend},archive;{sort_legend},sortPosts, sortOrder;{filter_legend:hide},filterByCategory;{template_legend:hide},postTpl,customTpl;{image_legend:hide},imgSize;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID';

$GLOBALS['TL_DCA']['tl_module']['palettes']['postteaser']  = '{title_legend},name,headline,type;{config_legend},featured,numberOfItems,skipFirst,perPage;{archive_legend},archive;{sort_legend},sortPosts, sortOrder;{filter_legend:hide},filterByCategory;{redirect_legend},' . (isset($bundles['AgoatPermalinkBundle']) ? '' : 'jumpTo,') . 'alternativeLink;{image_legend:hide},imgSize;{template_legend:hide},teaserTpl,customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID';
$GLOBALS['TL_DCA']['tl_module']['palettes']['postcomments']  = '{title_legend},name,headline,type;{comment_legend},com_order,perPage,com_moderate,com_bbcode,com_protected,com_requireLogin,com_disableCaptcha;{template_legend:hide},com_template,customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID';
$GLOBALS['TL_DCA']['tl_module']['palettes']['relatedpostteaser']  = '{title_legend},name,headline,type;{config_legend},numberOfItems,skipFirst,perPage;{sort_legend},sortRelated,sortOrder;{image_legend:hide},imgSize;{template_legend:hide},teaserTpl,customTpl;{redirect_legend},' . (isset($bundles['AgoatPermalinkBundle']) ? '' : 'jumpTo,') . 'alternativeLink;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID';
$GLOBALS['TL_DCA']['tl_module']['palettes']['taggedpostteaser']  = '{title_legend},name,headline,type;{config_legend},featured,numberOfItems,skipFirst,perPage;{tagmenu_legend},tagmenuModule;{sort_legend},sortPosts,sortOrder;{image_legend:hide},imgSize;{template_legend:hide},teaserTpl,customTpl;{redirect_legend},' . (isset($bundles['AgoatPermalinkBundle']) ? '' : 'jumpTo,') . 'alternativeLink;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID';
$GLOBALS['TL_DCA']['tl_module']['palettes']['posttagmenu']  = '{title_legend},name,headline,type;{config_legend},numberOfItems;{archive_legend},archive;{sort_legend},sortTags,sortOrder;{redirect_legend},jumpTo;{template_legend:hide},tagsTpl,customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID';
$GLOBALS['TL_DCA']['tl_module']['palettes']['static']  = '{title_legend},name,headline,type;{static_legend:hide},staticContent;{template_legend:hide},customTpl,noMarkup;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID';
$GLOBALS['TL_DCA']['tl_module']['palettes']['containerlist']  = '{title_legend},name,headline,type;{config_legend},skipFirst,section;{reference_legend:hide},defineRoot;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID';

$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'] = array_merge
(
	$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'],
	array('filterByCategory')
);
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['filterByCategory'] = 'category';


$GLOBALS['TL_DCA']['tl_module']['fields']['readerModule'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['readerModule'],
	'exclude'                 => true,
	'inputType'               => 'select',
	'options_callback'        => array('tl_module_posts', 'getReaderModules'),
	'reference'               => &$GLOBALS['TL_LANG']['tl_module'],
	'eval'                    => array('mandatory'=>true, 'submitOnChange'=>true, 'tl_class'=>'w50 wizard'),
	'wizard' => array
	(
		array('tl_module_posts', 'editModule')
	),
	'sql'                     => "int(10) unsigned NOT NULL default '0'"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['tagmenuModule'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['tagmenuModule'],
	'exclude'                 => true,
	'inputType'               => 'select',
	'options_callback'        => array('tl_module_posts', 'getTagMenuModules'),
	'reference'               => &$GLOBALS['TL_LANG']['tl_module'],
	'eval'                    => array('mandatory'=>true, 'submitOnChange'=>true, 'tl_class'=>'w50 wizard'),
	'wizard' => array
	(
		array('tl_module_posts', 'editModule')
	),
	'sql'                     => "int(10) unsigned NOT NULL default '0'"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['featured'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['featuredPosts'],
	'default'                 => 'all_posts',
	'exclude'                 => true,
	'inputType'               => 'select',
	'options'                 => array('all_posts', 'featured_posts', 'unfeatured_posts'),
	'reference'               => &$GLOBALS['TL_LANG']['tl_module'],
	'eval'                    => array('tl_class'=>'w50'),
	'sql'                     => "varchar(32) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['showTeaser'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['showTeaser'],
	'exclude'                 => true,
	'inputType'               => 'checkbox',
	'eval'                    => array('tl_class'=>'w50 m12'),
	'sql'                     => "char(1) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['postTpl'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['postTpl'],
	'exclude'                 => true,
	'inputType'               => 'select',
	'options_callback' => function ()
	{
		return $this->getTemplateGroup('post_');
	},
	'eval'                    => array('tl_class'=>'w50'),
	'sql'                     => "varchar(64) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['teaserTpl'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['teaserTpl'],
	'exclude'                 => true,
	'inputType'               => 'select',
	'options_callback' => function ()
	{
		return $this->getTemplateGroup('teaser_');
	},
	'eval'                    => array('tl_class'=>'w50'),
	'sql'                     => "varchar(64) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['tagsTpl'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['tagsTpl'],
	'exclude'                 => true,
	'inputType'               => 'select',
	'options_callback' => function ()
	{
		return $this->getTemplateGroup('tags_');
	},
	'eval'                    => array('tl_class'=>'w50'),
	'sql'                     => "varchar(64) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['alternativeLink'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['alternativeLink'],
	'exclude'                 => true,
	'inputType'               => 'checkbox',
	'eval'                    => array('tl_class'=>'w50 m12'),
	'sql'                     => "char(1) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['noMarkup'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['noMarkup'],
	'exclude'                 => true,
	'inputType'               => 'checkbox',
	'eval'                    => array('tl_class'=>'w50 m12'),
	'sql'                     => "char(1) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['filterByCategory'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['filterByCategory'],
	'exclude'                 => true,
	'inputType'               => 'checkbox',
	'eval'                    => array('submitOnChange'=>true),
	'sql'                     => "char(1) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['category'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['category'],
	'exclude'                 => true,
	'inputType'               => 'select',
	'options_callback'        => array('tl_module_posts', 'getPostsCategories'),
	'eval'                    => array('chosen'=>true, 'tl_class'=>'w50'),
	'sql'                     => "varchar(255) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['archive'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['archive'],
	'exclude'                 => true,
	'inputType'               => 'archiveTree',
	'eval'                    => array('multiple' => true, 'fieldType'=>'checkbox', 'tl_class'=>'clr'),
	'sql'                     => "blob NULL"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['sortPosts'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['sortPosts'],
	'exclude'                 => true,
	'inputType'               => 'select',
	'options'                 => array('date', 'title', 'popular', 'location', 'random'),
	'reference'               => &$GLOBALS['TL_LANG']['MSC']['sortPosts'],
	'eval'                    => array('tl_class'=>'w50'),
	'sql'                     => "char(16) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['sortRelated'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['sortPosts'],
	'exclude'                 => true,
	'inputType'               => 'select',
	'options'                 => array('custom', 'date', 'title', 'popular', 'location', 'random'),
	'reference'               => &$GLOBALS['TL_LANG']['MSC']['sortPosts'],
	'eval'                    => array('tl_class'=>'w50'),
	'sql'                     => "char(16) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['sortTags'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['sortTags'],
	'exclude'                 => true,
	'inputType'               => 'select',
	'options'                 => array('label', 'count', 'random'),
	'reference'               => &$GLOBALS['TL_LANG']['MSC']['sortTags'],
	'eval'                    => array('tl_class'=>'w50'),
	'sql'                     => "char(16) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['sortOrder'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['sortOrder'],
	'exclude'                 => true,
	'inputType'               => 'select',
	'options'                 => array('ascending', 'descending'),
	'reference'               => &$GLOBALS['TL_LANG']['MSC'],
	'load_callback' => array
	(
		array('tl_module_posts', 'setOrder')
	),
	'eval'                    => array('tl_class'=>'w50'),
	'sql'                     => "varchar(16) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['addComments'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['addComments'],
	'exclude'                 => true,
	'inputType'               => 'checkbox',
	'eval'                    => array('submitOnChange'=>true),
	'sql'                     => "char(1) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['notifyAdmin'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['notifyAdmin'],
	'exclude'                 => true,
	'inputType'               => 'checkbox',
	'eval'                    => array('tl_class'=>'w50'),
	'sql'                     => "char(1) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['staticContent'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['staticContent'],
	'exclude'                 => true,
	'inputType'               => 'staticTree',
	'eval'                    => array('mandatory'=>true, 'fieldType'=>'radio', 'filesOnly'=>true, 'tl_class'=>'clr'),
	'sql'                     => "int(10) unsigned NOT NULL default '0'"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['section'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['section'],
	'default'                 => 'main',
	'exclude'                 => true,
	'inputType'               => 'select',
	'options_callback'        => array('tl_module', 'getLayoutSections'),
	'reference'               => &$GLOBALS['TL_LANG']['COLS'],
	'eval'                    => array('tl_class'=>'w50'),
	'sql'                     => "varchar(32) NOT NULL default ''"
);


$GLOBALS['TL_DCA']['tl_module']['fields']['type']['load_callback'][] = array('tl_module_posts', 'adjustDCA');


$GLOBALS['TL_DCA']['tl_module']['fields']['defineRoot']['eval']['tl_class'] = 'clr w50 m12';
$GLOBALS['TL_DCA']['tl_module']['fields']['numberOfItems']['default'] = 10;


/**
 * Provide miscellaneous methods that are used by the data configuration array.
 *
 * @author Arne Stappen (alias aGoat) <https://agoat.xyz>
 */
class tl_module_posts extends Backend
{

	/**
	 * Explicitly set the DCA configuration for some modules
	 *
	 * @param DataContainer $dc
	 *
	 * @return array
	 */
	public function adjustDCA ($value, DataContainer $dc)
	{
		switch ($value)
		{
			// Tags module
			case 'tags':
				$GLOBALS['TL_DCA']['tl_module']['fields']['archive']['eval']['mandatory'] = true;
				$GLOBALS['TL_DCA']['tl_module']['fields']['jumpTo']['eval']['mandatory'] = true;
				break;

			// Tagged teaser module
			case 'taggedposts':
			case 'taggedteaser':
				$GLOBALS['TL_DCA']['tl_module']['fields']['archive']['eval']['mandatory'] = true;
				break;
		}

		return $value;
	}


	/**
	 * Get all post reader modules and return them as array
	 *
	 * @return array
	 */
	public function getReaderModules ()
	{
		$arrModules = array();
		$objModules = $this->Database->execute("SELECT m.id, m.name, t.name AS theme FROM tl_module m LEFT JOIN tl_theme t ON m.pid=t.id WHERE m.type='postreader' ORDER BY t.name, m.name");

		while ($objModules->next())
		{
			$arrModules[$objModules->theme][$objModules->id] = $objModules->name . ' (ID ' . $objModules->id . ')';
		}

		return $arrModules;
	}


	/**
	 * Get all related posts modules and return them as array
	 *
	 * @return array
	 */
	public function getRelatedModules ()
	{
		$arrModules = array();
		$objModules = $this->Database->execute("SELECT m.id, m.name, t.name AS theme FROM tl_module m LEFT JOIN tl_theme t ON m.pid=t.id WHERE m.type='relatedpoststeaser' ORDER BY t.name, m.name");

		while ($objModules->next())
		{
			$arrModules[$objModules->theme][$objModules->id] = $objModules->name . ' (ID ' . $objModules->id . ')';
		}

		return $arrModules;
	}


	/**
	 * Get all posts tags menu modules and return them as array
	 *
	 * @return array
	 */
	public function getTagMenuModules ()
	{
		$arrModules = array();
		$objModules = $this->Database->execute("SELECT m.id, m.name, t.name AS theme FROM tl_module m LEFT JOIN tl_theme t ON m.pid=t.id WHERE m.type='posttagmenu' ORDER BY t.name, m.name");

		while ($objModules->next())
		{
			$arrModules[$objModules->theme][$objModules->id] = $objModules->name . ' (ID ' . $objModules->id . ')';
		}

		return $arrModules;
	}


	/**
	 * Return the edit module alias wizard
	 *
	 * @param DataContainer $dc
	 *
	 * @return string
	 */
	public function editModule (DataContainer $dc)
	{
		return ($dc->value < 1) ? '' : ' <a href="contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $dc->value . '&amp;popup=1&amp;nb=1&amp;rt=' . REQUEST_TOKEN . '" title="' . \StringUtil::specialchars($GLOBALS['TL_LANG']['tl_module']['edit_module']) . '" onclick="Backend.openModalIframe({\'width\':768,\'title\':\'' . \StringUtil::specialchars(str_replace("'", "\\'", $GLOBALS['TL_LANG']['tl_module']['edit_module'])) . '\',\'url\':this.href});return false">' . Image::getHtml('alias.svg', $GLOBALS['TL_LANG']['tl_module']['edit_module']) . '</a>';
	}


	/**
	 * Return the sorting order depending on the module type
	 *
	 * @param string $value
	 * @param DataContainer $dc
	 *
	 * @return string
	 */
	public function setOrder ($value, DataContainer $dc)
	{
		if ('' == $value && in_array($dc->activeRecord->type, array_keys($GLOBALS['FE_MOD']['posts'])))
		{
			$value = 'descending';
		}

		return $value;
	}


	/**
	 * Return the posts categories
	 *
	 * @param DataContainer $dc
	 *
	 * @return array
	 */
	public function getPostsCategories (DataContainer $dc)
	{
		$objPosts = PostModel::findAll();

		$archiveIds = StringUtil::deserialize($dc->activeRecord->archive);
		$arrCat = array();

		if ($objPosts !== null) {
			foreach ($objPosts as $objPost) {
			    if (in_array($objPost->pid, $archiveIds)) {
                     $arrCat[$objPost->category] = $objPost->category;
                }
			}
		}

		return $arrCat;
	}
}
