<?php

/*
 * Contao Extended Articles Extension
 *
 * Copyright (c) 2017 Arne Stappen
 *
 * @license LGPL-3.0+
 */
 
 
/**
 * Register back end module (additional javascript)
 */

$content = array
(
	'posts'	=> array
	(
		'tables'		=> array('tl_archive', 'tl_posts', 'tl_content'),
		'table'			=> array('contao.controller.backend_csv_import', 'importTableWizard'),
		'list'			=> array('contao.controller.backend_csv_import', 'importListWizard'),
		'javascript'	=> array
		(
			'bundles/agoatpostsnpages/chosenAddOption.js',
		)	
	),
	'pages'	=> array
	(
		'tables'	=> array('tl_container', 'tl_content'),
		'table'		=> array('contao.controller.backend_csv_import', 'importTableWizard'),
		'list'		=> array('contao.controller.backend_csv_import', 'importListWizard')
	),
	'static'	=> array
	(
		'tables'	=> array('tl_static', 'tl_content'),
		'table'		=> array('contao.controller.backend_csv_import', 'importTableWizard'),
		'list'		=> array('contao.controller.backend_csv_import', 'importListWizard')	
	)
);

$GLOBALS['BE_MOD']['content'] = $content + $GLOBALS['BE_MOD']['content'];



/**
 * Register front end modules
 */
$arrModules['posts']['postscontent'] 		= 'Agoat\PostsnPages\ModulePostsContent';
$arrModules['posts']['poststeaser'] 		= 'Agoat\PostsnPages\ModulePostsTeaser';
$arrModules['posts']['postreader'] 			= 'Agoat\PostsnPages\ModulePostReader';

$arrModules['posts']['taggedpostscontent'] 	= 'Agoat\PostsnPages\ModuleTaggedPostsContent';
$arrModules['posts']['taggedpoststeaser'] 	= 'Agoat\PostsnPages\ModuleTaggedPostsTeaser';

$GLOBALS['FE_MOD'] = $arrModules + $GLOBALS['FE_MOD'];


$GLOBALS['FE_MOD']['navigationMenu']['poststagmenu']		= 'Agoat\PostsnPages\ModulePostsTagMenu';
$GLOBALS['FE_MOD']['navigationMenu']['postsarchivemenu'] 	= 'Agoat\PostsnPages\ModulePostsArchiveMenu';
$GLOBALS['FE_MOD']['navigationMenu']['poststimetablemenu'] 	= 'Agoat\PostsnPages\ModulePostsTimetableMenu';

$GLOBALS['FE_MOD']['miscellaneous']['static'] 			= 'Agoat\PostsnPages\ModuleStatic';


/**
 * Back end form fields (widgets)
 */
$GLOBALS['BE_FFL']['inputselect'] 	= '\Agoat\PostsnPages\InputSelect';
$GLOBALS['BE_FFL']['moduleWizard'] 	= '\Agoat\PostsnPages\ModuleWizard';
$GLOBALS['BE_FFL']['archiveTree'] 	= '\Agoat\PostsnPages\ArchiveTree';
$GLOBALS['BE_FFL']['postTree'] 		= '\Agoat\PostsnPages\PostTree';
$GLOBALS['BE_FFL']['staticTree'] 	= '\Agoat\PostsnPages\StaticTree';


/**
 * Register the auto_item keywords
 */
$GLOBALS['TL_AUTO_ITEM'][] = 'posts';
$GLOBALS['TL_AUTO_ITEM'][] = 'tags';


/**
 * Style sheet
 */
if (TL_MODE == 'BE')
{
	$GLOBALS['TL_CSS'][] = 'bundles/agoatpostsnpages/style.css|static';
}



/**
 * Register HOOKS
 */

$GLOBALS['TL_HOOKS']['getArticles'][] = array('Agoat\\PostsnPages\\Controller', 'renderContainer'); 
 
$GLOBALS['TL_HOOKS']['initializeSystem'][] = array('Agoat\\PostsnPages\\Controller', 'hideArticles'); 
$GLOBALS['TL_HOOKS']['executePostActions'][] = array('Agoat\\PostsnPages\\Ajax','postActions');
$GLOBALS['TL_HOOKS']['replaceInsertTags'][] = array('Agoat\\PostsnPages\\InsertTags','doReplace');


$bundles = \System::getContainer()->getParameter('kernel.bundles');

if (array_key_exists('ContaoCommentsBundle', $bundles))
{
	$GLOBALS['TL_HOOKS']['listComments'][] = array('tl_comments_extendedarticle', 'listPatternComments'); 
}

if (array_key_exists('AgoatContentElementsBundle', $bundles))
{
	$GLOBALS['TL_HOOKS']['getLayoutId'][] = array('Agoat\\PostsnPages\\Controller', 'getLayoutId'); 
}
