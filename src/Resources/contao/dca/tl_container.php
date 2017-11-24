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


/**
 * Load tl_page data container
 */
$this->loadDataContainer('tl_page');


/**
 * Table tl_container
 */
$GLOBALS['TL_DCA']['tl_container'] = array
(

	// Config
	'config' => array
	(
		'dataContainer'               => 'TableExtended',
		'ptable'                      => 'tl_page',
		'ctable'                      => array('tl_content'),
		'switchToEdit'                => true,
		'enableVersioning'            => true,
		'onload_callback' => array
		(
			array('tl_container', 'checkPermission'),
			array('tl_container', 'addCustomLayoutSectionReferences'),
			array('tl_page', 'addBreadcrumb')
		),
		'sql' => array
		(
			'keys' => array
			(
				'id' => 'primary',
				'pid,start,stop,published,sorting' => 'index'
			)
		)
	),

	// List
	'list' => array
	(
		'sorting' => array
		(
			'mode'                    => 6,
			'paste_button_callback'   => array('tl_container', 'pasteContainer'),
			'panelLayout'             => 'filter;search',
			'pfilter'				  => array("type IN ('regular','error_403','error_404')"),
			'group'					  => 'inColumn'
		),
		'label' => array
		(
			'fields'                  => array('title'),
			'label_callback'          => array('tl_container', 'addIcon')
		),
		'global_operations' => array
		(
			'toggleNodes' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['MSC']['toggleAll'],
				'href'                => '&amp;ptg=all',
				'class'               => 'header_toggle',
				'showOnSelect'        => true
			),
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
				'label'               => &$GLOBALS['TL_LANG']['tl_container']['edit'],
				'href'                => 'table=tl_content',
				'icon'                => 'edit.svg',
				'button_callback'     => array('tl_container', 'editContainer')
			),
			'editheader' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_container']['editheader'],
				'href'                => 'act=edit',
				'icon'                => 'header.svg',
				'button_callback'     => array('tl_container', 'editHeader')
			),
			'copy' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_container']['copy'],
				'href'                => 'act=paste&amp;mode=copy',
				'icon'                => 'copy.svg',
				'attributes'          => 'onclick="Backend.getScrollOffset()"',
				'button_callback'     => array('tl_container', 'copyContainer')
			),
			'cut' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_container']['cut'],
				'href'                => 'act=paste&amp;mode=cut',
				'icon'                => 'cut.svg',
				'attributes'          => 'onclick="Backend.getScrollOffset()"',
				'button_callback'     => array('tl_container', 'cutContainer')
			),
			'delete' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_container']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.svg',
				'attributes'          => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"',
				'button_callback'     => array('tl_container', 'deleteContainer')
			),
			'toggle' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_container']['toggle'],
				'icon'                => 'visible.svg',
				'attributes'          => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
				'button_callback'     => array('tl_container', 'toggleIcon')
			),
			'show' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_container']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.svg'
			)
		)
	),

	// Palettes
	'palettes' => array
	(
		'__selector__'                => array('protected'),
		'default'                     => '{title_legend},title;{layout_legend},inColumn,keywords;{template_legend:hide},customTpl,noMarkup;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID;{publish_legend},published,start,stop'
	),

	// Subpalettes
	'subpalettes' => array
	(
		'protected'                   => 'groups'
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
		'title' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_container']['title'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'search'                  => true,
			'eval'                    => array('mandatory'=>true, 'decodeEntities'=>true, 'maxlength'=>255, 'tl_class'=>'w50'),
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'inColumn' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_container']['inColumn'],
			'exclude'                 => true,
			'filter'                  => true,
			'default'                 => 'main',
			'inputType'               => 'select',
			'options_callback'        => array('tl_container', 'getActiveLayoutSections'),
			'eval'                    => array('tl_class'=>'w50'),
			'reference'               => &$GLOBALS['TL_LANG']['COLS'],
			'sql'                     => "varchar(32) NOT NULL default ''"
		),
		'keywords' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_container']['keywords'],
			'exclude'                 => true,
			'inputType'               => 'textarea',
			'search'                  => true,
			'eval'                    => array('style'=>'height:60px', 'decodeEntities'=>true, 'tl_class'=>'clr'),
			'sql'                     => "text NULL"
		),
		'customTpl' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_container']['customTpl'],
			'exclude'                 => true,
			'inputType'               => 'select',
			'options_callback'        => array('tl_container', 'getContainerTemplates'),
			'eval'                    => array('includeBlankOption'=>true, 'chosen'=>true, 'tl_class'=>'w50'),
			'sql'                     => "varchar(64) NOT NULL default ''"
		),
		'noMarkup' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_container']['noMarkup'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'eval'                    => array('tl_class'=>'w50 m12'),
			'sql'                     => "char(1) NOT NULL default ''"
		),
		'protected' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_container']['protected'],
			'exclude'                 => true,
			'filter'                  => true,
			'inputType'               => 'checkbox',
			'eval'                    => array('submitOnChange'=>true),
			'sql'                     => "char(1) NOT NULL default ''"
		),
		'groups' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_container']['groups'],
			'exclude'                 => true,
			'filter'                  => true,
			'inputType'               => 'checkbox',
			'foreignKey'              => 'tl_member_group.name',
			'eval'                    => array('mandatory'=>true, 'multiple'=>true),
			'sql'                     => "blob NULL",
			'relation'                => array('type'=>'hasMany', 'load'=>'lazy')
		),
		'guests' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_container']['guests'],
			'exclude'                 => true,
			'filter'                  => true,
			'inputType'               => 'checkbox',
			'eval'                    => array('tl_class'=>'w50'),
			'sql'                     => "char(1) NOT NULL default ''"
		),
		'cssID' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_container']['cssID'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('multiple'=>true, 'size'=>2, 'tl_class'=>'w50 clr'),
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'published' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_container']['published'],
			'exclude'                 => true,
			'filter'                  => true,
			'inputType'               => 'checkbox',
			'eval'                    => array('doNotCopy'=>true),
			'sql'                     => "char(1) NOT NULL default ''"
		),
		'start' => array
		(
			'exclude'                 => true,
			'label'                   => &$GLOBALS['TL_LANG']['tl_container']['start'],
			'inputType'               => 'text',
			'eval'                    => array('rgxp'=>'datim', 'datepicker'=>true, 'tl_class'=>'w50 wizard'),
			'sql'                     => "varchar(10) NOT NULL default ''"
		),
		'stop' => array
		(
			'exclude'                 => true,
			'label'                   => &$GLOBALS['TL_LANG']['tl_container']['stop'],
			'inputType'               => 'text',
			'eval'                    => array('rgxp'=>'datim', 'datepicker'=>true, 'tl_class'=>'w50 wizard'),
			'sql'                     => "varchar(10) NOT NULL default ''"
		)
	)
);


