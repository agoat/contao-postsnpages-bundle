<?php

/*
 * Contao Extended Articles Extension
 *
 * Copyright (c) 2017 Arne Stappen
 *
 * @license LGPL-3.0+
 */
 

/**
 * Palettes
 */
$GLOBALS['TL_DCA']['tl_module']['palettes']['posts']  = '{title_legend},name,headline,type;{config_legend},featured,showTeaser,numberOfItems,skipFirst,perPage;{archive_legend:hide},archive;{sort_legend:hide},sortPosts, sortOrder;{filter_legend:hide},filterByCategory;{template_legend:hide},postTpl,customTpl;{image_legend:hide},imgSize;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID';
$GLOBALS['TL_DCA']['tl_module']['palettes']['teaser']  = '{title_legend},name,headline,type;{config_legend},featured,readerModule,numberOfItems,skipFirst,perPage;{archive_legend:hide},archive;{sort_legend:hide},sortPosts, sortOrder;{filter_legend:hide},filterByCategory;{redirect_legend},jumpTo;{template_legend:hide},teaserTpl,customTpl;{image_legend:hide},imgSize;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID';

$bundles = \System::getContainer()->getParameter('kernel.bundles');

if (isset($bundles['ContaoCommentsBundle']))
{
	$GLOBALS['TL_DCA']['tl_module']['palettes']['postreader']  = '{title_legend},name,headline,type;{config_legend},showTeaser;{template_legend:hide},postTpl,customTpl;{image_legend:hide},imgSize;{comment_legend},allowComments;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID';
}
else
{
	$GLOBALS['TL_DCA']['tl_module']['palettes']['postreader']  = '{title_legend},name,headline,type;{config_legend},showTeaser;{template_legend:hide},postTpl,customTpl;{image_legend:hide},imgSize;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID';
}


$GLOBALS['TL_DCA']['tl_module']['palettes']['tags']  = '{title_legend},name,headline,type;{config_legend},numberOfItems;{archive_legend:hide},archive;{sort_legend:hide},sortTags, sortOrder;{redirect_legend},jumpTo;{template_legend:hide},tagsTpl,customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID';
$GLOBALS['TL_DCA']['tl_module']['palettes']['taggedposts']  = '{title_legend},name,headline,type;{config_legend},featured,perPage;{archive_legend:hide},archive;{sort_legend:hide},sortPosts,sortOrder;{redirect_legend},jumpTo;{template_legend:hide},teaserTpl,customTpl;{image_legend:hide},imgSize;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID';
$GLOBALS['TL_DCA']['tl_module']['palettes']['taggedteaser']  = '{title_legend},name,headline,type;{config_legend},featured,perPage;{archive_legend:hide},archive;{sort_legend:hide},sortPosts,sortOrder;{redirect_legend},jumpTo;{template_legend:hide},teaserTpl,customTpl;{image_legend:hide},imgSize;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID';


$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'] = array_merge
(
	$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'],
	array('filterByCategory', 'allowComments')
);
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['filterByCategory'] = 'category';
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['allowComments'] = 'com_order,perPage,com_moderate,com_bbcode,com_protected,com_requireLogin,com_disableCaptcha,com_template,notifyAdmin';



