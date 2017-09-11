<?php

/*
 * Contao Extended Articles Extension
 *
 * Copyright (c) 2017 Arne Stappen
 *
 * @license LGPL-3.0+
 */
 
 
 /**
 * Load tl_content language file
 */
System::loadLanguageFile('tl_content');


/**
 * Table tl_posts
 */
$GLOBALS['TL_DCA']['tl_posts'] = array
(

	// Config
	'config' => array
	(
		'dataContainer'               => 'TableExtended',
		'ptable'                      => 'tl_archive',
		'ctable'                      => array('tl_content'),
		'switchToEdit'                => true,
		'enableVersioning'            => true,
		'onload_callback' => array
		(
			array('tl_posts', 'checkPermission'),
			//array('tl_page', 'addBreadcrumb')
		),
		'onsubmit_callback' => array
		(
			array('tl_posts', 'adjustTime'),
		),
		'sql' => array
		(
			'keys' => array
			(
				'id' => 'primary',
				'alias' => 'index',
				'pid,start,stop,published' => 'index',
				'pid,featured,start,stop,published' => 'index'
			)
		)
	),

	// List
	'list' => array
	(
		'sorting' => array
		(
			'mode'                    => 4,
			'fields'                  => array('date DESC', 'title', 'author'),
			'paste_button_callback'   => array('tl_posts', 'pastePost'),
			'panelLayout'             => 'filter;filter;sort,search,limit',
			'headerFields'            => array('title', 'protected'),
			'child_record_callback'   => array('tl_posts', 'renderPost'),
		),
		'label' => array
		(
			'fields'                  => array('title', 'date'),
			'format'                  => '%s <span style="color:#999;padding-left:3px">- %s</span>',
		),
		'global_operations' => array
		(
			'all' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['MSC']['all'],
				'href'                => 'act=select',
				'class'               => 'header_edit_all',
				'attributes'          => 'onclick="Backend.getScrollOffset()" accesskey="e"'
			)
		),
		'operations' => array
		(
			'edit' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_posts']['edit'],
				'href'                => 'table=tl_content',
				'icon'                => 'edit.svg',
				'button_callback'     => array('tl_posts', 'editPost')
			),
			'editheader' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_posts']['editheader'],
				'href'                => 'act=edit',
				'icon'                => 'header.svg',
				'button_callback'     => array('tl_posts', 'editHeader')
			),
			'copy' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_posts']['copy'],
				'href'                => 'act=paste&amp;mode=copy',
				'icon'                => 'copy.svg',
				'attributes'          => 'onclick="Backend.getScrollOffset()"',
				'button_callback'     => array('tl_posts', 'copyPost')
			),
			'delete' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_posts']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.svg',
				'attributes'          => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"',
				'button_callback'     => array('tl_posts', 'deletePost')
			),
			'toggle' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_posts']['toggle'],
				'icon'                => 'visible.svg',
				'attributes'          => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
				'button_callback'     => array('tl_posts', 'toggleIcon')
			),
			'feature' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_posts']['feature'],
				'icon'                => 'featured.svg',
				'attributes'          => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleFeaturedArticle(this,%s)"',
				'button_callback'     => array('tl_posts', 'iconFeatured')		
			),
			'show' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_posts']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.svg'
			)
		)
	),

	// Select
	'select' => array
	(
		'buttons_callback' => array
		(
			array('tl_posts', 'addAliasButton')
		)
	),

	// Palettes
	'palettes' => array
	(
		'__selector__'                => array('addImage', 'readmore'),
		'default'                     => '{title_legend},title,alias,author;{layout_legend},keywords;;{date_legend},date,time;{location_legend},location,latlong;{teaser_legend},subTitle,teaser;{image_legend},addImage;{category_legend},category;{readmore_legend},readmore;{related_legend},relatedPosts;{syndication_legend},printable;{template_legend:hide},customTpl;{expert_legend:hide},noComments,featured,format,cssID;{publish_legend},published,start,stop'
	),

	// Subpalettes
	'subpalettes' => array
	(
		'addImage'                   => 'singleSRC,alt,caption',
		'readmore'                   => 'url,target'
	),

	// Fields
	'fields' => array
	(
		'id' => array
		(
			'label'                   => array('ID'),
			'search'                  => true,
			'sql'                     => "int(10) unsigned NOT NULL auto_increment"
		),
		'pid' => array
		(
			'foreignKey'              => 'tl_page.title',
			'sql'                     => "int(10) unsigned NOT NULL default '0'",
			'relation'                => array('type'=>'belongsTo', 'load'=>'lazy')
		),
		'sorting' => array
		(
			'sql'                     => "int(10) unsigned NOT NULL default '0'"
		),
		'tstamp' => array
		(
			'sql'                     => "int(10) unsigned NOT NULL default '0'"
		),
		'popular' => array
		(
			'sql'                     => "int(10) unsigned NOT NULL default '0'"
		),
		'title' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_posts']['title'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'sorting'                 => true,
			'search'                  => true,
			'eval'                    => array('mandatory'=>true, 'decodeEntities'=>true, 'maxlength'=>255, 'tl_class'=>'w50'),
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'alias' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_posts']['alias'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'search'                  => true,
			'eval'                    => array('rgxp'=>'alias', 'doNotCopy'=>true, 'maxlength'=>128, 'tl_class'=>'w50 clr'),
			'save_callback' => array
			(
				array('tl_posts', 'generateAlias')
			),
			'sql'                     => "varchar(128) COLLATE utf8_bin NOT NULL default ''"
		),
		'author' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_posts']['author'],
			'default'                 => BackendUser::getInstance()->id,
			'exclude'                 => true,
			'search'                  => true,
			'filter'                  => true,
			'inputType'               => 'select',
			'foreignKey'              => 'tl_user.name',
			'eval'                    => array('doNotCopy'=>true, 'mandatory'=>true, 'chosen'=>true, 'includeBlankOption'=>true, 'tl_class'=>'w50'),
			'sql'                     => "int(10) unsigned NOT NULL default '0'",
			'relation'                => array('type'=>'hasOne', 'load'=>'eager')
		),
		'keywords' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_posts']['keywords'],
			'exclude'                 => true,
			'inputType'               => 'textarea',
			'search'                  => true,
			'eval'                    => array('style'=>'height:60px', 'decodeEntities'=>true, 'tl_class'=>'clr'),
			'sql'                     => "text NULL"
		),
		'date' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_posts']['date'],
			'default'                 => time(),
			'exclude'                 => true,
			'filter'                  => true,
			'sorting'                 => true,
			'flag'                    => 8,
			'inputType'               => 'text',
			'eval'                    => array('rgxp'=>'date', 'doNotCopy'=>true, 'datepicker'=>true, 'tl_class'=>'w50 wizard'),
			'sql'                     => "int(10) unsigned NOT NULL default '0'"
		),
		'time' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_posts']['time'],
			'default'                 => time(),
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('rgxp'=>'time', 'doNotCopy'=>true, 'tl_class'=>'w50'),
			'sql'                     => "int(10) unsigned NOT NULL default '0'"
		),
		'location' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_posts']['location'],
			'exclude'                 => true,
			'search'                  => true,
			'filter'                  => true,
			'sorting'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>255, 'tl_class'=>'w50'),
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'latlong' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_posts']['latlong'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('rgxp'=>'digit', 'multiple'=>true, 'size'=>2, 'tl_class'=>'w50'),
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'subTitle' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_posts']['subTitle'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>255, 'tl_class'=>'long'),
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'teaser' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_posts']['teaser'],
			'exclude'                 => true,
			'inputType'               => 'textarea',
			'search'                  => true,
			'eval'                    => array('rte'=>'tinyMCE', 'tl_class'=>'clr'),
			'sql'                     => "text NULL"
		),
		'addImage' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_posts']['addImage'],
			'exclude'                 => true,
			'filter'                  => true,
			'inputType'               => 'checkbox',
			'eval'                    => array('submitOnChange'=>true),
			'sql'                     => "char(1) NOT NULL default ''"
		),
		'singleSRC' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_content']['singleSRC'],
			'exclude'                 => true,
			'inputType'               => 'fileTree',
			'eval'                    => array('filesOnly'=>true, 'extensions'=>Config::get('validImageTypes'), 'fieldType'=>'radio', 'mandatory'=>true),
			'save_callback' => array
			(
		//		array('tl_news', 'storeFileMetaInformation')
			),
			'sql'                     => "binary(16) NULL"
		),
		'alt' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_content']['alt'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>255, 'tl_class'=>'w50'),
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'caption' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_content']['caption'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>255, 'allowHtml'=>true, 'tl_class'=>'w50'),
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'category' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_posts']['category'],
			'exclude'                 => true,
			'filter'                  => 1,
			'inputType'               => 'inputselect',
			'options_callback'        => array('tl_posts', 'getPostCategories'),
			'eval'                    => array('includeBlankOption'=>true, 'rgxp'=>'alias', 'multiple'=>true, 'noResult'=>$GLOBALS['TL_LANG']['tl_posts']['addCategory'], 'tl_class'=>'w50'),
			'sql'                     => "varchar(1022) NOT NULL default ''"
		),
		'tags' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_posts']['tags'],
			'exclude'                 => true,
			'filter'                  => true,
			'inputType'               => 'inputselect',
			'options_callback'        => array('tl_posts', 'getPostCategories'),
			'eval'                    => array('includeBlankOption'=>true, 'rgxp'=>'alias', 'multiple'=>true, 'noResult'=>$GLOBALS['TL_LANG']['tl_posts']['addCategory'], 'tl_class'=>'w50'),
			'sql'                     => "varchar(1022) NOT NULL default ''"
		),
		'readmore' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_posts']['readmore'],
			'exclude'                 => true,
			'filter'                  => true,
			'inputType'               => 'checkbox',
			'eval'                    => array('submitOnChange'=>true, 'tl_class'=>'w50'),
			'sql'                     => "varchar(1) NOT NULL default ''"
		),
		'url' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['MSC']['url'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'rgxp'=>'url', 'decodeEntities'=>true, 'maxlength'=>255, 'dcaPicker'=>true, 'fieldType'=>'radio', 'filesOnly'=>true, 'tl_class'=>'w50 wizard'),
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'target' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['MSC']['target'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'eval'                    => array('tl_class'=>'w50 m12'),
			'sql'                     => "char(1) NOT NULL default ''"
		),
		'relatedPosts' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_posts']['relatedPosts'],
			'exclude'                 => true,
			'inputType'               => 'articleTree',
			'eval'                    => array('multiple'=>	true, 'fieldType'=>'checkbox', 'orderField'=>'orderArticle', 'tl_class'=>'clr'),
			'sql'                     => "blob NULL"
		),
		'orderPosts' => array
		(
			'sql'                     => "blob NULL"
		),
		'printable' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_posts']['printable'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'options'                 => array('print', 'pdf', 'facebook', 'twitter', 'gplus'),
			'eval'                    => array('multiple'=>true),
			'reference'               => &$GLOBALS['TL_LANG']['tl_posts'],
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'customTpl' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_posts']['customTpl'],
			'exclude'                 => true,
			'inputType'               => 'select',
			'options_callback'        => array('tl_posts', 'getPostTemplates'),
			'eval'                    => array('includeBlankOption'=>true, 'chosen'=>true, 'tl_class'=>'w50'),
			'sql'                     => "varchar(64) NOT NULL default ''"
		),
		'format' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_posts']['format'],
			'exclude'                 => true,
			'filter'                  => true,
			'inputType'               => 'select',
			'options_callback'        => array('tl_posts', 'getPostFormats'),
			'reference'               => &$GLOBALS['TL_LANG']['tl_posts'],
			'eval'                    => array('tl_class'=>'w50 clr'),
			'sql'                     => "varchar(32) NOT NULL default ''"
		),
		'cssID' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_posts']['cssID'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('multiple'=>true, 'size'=>2, 'tl_class'=>'w50'),
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'featured' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_posts']['featured'],
			'exclude'                 => true,
			'filter'                  => true,
			'inputType'               => 'checkbox',
			'eval'                    => array('tl_class'=>'w50'),
			'sql'                     => "char(1) NOT NULL default ''"
		),
		'noComments' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_posts']['noComments'],
			'exclude'                 => true,
			'filter'                  => true,
			'inputType'               => 'checkbox',
			'eval'                    => array('tl_class'=>'w50'),
			'sql'                     => "char(1) NOT NULL default ''"
		),
		'published' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_posts']['published'],
			'exclude'                 => true,
			'filter'                  => true,
			'inputType'               => 'checkbox',
			'eval'                    => array('doNotCopy'=>true),
			'sql'                     => "char(1) NOT NULL default ''"
		),
		'start' => array
		(
			'exclude'                 => true,
			'label'                   => &$GLOBALS['TL_LANG']['tl_posts']['start'],
			'inputType'               => 'text',
			'eval'                    => array('rgxp'=>'datim', 'datepicker'=>true, 'tl_class'=>'w50 wizard'),
			'sql'                     => "varchar(10) NOT NULL default ''"
		),
		'stop' => array
		(
			'exclude'                 => true,
			'label'                   => &$GLOBALS['TL_LANG']['tl_posts']['stop'],
			'inputType'               => 'text',
			'eval'                    => array('rgxp'=>'datim', 'datepicker'=>true, 'tl_class'=>'w50 wizard'),
			'sql'                     => "varchar(10) NOT NULL default ''"
		)
	)
);


