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
$arrModules['posts']['postscontent'] 		= 'Agoat\PostsnPagesBundle\Contao\ModulePostsContent';
$arrModules['posts']['poststeaser'] 		= 'Agoat\PostsnPagesBundle\Contao\ModulePostsTeaser';
$arrModules['posts']['postreader'] 			= 'Agoat\PostsnPagesBundle\Contao\ModulePostReader';
$arrModules['posts']['taggedpoststeaser'] 	= 'Agoat\PostsnPagesBundle\Contao\ModuleTaggedPostsTeaser';
$arrModules['posts']['relatedpoststeaser'] 	= 'Agoat\PostsnPagesBundle\Contao\ModuleRelatedPostsTeaser';

$GLOBALS['FE_MOD'] = $arrModules + $GLOBALS['FE_MOD'];


$GLOBALS['FE_MOD']['navigationMenu']['poststagmenu']		= 'Agoat\PostsnPagesBundle\Contao\ModulePostsTagMenu';
//$GLOBALS['FE_MOD']['navigationMenu']['postscategorymenu'] 	= 'Agoat\PostsnPagesBundle\Contao\ModulePostsArchiveMenu';
//$GLOBALS['FE_MOD']['navigationMenu']['poststimetablemenu'] 	= 'Agoat\PostsnPagesBundle\Contao\ModulePostsTimetableMenu';

$GLOBALS['FE_MOD']['miscellaneous']['static'] 			= 'Agoat\PostsnPagesBundle\Contao\ModuleStatic';
$GLOBALS['FE_MOD']['miscellaneous']['containerlist'] 	= 'Agoat\PostsnPagesBundle\Contao\ModuleContainerList';


/**
 * Back end form fields (widgets)
 */
$GLOBALS['BE_FFL']['inputselect'] 	= '\Agoat\PostsnPagesBundle\Contao\InputSelect';
$GLOBALS['BE_FFL']['moduleWizard'] 	= '\Agoat\PostsnPagesBundle\Contao\ModuleWizard';
$GLOBALS['BE_FFL']['archiveTree'] 	= '\Agoat\PostsnPagesBundle\Contao\ArchiveTree';
$GLOBALS['BE_FFL']['postTree'] 		= '\Agoat\PostsnPagesBundle\Contao\PostTree';
$GLOBALS['BE_FFL']['staticTree'] 	= '\Agoat\PostsnPagesBundle\Contao\StaticTree';


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

$GLOBALS['TL_HOOKS']['getArticles'][] = array('Agoat\\PostsnPagesBundle\\Contao\\Controller', 'renderContainer'); 
 
$GLOBALS['TL_HOOKS']['initializeSystem'][] = array('Agoat\\PostsnPagesBundle\\Contao\\DataContainer', 'hideArticles'); 
$GLOBALS['TL_HOOKS']['executePostActions'][] = array('Agoat\\PostsnPagesBundle\\Contao\\Ajax','postActions');
$GLOBALS['TL_HOOKS']['replaceInsertTags'][] = array('Agoat\\PostsnPagesBundle\\Contao\\InsertTags','doReplace');

$GLOBALS['TL_HOOKS']['getLayoutId'][] = array('Agoat\\PostsnPagesBundle\\Contao\\Controller','getLayoutId');


$bundles = \System::getContainer()->getParameter('kernel.bundles');

if (array_key_exists('ContaoCommentsBundle', $bundles))
{
	$GLOBALS['TL_HOOKS']['listComments'][] = array('Agoat\\PostsnPagesBundle\\Contao\\Controller', 'listPatternComments'); 
}

if (array_key_exists('AgoatContentElementsBundle', $bundles))
{
	$GLOBALS['TL_HOOKS']['getRootPageId'][] = array('Agoat\\PostsnPagesBundle\\Contao\\Controller', 'getRootPageId'); 
}

if (array_key_exists('changelanguage', $bundles))
{
	$GLOBALS['TL_HOOKS']['loadDataContainer'][] = array('Agoat\\PostsnPagesBundle\\Contao\\ChangeLanguage', 'addPostsLanguage'); 
	$GLOBALS['TL_HOOKS']['changelanguageNavigation'][] = array('Agoat\\PostsnPagesBundle\\Contao\\ChangeLanguage', 'getPostsNavigation'); 
}