/**
 * Provide miscellaneous methods that are used by the data configuration array.
 */
class tl_container extends Backend
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
				$objArticle = $this->Database->prepare("SELECT p.pid, p.includeChmod, p.chmod, p.cuser, p.cgroup FROM tl_container a, tl_page p WHERE a.id=? AND a.pid=p.id")
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
		if (isset($session['CLIPBOARD']['tl_container']) && is_array($session['CLIPBOARD']['tl_container']['id']))
		{
			$clipboard = array();

			foreach ($session['CLIPBOARD']['tl_container']['id'] as $id)
			{
				$objArticle = $this->Database->prepare("SELECT p.pid, p.includeChmod, p.chmod, p.cuser, p.cgroup FROM tl_container a, tl_page p WHERE a.id=? AND a.pid=p.id")
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

			$session['CLIPBOARD']['tl_container']['id'] = $clipboard;
		}

		$permission = 0;

		// Overwrite the session
		$objSession->replace($session);

		// Check current action
		if (Input::get('act') && Input::get('act') != 'paste')
		{
			// Set ID of the article's page
			$objPage = $this->Database->prepare("SELECT pid FROM tl_container WHERE id=?")
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
						$objParent = $this->Database->prepare("SELECT id, type FROM tl_page WHERE id=(SELECT pid FROM tl_container WHERE id=?)")
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
	public function addIcon($row, $label)
	{
		$image = 'articles';
		$time = \Date::floorToMinute();

		$unpublished = $row['start'] != '' && $row['start'] > $time || $row['stop'] != '' && $row['stop'] < $time;

		if (!$row['published'] || $unpublished)
		{
			$image .= '_';
		}

		return '<a>'.Image::getHtml($image.'.svg', '', 'data-icon="'.($unpublished ? $image : rtrim($image, '_')).'.svg" data-icon-disabled="'.rtrim($image, '_').'_.svg"').'</a> '.$label;
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

		$objAlias = $this->Database->prepare("SELECT id FROM tl_container WHERE id=? OR alias=?")
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

				$arrModules = StringUtil::deserialize($objLayout->modules);

				if (empty($arrModules) || !is_array($arrModules))
				{
					continue;
				}

				// Find all sections with an article module (see #6094)
				foreach ($arrModules as $arrModule)
				{
					if ($arrModule['mod'] == 0 && $arrModule['enable'])
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
				$arrCustom = StringUtil::deserialize($objLayout->sections);

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
	 * Return all module templates as array
	 *
	 * @return array
	 */
	public function getContainerTemplates()
	{
		return $this->getTemplateGroup('mod_container');
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
	public function editContainer($row, $href, $label, $title, $icon, $attributes)
	{
		$objPage = \PageModel::findById($row['pid']);

		return $this->User->isAllowed(BackendUser::CAN_EDIT_ARTICLES, $objPage->row()) ? '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.StringUtil::specialchars($title).'"'.$attributes.'>'.Image::getHtml($icon, $label).'</a> ' : Image::getHtml(preg_replace('/\.svg$/i', '_.svg', $icon)).' ';
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
		if (!$this->User->canEditFieldsOf('tl_container'))
		{
			return Image::getHtml(preg_replace('/\.svg$/i', '_.svg', $icon)).' ';
		}

		$objPage = \PageModel::findById($row['pid']);

		return $this->User->isAllowed(BackendUser::CAN_EDIT_ARTICLES, $objPage->row()) ? '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.StringUtil::specialchars($title).'"'.$attributes.'>'.Image::getHtml($icon, $label).'</a> ' : Image::getHtml(preg_replace('/\.svg$/i', '_.svg', $icon)).' ';
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
	public function copyContainer($row, $href, $label, $title, $icon, $attributes, $table)
	{
		if ($GLOBALS['TL_DCA'][$table]['config']['closed'])
		{
			return '';
		}

		$objPage = \PageModel::findById($row['pid']);

		return $this->User->isAllowed(BackendUser::CAN_EDIT_ARTICLE_HIERARCHY, $objPage->row()) ? '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.StringUtil::specialchars($title).'"'.$attributes.'>'.Image::getHtml($icon, $label).'</a> ' : Image::getHtml(preg_replace('/\.svg$/i', '_.svg', $icon)).' ';
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
	public function cutContainer($row, $href, $label, $title, $icon, $attributes)
	{
		$objPage = \PageModel::findById($row['pid']);

		return $this->User->isAllowed(BackendUser::CAN_EDIT_ARTICLE_HIERARCHY, $objPage->row()) ? '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.StringUtil::specialchars($title).'"'.$attributes.'>'.Image::getHtml($icon, $label).'</a> ' : Image::getHtml(preg_replace('/\.svg$/i', '_.svg', $icon)).' ';
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
	public function pasteContainer(DataContainer $dc, $row, $table, $cr, $arrClipboard=null)
	{
		$imagePasteAfter = Image::getHtml('pasteafter.svg', sprintf($GLOBALS['TL_LANG'][$dc->table]['pasteafter'][1], $row['id']));
		$imagePasteInto = Image::getHtml('pasteinto.svg', sprintf($GLOBALS['TL_LANG'][$dc->table]['pasteinto'][1], $row['id']));

		if ($table == $GLOBALS['TL_DCA'][$dc->table]['config']['ptable'])
		{
			if (
				!in_array($row['type'], array('regular', 'error_403', 'error_404')) || 
				!$this->User->isAllowed(BackendUser::CAN_EDIT_ARTICLE_HIERARCHY, $row) ||
				$cr
			)
			{
				return Image::getHtml('pasteinto_.svg').' ';
			}
			else
			{
				return '<a href="'.$this->addToUrl('act='.$arrClipboard['mode'].'&amp;mode=2&amp;pid='.$row['id'].(!is_array($arrClipboard['id']) ? '&amp;id='.$arrClipboard['id'] : '')).'" title="'.StringUtil::specialchars(sprintf($GLOBALS['TL_LANG'][$dc->table]['pasteinto'][1], $row['id'])).'" onclick="Backend.getScrollOffset()">'.$imagePasteInto.'</a> ';
			}
		}

		$objPage = \PageModel::findById($row['pid']);
		
		$objContainer = \ContainerModel::findById($arrClipboard['id']);
		
		if (
			($arrClipboard['mode'] == 'cut' && $arrClipboard['id'] == $row['id']) ||
			($arrClipboard['mode'] == 'cutAll' && in_array($row['id'], $arrClipboard['id'])) || 
			!$this->User->isAllowed(BackendUser::CAN_EDIT_ARTICLE_HIERARCHY, $objPage->row()) || 
			$cr||
			$row[$GLOBALS['TL_DCA'][$dc->table]['list']['sorting']['group']] != $objContainer->{$GLOBALS['TL_DCA'][$dc->table]['list']['sorting']['group']}
		)
		{
			return Image::getHtml('pasteafter_.svg').' ';
		}
		else
		{
			return '<a href="'.$this->addToUrl('act='.$arrClipboard['mode'].'&amp;mode=1&amp;pid='.$row['id'].(!is_array($arrClipboard['id']) ? '&amp;id='.$arrClipboard['id'] : '')).'" title="'.StringUtil::specialchars(sprintf($GLOBALS['TL_LANG'][$dc->table]['pasteafter'][1], $row['id'])).'" onclick="Backend.getScrollOffset()">'.$imagePasteAfter.'</a> ';
		}
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
	public function deleteContainer($row, $href, $label, $title, $icon, $attributes)
	{
		$objPage = \PageModel::findById($row['pid']);

		return $this->User->isAllowed(BackendUser::CAN_DELETE_ARTICLES, $objPage->row()) ? '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.StringUtil::specialchars($title).'"'.$attributes.'>'.Image::getHtml($icon, $label).'</a> ' : Image::getHtml(preg_replace('/\.svg$/i', '_.svg', $icon)).' ';
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
		if (!$this->User->hasAccess('tl_container::published', 'alexf'))
		{
			return '';
		}

		$href .= '&amp;tid='.$row['id'].'&amp;state='.($row['published'] ? '' : 1);

		if (!$row['published'])
		{
			$icon = 'invisible.svg';
		}

		$objPage = \PageModel::findById($row['pid']);

		if (!$this->User->isAllowed(BackendUser::CAN_EDIT_ARTICLES, $objPage->row()))
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
		if (is_array($GLOBALS['TL_DCA']['tl_container']['config']['onload_callback']))
		{
			foreach ($GLOBALS['TL_DCA']['tl_container']['config']['onload_callback'] as $callback)
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
		if (!$this->User->hasAccess('tl_container::published', 'alexf'))
		{
			throw new Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to publish/unpublish article ID "' . $intId . '".');
		}

		// Set the current record
		if ($dc)
		{
			$objRow = $this->Database->prepare("SELECT * FROM tl_container WHERE id=?")
									 ->limit(1)
									 ->execute($intId);

			if ($objRow->numRows)
			{
				$dc->activeRecord = $objRow;
			}
		}

		$objVersions = new Versions('tl_container', $intId);
		$objVersions->initialize();

		// Trigger the save_callback
		if (is_array($GLOBALS['TL_DCA']['tl_container']['fields']['published']['save_callback']))
		{
			foreach ($GLOBALS['TL_DCA']['tl_container']['fields']['published']['save_callback'] as $callback)
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
		$this->Database->prepare("UPDATE tl_container SET tstamp=$time, published='" . ($blnVisible ? '1' : '') . "' WHERE id=?")
					   ->execute($intId);

		if ($dc)
		{
			$dc->activeRecord->time = $time;
			$dc->activeRecord->published = ($blnVisible ? '1' : '');
		}

		// Trigger the onsubmit_callback
		if (is_array($GLOBALS['TL_DCA']['tl_container']['config']['onsubmit_callback']))
		{
			foreach ($GLOBALS['TL_DCA']['tl_container']['config']['onsubmit_callback'] as $callback)
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
}