/**
 * Provide miscellaneous methods that are used by the data configuration array.
 *
 * @author Arne Stappen <https://github.com/agoat>
 */
class tl_posts extends Backend
{


	/**
	 * Import the back end user object
	 */
	public function __construct()
	{
		parent::__construct();
		$this->import('BackendUser', 'User');
	}


	/**
	 * Check permissions to edit table tl_page
	 *
	 * @throws Contao\CoreBundle\Exception\AccessDeniedException
	 */
	public function checkPermission()
	{
		if ($this->User->isAdmin)
		{
			return;
		}

		/** @var Symfony\Component\HttpFoundation\Session\SessionInterface $objSession */
		$objSession = System::getContainer()->get('session');

		$session = $objSession->all();

		// Set the default page user and group
		$GLOBALS['TL_DCA']['tl_page']['fields']['cuser']['default'] = intval(Config::get('defaultUser') ?: $this->User->id);
		$GLOBALS['TL_DCA']['tl_page']['fields']['cgroup']['default'] = intval(Config::get('defaultGroup') ?: $this->User->groups[0]);

		// Restrict the page tree
		$GLOBALS['TL_DCA']['tl_page']['list']['sorting']['root'] = $this->User->pagemounts;

		// Set allowed page IDs (edit multiple)
		if (is_array($session['CURRENT']['IDS']))
		{
			$edit_all = array();
			$delete_all = array();

			foreach ($session['CURRENT']['IDS'] as $id)
			{
				$objArticle = $this->Database->prepare("SELECT p.pid, p.includeChmod, p.chmod, p.cuser, p.cgroup FROM tl_archive a, tl_page p WHERE a.id=? AND a.pid=p.id")
											 ->limit(1)
											 ->execute($id);

				if ($objArticle->numRows < 1)
				{
					continue;
				}

				$row = $objArticle->row();

				if ($this->User->isAllowed(BackendUser::CAN_EDIT_ARTICLES, $row))
				{
					$edit_all[] = $id;
				}

				if ($this->User->isAllowed(BackendUser::CAN_DELETE_ARTICLES, $row))
				{
					$delete_all[] = $id;
				}
			}

			$session['CURRENT']['IDS'] = (Input::get('act') == 'deleteAll') ? $delete_all : $edit_all;
		}

		// Set allowed clipboard IDs
		if (isset($session['CLIPBOARD']['tl_archive']) && is_array($session['CLIPBOARD']['tl_archive']['id']))
		{
			$clipboard = array();

			foreach ($session['CLIPBOARD']['tl_archive']['id'] as $id)
			{
				$objArticle = $this->Database->prepare("SELECT p.pid, p.includeChmod, p.chmod, p.cuser, p.cgroup FROM tl_archive a, tl_page p WHERE a.id=? AND a.pid=p.id")
											 ->limit(1)
											 ->execute($id);

				if ($objArticle->numRows < 1)
				{
					continue;
				}

				if ($this->User->isAllowed(BackendUser::CAN_EDIT_ARTICLE_HIERARCHY, $objArticle->row()))
				{
					$clipboard[] = $id;
				}
			}

			$session['CLIPBOARD']['tl_archive']['id'] = $clipboard;
		}

		$permission = 0;

		// Overwrite the session
		$objSession->replace($session);

		// Check current action
		if (Input::get('act') && Input::get('act') != 'paste')
		{
			// Set ID of the article's page
			$objPage = $this->Database->prepare("SELECT pid FROM tl_archive WHERE id=?")
									  ->limit(1)
									  ->execute(Input::get('id'));

			$ids = $objPage->numRows ? array($objPage->pid) : array();

			// Set permission
			switch (Input::get('act'))
			{
				case 'edit':
				case 'toggle':
					$permission = BackendUser::CAN_EDIT_ARTICLES;
					break;

				case 'move':
					$permission = BackendUser::CAN_EDIT_ARTICLE_HIERARCHY;
					$ids[] = Input::get('sid');
					break;

				// Do not insert articles into a website root page
				case 'create':
				case 'copy':
				case 'copyAll':
				case 'cut':
				case 'cutAll':
					$permission = BackendUser::CAN_EDIT_ARTICLE_HIERARCHY;

					// Insert into a page
					if (Input::get('mode') == 2)
					{
						$objParent = $this->Database->prepare("SELECT id, type FROM tl_page WHERE id=?")
													->limit(1)
													->execute(Input::get('pid'));

						$ids[] = Input::get('pid');
					}

					// Insert after an article
					else
					{
						$objParent = $this->Database->prepare("SELECT id, type FROM tl_page WHERE id=(SELECT pid FROM tl_archive WHERE id=?)")
													->limit(1)
													->execute(Input::get('pid'));

						$ids[] = $objParent->id;
					}

					if ($objParent->numRows && $objParent->type == 'root')
					{
						throw new Contao\CoreBundle\Exception\AccessDeniedException('Attempt to insert an article into website root page ID ' . Input::get('pid') . '.');
					}
					break;

				case 'delete':
					$permission = BackendUser::CAN_DELETE_ARTICLES;
					break;
			}

			// Check user permissions
			if (Input::get('act') != 'show')
			{
				$pagemounts = array();

				// Get all allowed pages for the current user
				foreach ($this->User->pagemounts as $root)
				{
					$pagemounts[] = $root;
					$pagemounts = array_merge($pagemounts, $this->Database->getChildRecords($root, 'tl_page'));
				}

				$pagemounts = array_unique($pagemounts);

				// Check each page
				foreach ($ids as $id)
				{
					if (!in_array($id, $pagemounts))
					{
						throw new Contao\CoreBundle\Exception\AccessDeniedException('Page ID ' . $id . ' is not mounted.');
					}

					$objPage = $this->Database->prepare("SELECT * FROM tl_page WHERE id=?")
											  ->limit(1)
											  ->execute($id);

					// Check whether the current user has permission for the current page
					if ($objPage->numRows && !$this->User->isAllowed($permission, $objPage->row()))
					{
						throw new Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to ' . Input::get('act') . ' ' . (strlen(Input::get('id')) ? 'article ID ' . Input::get('id') : ' articles') . ' on page ID ' . $id . ' or to paste it/them into page ID ' . $id . '.');
					}
				}
			}
		}
	}



