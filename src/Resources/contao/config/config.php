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
		'stylesheet'	=> array('bundles/agoatextendedarticles/articleTree.css'),
		'javascript'	=> array
		(
			'bundles/agoatextendedarticles/core.js',
			'bundles/agoatextendedarticles/chosenAddOption.js',
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

$GLOBALS['BE_MOD']['content'] = array_merge($content, $GLOBALS['BE_MOD']['content']);


/**
 * Register front end modules
 */
$arrModules['article']['posts'] = 'Agoat\ContentManager\ModulePostPosts';
$arrModules['article']['teaser'] = 'Agoat\ContentManager\ModulePostTeaser';
$arrModules['article']['postreader'] = 'Agoat\ContentManager\ModulePostReader';

//$arrModules['article']['archive'] = 'Agoat\ContentManager\ModuleContainer';
//$arrModules['article']['tags'] = 'Agoat\ContentManager\ModuleContainer';
//$arrModules['navigation']['articles'] = 'Agoat\ContentManager\ModuleContainer';

$GLOBALS['FE_MOD'] = array_merge($arrModules, $GLOBALS['FE_MOD']);


/**
 * Back end form fields (widgets)
 */
$GLOBALS['BE_FFL']['inputselect'] = '\Agoat\ContentManager\InputSelect';
$GLOBALS['BE_FFL']['moduleWizard'] = '\Agoat\ContentManager\ModuleWizard';


/**
 * Register the auto_item keywords
 */
$GLOBALS['TL_AUTO_ITEM'][] = 'posts';


/**
 * Register HOOK
 */

//$GLOBALS['TL_HOOKS']['getArticles'][] = array('Agoat\\ContentManager\\Controller', 'getSections'); 
//$GLOBALS['TL_HOOKS']['getPageIdFromUrl'][] = array('Agoat\\ContentManager\\Controller', 'avoidArticlesFragment'); 
 
$GLOBALS['TL_HOOKS']['initializeSystem'][] = array('Agoat\\ContentManager\\Controller', 'hideArticles'); 
$GLOBALS['TL_HOOKS']['executePostActions'][] = array('tl_posts', 'toggleFeaturedPost'); 


$bundles = \System::getContainer()->getParameter('kernel.bundles');
if (isset($bundles['ContaoCommentsBundle']))
{
	$GLOBALS['TL_HOOKS']['listComments'][] = array('tl_comments_extendedarticle', 'listPatternComments'); 
}