/**
 * Fields
 */
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
$GLOBALS['TL_DCA']['tl_module']['fields']['featured'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['featured'],
	'default'                 => 'all',
	'exclude'                 => true,
	'inputType'               => 'select',
	'options'                 => array('all_articles', 'featured_articles', 'unfeatured_articles'),
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
	'options_callback'        => array('tl_module_posts', 'getPostTemplates'),
	'eval'                    => array('tl_class'=>'w50'),
	'sql'                     => "varchar(64) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['teaserTpl'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['teaserTpl'],
	'exclude'                 => true,
	'inputType'               => 'select',
	'options_callback'        => array('tl_module_posts', 'getTeaserTemplates'),
	'eval'                    => array('tl_class'=>'w50'),
	'sql'                     => "varchar(64) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['tagsTpl'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['tagsTpl'],
	'exclude'                 => true,
	'inputType'               => 'select',
	'options_callback'        => array('tl_module_posts', 'getTagsTemplates'),
	'eval'                    => array('tl_class'=>'w50'),
	'sql'                     => "varchar(64) NOT NULL default ''"
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
	'options_callback'        => array('tl_module_posts', 'getArticleCategories'),
	'eval'                    => array('chosen'=>true, 'tl_class'=>'w50'),
	'sql'                     => "varchar(255) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['archive'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['archive'],
	'exclude'                 => true,
	'inputType'               => 'archiveTree',
	'eval'                    => array('multiple'=>	true, 'fieldType'=>'checkbox','tl_class'=>'clr'),
	'sql'                     => "blob NULL"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['tags'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['tags'],
	'exclude'                 => true,
	'inputType'               => 'select',
	'options_callback'		  => array('tl_module_posts', 'getArchives'),
	'reference'               => &$GLOBALS['TL_LANG']['tl_module']['tags'],
	'eval'                    => array('tl_class'=>'w50'),
	'sql'                     => "varchar(8) NOT NULL default ''"
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
$GLOBALS['TL_DCA']['tl_module']['fields']['allowComments'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['allowComments'],
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
	'eval'                    => array('submitOnChange'=>true,'tl_class'=>'w50 m12'),
	'sql'                     => "char(1) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_module']['fields']['type']['load_callback'][] = array('tl_module_posts', 'adjustDCA');


$GLOBALS['TL_DCA']['tl_module']['fields']['defineRoot']['eval']['tl_class'] = 'clr w50 m12';
$GLOBALS['TL_DCA']['tl_module']['fields']['numberOfItems']['default'] = 10;


/**
 * Provide miscellaneous methods that are used by the data configuration array.
 *
 * @author Arne Stappen <https://github.com/agoat>
 */
class tl_module_posts extends Backend
{

	/**
	 * Explizit DCA configuration for some modules
	 *
	 * @param DataContainer $dc
	 *
	 * @return array
	 */
	public function adjustDCA($value, DataContainer $dc)
	{
		switch ($value)
		{
			// tags module
			case 'tags':
				$GLOBALS['TL_DCA']['tl_module']['fields']['archive']['eval']['mandatory'] = true;
				$GLOBALS['TL_DCA']['tl_module']['fields']['jumpTo']['eval']['mandatory'] = true;
				break;

			// tagged teaser module
			case 'taggedposts':
			case 'taggedteaser':
				$GLOBALS['TL_DCA']['tl_module']['fields']['archive']['eval']['mandatory'] = true;
				break;
		}
		
		return $value;
	}
	

	
	/**
	 * Return all article templates as array
	 *
	 * @param DataContainer $dc
	 *
	 * @return array
	 */
	public function getPostTemplates()
	{
		return $this->getTemplateGroup('post_');
	}
	
	
	/**
	 * Return all article teaser templates as array
	 *
	 * @param DataContainer $dc
	 *
	 * @return array
	 */
	public function getTeaserTemplates()
	{
		return $this->getTemplateGroup('teaser_');
	}	

	
	/**
	 * Return all article teaser templates as array
	 *
	 * @param DataContainer $dc
	 *
	 * @return array
	 */
	public function getTagsTemplates()
	{
		return $this->getTemplateGroup('tags_');
	}	

	
	/**
	 * Get all news reader modules and return them as array
	 *
	 * @return array
	 */
	public function getReaderModules()
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
	 * Return the edit article alias wizard
	 *
	 * @param DataContainer $dc
	 *
	 * @return string
	 */
	public function editModule(DataContainer $dc)
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
	public function setOrder($value, DataContainer $dc)
	{
		if ('' == $value && in_array($dc->activeRecord->type, array_keys($GLOBALS['FE_MOD']['post'])))
		{
			$value = 'descending';
		}

		return $value;
	}

	
	/**
	 * Return the archives
	 *
	 * @param DataContainer $dc
	 *
	 * @return array
	 */
	public function getArchives(DataContainer $dc)
	{
		$arrArchives = array
		(
			'page' => array
			(
				'all' 	=> 'all',
				'first'	=> 'first'
			)
		);

		$objArchives = \ArchiveModel::findAll();
		
		if ($objArchives === null)
		{
			return $arrArchives;
		}
		
		$arrArchives['particular'] = $objArchives->fetchEach('title');

		return $arrArchives;
	}
	
	
	/**
	 * Return the archives
	 *
	 * @param DataContainer $dc
	 *
	 * @return array
	 */
	public function getTags(DataContainer $dc)
	{
		$arrTags = array
		(
			'page' => array
			(
				'all' 	=> 'all'
			)
		);

		$objTags = \TagsModel::findAll();
		
		if ($objTags === null)
		{
			return $arrTags;
		}
		
		$arrTags['particular'] = $objTags->fetchEach('tag');

		return $arrTags;
	}
	
	
	/**
	 * Return the article categories
	 *
	 * @param DataContainer $dc
	 *
	 * @return array
	 */
	public function getArticleCategories(DataContainer $dc)
	{
		$objArticles = \ArticleModel::findAll();

		$arrCat = array();
		
		foreach ($objArticles as $objArticle)
		{
			if (is_array($category = \StringUtil::deserialize($objArticle->category)))
			{
				foreach ($category as $val)
				{
					$arrCat[$val] = $val;
				}
			}
		}

		return $arrCat;
	}

}