	/**
	 * Add an image to each page in the tree
	 *
	 * @param array  $row
	 * @param string $label
	 *
	 * @return string
	 */
	public function renderPost($arrRow)
	{
		dump($arrRow);
		
		$label = $arrRow['title'];
		$image = 'articles';

		$time = \Date::floorToMinute();
		$unpublished = $arrRow['start'] != '' && $arrRow['start'] > $time || $arrRow['stop'] != '' && $arrRow['stop'] < $time;
	dump($unpublished);	
		if (!$arrRow['published'] || $unpublished)
		{
			$image .= '_';
		}
	dump($image);	

		$return = '<div class="tl_content_left"><div class="list_icon" style="background-image: url(\'system/themes/' . Backend::getTheme() . '/icons/' . $image. '.svg\')" data-icon="' . ($unpublished ? $image : rtrim($image, '_')) . '.svg" data-icon-disabled="' . rtrim($image, '_').'_.svg">';
		
		$return .= $label;
		
		$return .= "</div></div>";
		
		return $return;
		
		return '<a href="contao/main.php?do=feRedirect&amp;article='.($arrRow['alias'] ?: $arrRow['id']).'" title="'.StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['view']).'" target="_blank">'.Image::getHtml($image.'.svg', '', 'class="data-icon="'.($unpublished ? $image : rtrim($image, '_')).'.svg" data-icon-disabled="'.rtrim($image, '_').'_.svg"').'</a> '.$label;
	}

	
	/**
	 * Auto-generate an article alias if it has not been set yet
	 *
	 * @param mixed         $varValue
	 * @param DataContainer $dc
	 *
	 * @return string
	 *
	 * @throws Exception
	 */
	public function generateAlias($varValue, DataContainer $dc)
	{
		$autoAlias = false;
		
		// Generate an alias if there is none
		if ($varValue == '')
		{
			$autoAlias = true;
			$varValue = StringUtil::generateAlias($dc->activeRecord->title);
		}
		
		// Add a prefix to reserved names (see #6066)
		if (in_array($varValue, array('top', 'wrapper', 'header', 'container', 'main', 'left', 'right', 'footer')))
		{
			$varValue = 'article-' . $varValue;
		}
		
		$objAlias = $this->Database->prepare("SELECT id FROM tl_posts WHERE id=? OR alias=?")
								   ->execute($dc->id, $varValue);
								   
		// Check whether the page alias exists
		if ($objAlias->numRows > 1)
		{
			if (!$autoAlias)
			{
				throw new Exception(sprintf($GLOBALS['TL_LANG']['ERR']['aliasExists'], $varValue));
			}
			$varValue .= '-' . $dc->id;
		}
		
		return $varValue;
	}


	/**
	 * Adjust start end end time of the event based on date, span, startTime and endTime
	 *
	 * @param DataContainer $dc
	 */
	public function adjustTime(DataContainer $dc)
	{
		// Return if there is no active record (override all)
		if (!$dc->activeRecord)
		{
			return;
		}
		
		$arrSet['date'] = strtotime(date('Y-m-d', $dc->activeRecord->date) . ' ' . date('H:i:s', $dc->activeRecord->time));
		$arrSet['time'] = $arrSet['date'];
		
		$this->Database->prepare("UPDATE tl_posts %s WHERE id=?")->set($arrSet)->execute($dc->id);
	}


	/**
	 * Return all active layout sections as array
	 *
	 * @param DataContainer $dc
	 *
	 * @return array
	 */
	public function getActiveLayoutSections(DataContainer $dc)
	{
		// Show only active sections
		if ($dc->activeRecord->pid)
		{
			$arrSections = array();
			$objPage = PageModel::findWithDetails($dc->activeRecord->pid);

			// Get the layout sections
			foreach (array('layout', 'mobileLayout') as $key)
			{
				if (!$objPage->$key)
				{
					continue;
				}

				$objLayout = LayoutModel::findByPk($objPage->$key);

				if ($objLayout === null)
				{
					continue;
				}

				$arrModules = \StringUtil::deserialize($objLayout->modules);

				if (empty($arrModules) || !is_array($arrModules))
				{
					continue;
				}

				$articleModules = array('0');
				
				if (($objArticleModules = ModuleModel::findBy(array("tl_module.type IN('articles','teasers','articlereader')"), null)) !== null)
				{
					$articleModules = array_merge($articleModules, $objArticleModules->fetchEach('id'));
				}	
	
				// Find all sections with an article module (see #6094)
				foreach ($arrModules as $arrModule)
				{
					if (in_array($arrModule['mod'], $articleModules) && $arrModule['enable'])
					{
						$arrSections[] = $arrModule['col'];
					}
				}
			}
		}

		// Show all sections (e.g. "override all" mode)
		else
		{
			$arrSections = array('header', 'left', 'right', 'main', 'footer');
			$objLayout = $this->Database->query("SELECT sections FROM tl_layout WHERE sections!=''");

			while ($objLayout->next())
			{
				$arrCustom = \StringUtil::deserialize($objLayout->sections);

				// Add the custom layout sections
				if (!empty($arrCustom) && is_array($arrCustom))
				{
					foreach ($arrCustom as $v)
					{
						if (!empty($v['id']))
						{
							$arrSections[] = $v['id'];
						}
					}
				}
			}
		}

		return Backend::convertLayoutSectionIdsToAssociativeArray($arrSections);
	}

	
	/**
	 * Return the "feature/unfeature element" button
	 *
	 * @param array  $row
	 * @param string $href
	 * @param string $label
	 * @param string $title
	 * @param string $icon
	 * @param string $attributes
	 *
	 * @return string
	 */
	public function iconFeatured($row, $href, $label, $title, $icon, $attributes)
	{
		if (strlen(Input::get('fid')))
		{
			$this->toggleFeatured(Input::get('fid'), (Input::get('state') == 1), (@func_get_arg(12) ?: null));
			$this->redirect($this->getReferer());
		}
		
		// Check permissions AFTER checking the fid, so hacking attempts are logged
		if (!$this->User->hasAccess('tl_posts::featured', 'alexf'))
		{
			return '';
		}
		
		$href .= '&amp;fid='.$row['id'].'&amp;state='.($row['featured'] ? '' : 1);
		
		if (!$row['featured'])
		{
			$icon = 'featured_.svg';
		}
		
		return '<a href="'.$this->addToUrl($href).'" title="'.StringUtil::specialchars($title).'"'.$attributes.'>'.Image::getHtml($icon, $label, 'data-state="' . ($row['featured'] ? 1 : 0) . '"').'</a> ';
	}

	
	/**
	 * Feature/unfeature an article
	 *
	 * @param integer       $intId
	 * @param boolean       $blnVisible
	 * @param DataContainer $dc
	 *
	 * @return string
	 *
	 * @throws Contao\CoreBundle\Exception\AccessDeniedException
	 */
	public function toggleFeatured($intId, $blnVisible, DataContainer $dc=null)
	{
		// Check permissions to edit
		Input::setGet('id', $intId);
		Input::setGet('act', 'feature');

		$this->checkPermission();
		
		// Check permissions to feature
		if (!$this->User->hasAccess('tl_posts::featured', 'alexf'))
		{
			throw new Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to feature/unfeature article ID ' . $intId . '.');
		}
		
		$objVersions = new Versions('tl_posts', $intId);
		$objVersions->initialize();
		
		// Trigger the save_callback
		if (is_array($GLOBALS['TL_DCA']['tl_posts']['fields']['featured']['save_callback']))
		{
			foreach ($GLOBALS['TL_DCA']['tl_posts']['fields']['featured']['save_callback'] as $callback)
			{
				if (is_array($callback))
				{
					$this->import($callback[0]);
					$blnVisible = $this->{$callback[0]}->{$callback[1]}($blnVisible, ($dc ?: $this));
				}
				elseif (is_callable($callback))
				{
					$blnVisible = $callback($blnVisible, $this);
				}
			}
		}
		
		// Update the database
		$this->Database->prepare("UPDATE tl_posts SET tstamp=". time() .", featured='" . ($blnVisible ? 1 : '') . "' WHERE id=?")
					   ->execute($intId);
					   
		$objVersions->create();
	}

	
	/**
	 * HOOK executePostActions
	 *
	 * @param string        $strAction
	 * @param DataContainer $dc
	 */
	public function toggleFeaturedPost($strAction, DataContainer $dc)
	{
		if ($strAction == 'toggleFeaturedPost')
		{
			
			$this->toggleFeatured(\Input::post('id'), ((\Input::post('state') == 1) ? true : false));
		}
		
	}

	
	/**
	 * Return the article categories
	 *
	 * @param DataContainer $dc
	 *
	 * @return array
	 */
	public function getPostCategories(DataContainer $dc)
	{
	//	$objParents = \PageModel::findParentsById($dc->activeRecord->pid);
		
		if ($objParents !== null)
		{
			$intRootId = \PageModel::findParentsById($dc->activeRecord->pid)->last()->id;
		}
		else
		{
			$intRootId = 0;
		}
		

		$objArticles = \ArticleModel::findAll();

		$arrCat = array();
		
		foreach ($objArticles as $objArticle)
		{
			// Don't show categories from other archives
			if (\ArchiveModel::findById($objArticle->pid)->id != $intRootId && $intRootId !== 0)
			{
				continue;
			}
			
			if (is_array($category = \StringUtil::deserialize($objArticle->category)))
			{
				foreach ($category as $val)
				{
					if ($val !== '')
					{
						$arrCat[$val] = $val;
					}
				}
			}
			else
			{
				if ($objArticle->category !== '')
				{
					$arrCat[$objArticle->category] = $objArticle->category;
				}
			}
		}

		return $arrCat;
	}
	
	
	/**
	 * Return the article formats
	 *
	 * @param DataContainer $dc
	 *
	 * @return array
	 */
	public function getPostFormats(DataContainer $dc)
	{
		return \System::getContainer()->getParameter('contao.article.formats');
	}

	/**
	 * Return all post templates as array
	 *
	 * @return array
	 */
	public function getPostTemplates()
	{
		return $this->getTemplateGroup('mod_post');
	}

	
	/**
	 * Return the paste article button
	 *
	 * @param DataContainer $dc
	 * @param array         $row
	 * @param string        $table
	 * @param boolean       $cr
	 * @param array         $arrClipboard
	 *
	 * @return string
	 */
	public function pastePost(DataContainer $dc, $row, $table, $cr, $arrClipboard=null)
	{
		/** @var AttributeBagInterface $objSessionBag */
		$objSessionBag = \System::getContainer()->get('session')->getBag('contao_backend');
		$session = $objSessionBag->all();

		$imagePasteAfter = Image::getHtml('pasteafter.svg', sprintf($GLOBALS['TL_LANG'][$dc->table]['pasteafter'][1], $row['id']));
		$imagePasteInto = Image::getHtml('pasteinto.svg', sprintf($GLOBALS['TL_LANG'][$dc->table]['pasteinto'][1], $row['id']));
		
		if ($table == $GLOBALS['TL_DCA'][$dc->table]['config']['ptable'])
		{
			return ($row['type'] == 'root' || !$this->User->isAllowed(BackendUser::CAN_EDIT_ARTICLE_HIERARCHY, $row) || $cr) ? Image::getHtml('pasteinto_.svg').' ' : '<a href="'.$this->addToUrl('act='.$arrClipboard['mode'].'&amp;mode=2&amp;pid='.$row['id'].(!is_array($arrClipboard['id']) ? '&amp;id='.$arrClipboard['id'] : '')).'" title="'.StringUtil::specialchars(sprintf($GLOBALS['TL_LANG'][$dc->table]['pasteinto'][1], $row['id'])).'" onclick="Backend.getScrollOffset()">'.$imagePasteInto.'</a> ';
		}

/*		$objPage = \PageModel::findById($row['pid']);
		$objArticle = \ArticleModel::findById($arrClipboard['id']);
		
		if ($objPage->extArticles || ($objArticle->inColumn != $row['inColumn'] && \Input::get('mode') == 'cut'))
		{
*/			if ((isset($session['sorting'][$table]) && $session['sorting'][$table] != 'sorting') || (!isset($session['sorting'][$table]) && $GLOBALS['TL_DCA']['tl_posts']['list']['sorting']['fields'][0] != 'sorting'))
			{
				return Image::getHtml('pasteafter_.svg').' ';
			}
//		}
		
		$objArchiv = \ArchiveModel::findById($row['pid']);
								  
		return (($arrClipboard['mode'] == 'cut' && $arrClipboard['id'] == $row['id']) || ($arrClipboard['mode'] == 'cutAll' && in_array($row['id'], $arrClipboard['id'])) || !$this->User->isAllowed(BackendUser::CAN_EDIT_ARTICLE_HIERARCHY, $objArchiv->row()) || $cr) ? Image::getHtml('pasteafter_.svg').' ' : '<a href="'.$this->addToUrl('act='.$arrClipboard['mode'].'&amp;mode=1&amp;pid='.$row['id'].(!is_array($arrClipboard['id']) ? '&amp;id='.$arrClipboard['id'] : '')).'" title="'.StringUtil::specialchars(sprintf($GLOBALS['TL_LANG'][$dc->table]['pasteafter'][1], $row['id'])).'" onclick="Backend.getScrollOffset()">'.$imagePasteAfter.'</a> ';
	}

	
	
	/**
	 * Return the edit article button
	 *
	 * @param array  $row
	 * @param string $href
	 * @param string $label
	 * @param string $title
	 * @param string $icon
	 * @param string $attributes
	 *
	 * @return string
	 */
	public function editPost($row, $href, $label, $title, $icon, $attributes)
	{
		//$objPage = \PageModel::findById($row['pid']);
		$objArchive = \ArchiveModel::findById($row['pid']);

		return $this->User->isAllowed(BackendUser::CAN_EDIT_ARTICLES, $objArchive->row()) ? '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.StringUtil::specialchars($title).'"'.$attributes.'>'.Image::getHtml($icon, $label).'</a> ' : Image::getHtml(preg_replace('/\.svg$/i', '_.svg', $icon)).' ';
	}


	/**
	 * Return the edit header button
	 *
	 * @param array  $row
	 * @param string $href
	 * @param string $label
	 * @param string $title
	 * @param string $icon
	 * @param string $attributes
	 *
	 * @return string
	 */
	public function editHeader($row, $href, $label, $title, $icon, $attributes)
	{
		if (!$this->User->canEditFieldsOf('tl_posts'))
		{
			return Image::getHtml(preg_replace('/\.svg$/i', '_.svg', $icon)).' ';
		}

		$objArchive = \ArchiveModel::findById($row['pid']);

		return $this->User->isAllowed(BackendUser::CAN_EDIT_ARTICLES, $objArchive->row()) ? '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.StringUtil::specialchars($title).'"'.$attributes.'>'.Image::getHtml($icon, $label).'</a> ' : Image::getHtml(preg_replace('/\.svg$/i', '_.svg', $icon)).' ';
	}


	/**
	 * Return the copy article button
	 *
	 * @param array  $row
	 * @param string $href
	 * @param string $label
	 * @param string $title
	 * @param string $icon
	 * @param string $attributes
	 * @param string $table
	 *
	 * @return string
	 */
	public function copyPost($row, $href, $label, $title, $icon, $attributes, $table)
	{
		if ($GLOBALS['TL_DCA'][$table]['config']['closed'])
		{
			return '';
		}

		$objArchive = \ArchiveModel::findById($row['pid']);

		return $this->User->isAllowed(BackendUser::CAN_EDIT_ARTICLE_HIERARCHY, $objArchive->row()) ? '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.StringUtil::specialchars($title).'"'.$attributes.'>'.Image::getHtml($icon, $label).'</a> ' : Image::getHtml(preg_replace('/\.svg$/i', '_.svg', $icon)).' ';
	}


	/**
	 * Return the cut article button
	 *
	 * @param array  $row
	 * @param string $href
	 * @param string $label
	 * @param string $title
	 * @param string $icon
	 * @param string $attributes
	 *
	 * @return string
	 */
	public function cutPost($row, $href, $label, $title, $icon, $attributes)
	{
		$objArchive = \ArchiveModel::findById($row['pid']);
		
		return $this->User->isAllowed(BackendUser::CAN_EDIT_ARTICLE_HIERARCHY, $objArchive->row()) ? '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.StringUtil::specialchars($title).'"'.$attributes.'>'.Image::getHtml($icon, $label).'</a> ' : Image::getHtml(preg_replace('/\.svg$/i', '_.svg', $icon)).' ';
	}


	/**
	 * Return the delete article button
	 *
	 * @param array  $row
	 * @param string $href
	 * @param string $label
	 * @param string $title
	 * @param string $icon
	 * @param string $attributes
	 *
	 * @return string
	 */
	public function deletePost($row, $href, $label, $title, $icon, $attributes)
	{
		$objArchive = \ArchiveModel::findById($row['pid']);

		return $this->User->isAllowed(BackendUser::CAN_DELETE_ARTICLES, $objArchive->row()) ? '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.StringUtil::specialchars($title).'"'.$attributes.'>'.Image::getHtml($icon, $label).'</a> ' : Image::getHtml(preg_replace('/\.svg$/i', '_.svg', $icon)).' ';
	}

	/**
	 * Return the "toggle visibility" button
	 *
	 * @param array  $row
	 * @param string $href
	 * @param string $label
	 * @param string $title
	 * @param string $icon
	 * @param string $attributes
	 *
	 * @return string
	 */
	public function toggleIcon($row, $href, $label, $title, $icon, $attributes)
	{
		if (strlen(Input::get('tid')))
		{
			$this->toggleVisibility(Input::get('tid'), (Input::get('state') == 1), (@func_get_arg(12) ?: null));
			$this->redirect($this->getReferer());
		}

		// Check permissions AFTER checking the tid, so hacking attempts are logged
		if (!$this->User->hasAccess('tl_posts::published', 'alexf'))
		{
			return '';
		}

		$href .= '&amp;tid='.$row['id'].'&amp;state='.($row['published'] ? '' : 1);

		if (!$row['published'])
		{
			$icon = 'invisible.svg';
		}

		$objArchive = \ArchiveModel::findById($row['pid']);

		if (!$this->User->isAllowed(BackendUser::CAN_EDIT_ARTICLES, $objArchive->row()))
		{
			if ($row['published'])
			{
				$icon = preg_replace('/\.svg$/i', '_.svg', $icon); // see #8126
			}

			return Image::getHtml($icon) . ' ';
		}

		return '<a href="'.$this->addToUrl($href).'" title="'.StringUtil::specialchars($title).'"'.$attributes.'>'.Image::getHtml($icon, $label, 'data-state="' . ($row['published'] ? 1 : 0) . '"').'</a> ';
	}

}
