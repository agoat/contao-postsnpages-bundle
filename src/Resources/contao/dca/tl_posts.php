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
		'dataContainer'               => 'Table',
		'ptable'                      => 'tl_archive',
		'ctable'                      => array('tl_content', 'tl_tags'),
		'switchToEdit'                => true,
		'enableVersioning'            => true,
		'onload_callback' => array
		(
			array('tl_posts', 'checkPermission'),
		),
		'onsubmit_callback' => array
		(
			array('tl_posts', 'adjustTime'),
		),
		'oncut_callback' => array
		(
			array('tl_posts', 'adjustTags'),
		),
		'oncopy_callback' => array
		(
			array('tl_posts', 'adjustTags'),
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
			'cut' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_posts']['cut'],
				'href'                => 'act=paste&amp;mode=cut',
				'icon'                => 'cut.svg',
				'attributes'          => 'onclick="Backend.getScrollOffset()"',
				'button_callback'     => array('tl_posts', 'cutPost')
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
				'attributes'          => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleFeatured(this,%s)"',
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

	// Palettes
	'palettes' => array
	(
		'__selector__'                => array('addImage', 'alternativeLink'),
		'default'                     => '{title_legend},title,alias,author;{layout_legend},keywords;{date_legend},date,time;{location_legend},location,latlong;{teaser_legend},subTitle,teaser;{image_legend},addImage;{category_legend},category,tags;{readmore_legend},alternativeLink;{related_legend},related;{syndication_legend},printable;{template_legend:hide},customTpl;{expert_legend:hide},noComments,featured,format,cssID;{publish_legend},published,start,stop'
	),

	// Subpalettes
	'subpalettes' => array
	(
		'addImage'                   => 'singleSRC,alt,caption',
		'alternativeLink'                   => 'url,target'
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
			'foreignKey'              => 'tl_archive.title',
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
			'flag'                    => 3,
			'length'                  => 3,
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
			'load_callback' => array
			(
				array('tl_posts', 'loadDate')
			),
			'save_callback' => array
			(
				array('tl_posts', 'resetTime')
			),
			'eval'                    => array('rgxp'=>'date', 'doNotCopy'=>true, 'datepicker'=>true, 'tl_class'=>'w50 wizard'),
			'sql'                     => "int(10) unsigned NOT NULL default '0'"
		),
		'time' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_posts']['time'],
			'default'                 => time(),
			'exclude'                 => true,
			'inputType'               => 'text',
			'load_callback' => array
			(
				array('tl_posts', 'loadTime')
			),
			'save_callback' => array
			(
				array('tl_posts', 'resetTime')
			),
			'eval'                    => array('rgxp'=>'time', 'doNotCopy'=>true, 'datepicker'=>true, 'tl_class'=>'w50 wizard'),
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
			'sorting'                 => true,
			'filter'                  => true,
			'inputType'               => 'inputselect',
			'options_callback'        => array('tl_posts', 'getCategories'),
			'eval'                    => array('includeBlankOption'=>true, 'rgxp'=>'alias', 'maxlength'=>128, 'noResult'=>$GLOBALS['TL_LANG']['tl_posts']['choosen_addCategory'], 'tl_class'=>'w50'),
			'sql'                     => "varchar(128) NOT NULL default ''"
		),
		'tags' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_posts']['tags'],
			'exclude'                 => true,
			'filter'                  => true,
			'inputType'               => 'inputselect',
			'save_callback'           => array
			(
				array('tl_posts', 'saveTags')
			),
			'options_callback'        => array('tl_posts', 'getTags'),
			'eval'                    => array('multiple'=>true, 'noResult'=>$GLOBALS['TL_LANG']['tl_posts']['choosen_addTag'], 'tl_class'=>'clr long'),
			'sql'                     => "varchar(1022) NOT NULL default ''"
		),
		'alternativeLink' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_posts']['alternativeLink'],
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
			'eval'                    => array
			(
				'mandatory'			=> true, 
				'rgxp'				=> 'url', 
				'decodeEntities'	=> true, 
				'maxlength'			=> 255, 
				'dcaPicker'			=> true,
				'fieldType'			=> 'radio', 
				'filesOnly'			=> true, 
				'tl_class'			=> 'w50 wizard'
			),
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
		'related' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_posts']['related'],
			'exclude'                 => true,
			'inputType'               => 'postTree',
			'eval'                    => array('multiple'=>	true, 'fieldType'=>'checkbox', 'orderField'=>'orderRelated', 'tl_class'=>'clr'),
			'sql'                     => "blob NULL"
		),
		'orderRelated' => array
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
			'options_callback'        => array('tl_posts', 'getPostsFormats'),
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
						throw new Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to ' . Input::get('act') . ' ' . (strlen(Input::get('id')) ? 'post ID ' . Input::get('id') : ' posts') . ' on page ID ' . $id . ' or to paste it/them into page ID ' . $id . '.');
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
		$time = \Date::floorToMinute();
		$unpublished = !$arrRow['published'] || $arrRow['start'] != '' && $arrRow['start'] > $time || $arrRow['stop'] != '' && $arrRow['stop'] < $time;
	
		$return = '<div class="tl_content_left tl_post">';
		
		// Title
		$return .= '<h2>' . $arrRow['title'] . '</h2>';
		
		// Date | Location | LatLong | Author
		$return .= '<div class="tl_gray">';
		
		$return .= date(\Config::get('dateFormat'), $arrRow['date']);
		
		if ($arrRow['location'])
		{
			$return .= ' | ' . $arrRow['location'];
		}

		if (($arrLatLong = \StringUtil::deserialize($arrRow['latlong']))[0])
		{
			$return .= ' | ' . implode(', ', $arrLatLong );
		}
		
		if (($objUser = \UserModel::findById($arrRow['author'])))
		{
			$return .= ' | ' . $objUser->name;
		}
		
		$return .= '</div>';

		// Image | Subtitle + Teaser
		$return .= '<div class="post_teaser">';

		if ($arrRow['addImage'])
		{

			$objFile = \FilesModel::findByUuid($arrRow['singleSRC']);
						
			if (null !== $objFile)
			{
				$image = \System::getContainer()->get('contao.image.image_factory')->create(TL_ROOT . '/' . $objFile->path, array(180, 120, 'crop'))->getUrl(TL_ROOT);
			}
		
			$return .= '<figure><img src="' . $image . '"></figure>';
		}

		if ($arrRow['subTitle'])
		{
			$return .= '<h3>' . $arrRow['subTitle'] . '</h3>';
		}
		
		if ($arrRow['teaser'])
		{
			$return .= $arrRow['teaser'];
		}

		// Readmore
		$return .= '<p class="post_readmore"><a href="' . \Agoat\PostsnPages\Posts::generatePostUrl(\PostsModel::findById($arrRow['id']), true) . '" target="_blank">Read more</a></p>';

		$return .= '</div>';
		
		// Category | Tags
		if ($arrRow['category'] || $arrRow['tags'])
		{
			$return .= '<div class="tl_gray">';

			if ($arrRow['category'])
			{
				$return .= $GLOBALS['TL_LANG']['tl_posts']['category'][0] . ': ' . $arrRow['category'];
			}

			if ($arrRow['category'] && $arrRow['tags'])
			{
				$return .= ' | ';
			}
			
			if ($arrRow['tags'])
			{
				$objTags = \TagsModel::findMultipleByIds(\StringUtil::deserialize($arrRow['tags']));
				
				if (null !== $objTags)
				{
					$return .= $GLOBALS['TL_LANG']['tl_posts']['tags'][0] . ': ' . implode(', ', $objTags->fetchEach('label'));
				}
			}
			
			$return .= '</div>';
		}
		
		$return .= '</div>';

		return $return;
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
	 * Reduce the tstamp to contain only the date tstamp to be equal to what will returned
	 *
	 * @param string $value
	 *
	 * @return string
	 */
	public function loadDate($value)
	{
		$objDate = new \Date(date('Y-m-d', $value), 'Y-m-d');
		return $objDate->tstamp;
	}
	
	
	/**
	 * Reduce the tstamp to contain only the time tstamp to be equal to what will returned
	 *
	 * @param string $value
	 *
	 * @return string
	 */
	public function loadTime($value)
	{
		$objDate = new \Date(date('H:i:s', $value), 'H:i:s');
		return $objDate->tstamp;
	}
	
	
	/**
	 * Reset the date and/or time to the current time if empty
	 *
	 * @param string $value
	 */
	public function resetTime($value)
	{
		return empty($value) ? time() : $value;
	}


	/**
	 * Adjust date and time to have both containing date and time
	 *
	 * @param DataContainer $dc
	 */
	public function adjustTime($dc)
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
	 * Set the tags archive and tag ids correctly
	 *
	 * @param int           $insertID
	 * @param DataContainer $dc
	 */
	public function adjustTags($insertID, DataContainer $dc=null)
	{
		// Handle oncut_callback as well as oncopy_callback
		if (null === $dc)
		{
			$dc = $insertID;
			$insertID = $dc->id;
		}
		
		$arrSet['published'] = 0;
		$arrSet['archive'] = $this->Database->prepare("SELECT pid FROM tl_posts WHERE id=?")->execute($insertID)->pid;
		
		$this->Database->prepare("UPDATE tl_tags %s WHERE pid=?")->set($arrSet)->execute($insertID);
		
		$this->Database->prepare("UPDATE tl_posts SET tags=? WHERE id=?")->execute(serialize($this->Database->prepare("SELECT id FROM tl_tags WHERE pid=?")->execute($insertID)->fetchEach('id')), $insertID);
	}


	/**
	 * Return the article categories
	 *
	 * @param DataContainer $dc
	 *
	 * @return array
	 */
	public function getCategories(DataContainer $dc)
	{
		$intPid = (null === $dc->activeRecord) ? $dc->id : $dc->activeRecord->pid;
		
		$objPosts = \PostsModel::findByPid($intPid);

		if ($objPosts === null)
		{
			return array();
		}

		$arrCategories = array_unique($objPosts->fetchEach('category'));

		return array_combine($arrCategories, $arrCategories);
	}
	
	
	/**
	 * Return the article categories
	 *
	 * @param DataContainer $dc
	 *
	 * @return array
	 */
	public function getTags(DataContainer $dc)
	{
		$intPid = (null === $dc->activeRecord) ? $dc->id : $dc->activeRecord->pid;

		$objTags = \TagsModel::findByArchive($intPid);

		if (null === $objTags)
		{
			return array();
		}
			
		$arrTags = array_unique($objTags->fetchEach('label'));

		if (null !== $dc->activeRecord)
		{
			$objTags = \TagsModel::findByPid($dc->activeRecord->id);

			if (null !== $objTags)
			{
				$arrTags = array_unique($objTags->fetchEach('label') + $arrTags);
			}
		}

		return $arrTags;
	}

	
	
	/**
	 * Return the article categories
	 *
	 * @param DataContainer $dc
	 *
	 * @return array
	 */
	public function saveTags($value, $dc)
	{
		if (empty($value))
		{
			return;
		}
		
		$blnChanged = false;
		$tags = \StringUtil::deserialize($value);
	
		$tagIds = array_filter($tags, 'is_numeric');
		$newTags = (is_array($tagIds)) ? array_diff_key($tags, $tagIds) : array();

		$objTags = \TagsModel::findByPid($dc->activeRecord->id);
		
		$removedTags = (null !== $objTags) ? array_diff($objTags->fetchEach('id'), $tagIds) : array();
		$selectedTags = (null !== $objTags) ? array_diff($tagIds, $objTags->fetchEach('id')) : $tagIds;
	
		if (!empty($removedTags))
		{
			$this->Database->query("DELETE FROM tl_tags WHERE id IN ('" . implode("','", $removedTags) . "')");
		}
	
		if (!empty($newTags))
		{
			foreach ($newTags as $k=>$v)
			{
				if (!empty($v))
				{
					$tag = new \TagsModel();
					$tag->tstamp = time();
					$tag->label = $v;
					$tag->pid = $dc->activeRecord->id;
					$tag->archive = $dc->activeRecord->pid;
					$tag->published = $dc->activeRecord->published;
					
					$tag->save();
					
					$tags[$k] = $tag->id;
					$blnChanged = true;
				}
				else
				{
					unset($tags[$k]);
				}
			}
		}
		
		if (!empty($selectedTags))
		{
			foreach ($selectedTags as $k=>$v)
			{
				$objValue = \TagsModel::findById($v);
			
				if (null !== $objValue)
				{
					$tag = new \TagsModel();
					$tag->tstamp = time();
					$tag->label = $objValue->label;
					$tag->pid = $dc->activeRecord->id;
					$tag->archive = $dc->activeRecord->pid;
					$tag->published = $dc->activeRecord->published;
					
					$tag->save();
					
					$tags[$k] = $tag->id;
					$blnChanged = true;
				}
			}
		}

		return ($blnChanged) ? serialize($tags) : $value;
	}
	
	
	/**
	 * Return the article formats
	 *
	 * @param DataContainer $dc
	 *
	 * @return array
	 */
	public function getPostsFormats(DataContainer $dc)
	{
		return \System::getContainer()->getParameter('contao.posts.formats');
	}

	
	/**
	 * Return all post templates as array
	 *
	 * @return array
	 */
	public function getPostTemplates()
	{
		return $this->getTemplateGroup('post_');
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

	/**
	 * Disable/enable a user group
	 *
	 * @param integer       $intId
	 * @param boolean       $blnVisible
	 * @param DataContainer $dc
	 *
	 * @throws Contao\CoreBundle\Exception\AccessDeniedException
	 */
	public function toggleVisibility($intId, $blnVisible, DataContainer $dc=null)
	{
		// Set the ID and action
		Input::setGet('id', $intId);
		Input::setGet('act', 'toggle');
		
		if ($dc)
		{
			$dc->id = $intId; // see #8043
		}
		
		// Trigger the onload_callback
		if (is_array($GLOBALS['TL_DCA']['tl_posts']['config']['onload_callback']))
		{
			foreach ($GLOBALS['TL_DCA']['tl_posts']['config']['onload_callback'] as $callback)
			{
				if (is_array($callback))
				{
					$this->import($callback[0]);
					$this->{$callback[0]}->{$callback[1]}($dc);
				}
				elseif (is_callable($callback))
				{
					$callback($dc);
				}
			}
		}
		
		// Check the field access
		if (!$this->User->hasAccess('tl_posts::published', 'alexf'))
		{
			throw new Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to publish/unpublish article ID "' . $intId . '".');
		}
		
		// Set the current record
		if ($dc)
		{
			$objRow = $this->Database->prepare("SELECT * FROM tl_posts WHERE id=?")
									 ->limit(1)
									 ->execute($intId);
			if ($objRow->numRows)
			{
				$dc->activeRecord = $objRow;
			}
		}
		
		$objVersions = new Versions('tl_posts', $intId);
		$objVersions->initialize();
		
		// Trigger the save_callback
		if (is_array($GLOBALS['TL_DCA']['tl_posts']['fields']['published']['save_callback']))
		{
			foreach ($GLOBALS['TL_DCA']['tl_posts']['fields']['published']['save_callback'] as $callback)
			{
				if (is_array($callback))
				{
					$this->import($callback[0]);
					$blnVisible = $this->{$callback[0]}->{$callback[1]}($blnVisible, $dc);
				}
				elseif (is_callable($callback))
				{
					$blnVisible = $callback($blnVisible, $dc);
				}
			}
		}
		
		$time = time();
		
		// Update the database
		$this->Database->prepare("UPDATE tl_posts SET tstamp=$time, published='" . ($blnVisible ? '1' : '') . "' WHERE id=?")
					   ->execute($intId);
					   
		if ($dc)
		{
			$dc->activeRecord->tstamp = $time;
			$dc->activeRecord->published = ($blnVisible ? '1' : '');
		}
		
		// Change the publish state in the tl_tags table
		$this->Database->prepare("UPDATE tl_tags SET published='" . ($blnVisible ? '1' : '') . "' WHERE pid=?")
					   ->execute($intId);
		
		
		// Trigger the onsubmit_callback
		if (is_array($GLOBALS['TL_DCA']['tl_posts']['config']['onsubmit_callback']))
		{
			foreach ($GLOBALS['TL_DCA']['tl_posts']['config']['onsubmit_callback'] as $callback)
			{
				if (is_array($callback))
				{
					$this->import($callback[0]);
					$this->{$callback[0]}->{$callback[1]}($dc);
				}
				elseif (is_callable($callback))
				{
					$callback($dc);
				}
			}
		}
		
		$objVersions->create();
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

}
