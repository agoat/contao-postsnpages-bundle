<?php
 
 /**
 * Contao Open Source CMS - Posts'n'Pages extensino
 *
 * Copyright (c) 2017 Arne Stappen (aGoat)
 *
 *
 * @package   postsnpages
 * @author    Arne Stappen <http://agoat.de>
 * @license	  LGPL-3.0+
 */


 
/**
 * Table tl_content_element
 */
$GLOBALS['TL_DCA']['tl_tags'] = array
(
	// Config
	'config' => array
	(
		'dataContainer'               => 'Table',
		'ptable'                      => 'tl_page',

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



