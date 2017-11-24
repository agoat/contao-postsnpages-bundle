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
 * Table tl_tags
 */
$GLOBALS['TL_DCA']['tl_tags'] = array
(
	// Config
	'config' => array
	(
		'dataContainer'               => 'Table',
		'ptable'                      => 'tl_posts',

		'sql' => array
		(
			'keys' => array
			(
				'id' => 'primary',
				'archive' => 'index',
				'archive,published,label' => 'index'
			)
		)
	),

	// Fields
	'fields' => array
	(
		'id' => array
		(
			'sql'	=> "int(10) unsigned NOT NULL auto_increment"
		),
		'pid' => array
		(
			'sql'	=> "int(10) unsigned NOT NULL default '0'"
		),
		'tstamp' => array
		(
			'sql'	=> "int(10) unsigned NOT NULL default '0'"
		),
		'label' => array
		(
			'sql'	=> "varchar(128) NOT NULL default ''"
		),
		'archive' => array
		(
			'sql'	=> "int(10) unsigned NOT NULL default '0'"
		),
		'root' => array
		(
			'sql'	=> "int(10) unsigned NOT NULL default '0'"
		),
		'published' => array
		(
			'sql'	=> "char(1) NOT NULL default ''"
		),
	)
);
