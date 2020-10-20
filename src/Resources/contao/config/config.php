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

use Agoat\PostsnPagesBundle\Contao\ArchiveTree;
use Agoat\PostsnPagesBundle\Contao\InputSelect;
use Agoat\PostsnPagesBundle\Contao\ModuleWizard;
use Agoat\PostsnPagesBundle\Contao\PostTree;
use Agoat\PostsnPagesBundle\Contao\StaticTree;
use Contao\ListWizard;
use Contao\TableWizard;

/**
 * Back end modules
 */
$content = array
(
	'posts'	=> array
	(
		'tables'		=> array('tl_archive', 'tl_post', 'tl_content'),
		'table'			=> array(TableWizard::class, 'importTable'),
		'list'			=> array(ListWizard::class, 'importList'),
		'javascript'	=> array
		(
			'bundles/agoatpostsnpages/chosenAddOption.js',
		)
	),
	'pages'	=> array
	(
		'tables'	=> array('tl_container', 'tl_content'),
        'table'			=> array(TableWizard::class, 'importTable'),
        'list'			=> array(ListWizard::class, 'importList'),
	),
	'static'	=> array
	(
		'tables'	=> array('tl_static', 'tl_content'),
        'table'			=> array(TableWizard::class, 'importTable'),
        'list'			=> array(ListWizard::class, 'importList'),
	)
);

$GLOBALS['BE_MOD']['content'] = $content + $GLOBALS['BE_MOD']['content'];


$bundles = \System::getContainer()->getParameter('kernel.bundles');

/**
 * Front end modules
 */
$arrModules['posts']['postteaser'] 		= 'Agoat\PostsnPagesBundle\Contao\ModulePostTeaser';
if (array_key_exists('ContaoCommentsBundle', $bundles)) {
	$arrModules['posts']['postcomments'] 	= 'Agoat\PostsnPagesBundle\Contao\ModulePostComments';
}
$arrModules['posts']['relatedpostteaser'] 	= 'Agoat\PostsnPagesBundle\Contao\ModuleRelatedPostTeaser';
$arrModules['posts']['taggedpostteaser'] 	= 'Agoat\PostsnPagesBundle\Contao\ModuleTaggedPostTeaser';

$GLOBALS['FE_MOD'] = $arrModules + $GLOBALS['FE_MOD'];

$GLOBALS['FE_MOD']['navigationMenu']['posttagmenu']		= 'Agoat\PostsnPagesBundle\Contao\ModulePostTagMenu';
//$GLOBALS['FE_MOD']['navigationMenu']['postscategorymenu'] 	= 'Agoat\PostsnPagesBundle\Contao\ModulePostsArchiveMenu';
//$GLOBALS['FE_MOD']['navigationMenu']['poststimetablemenu'] 	= 'Agoat\PostsnPagesBundle\Contao\ModulePostsTimetableMenu';
$GLOBALS['FE_MOD']['miscellaneous']['static'] 			= 'Agoat\PostsnPagesBundle\Contao\ModuleStatic';
$GLOBALS['FE_MOD']['miscellaneous']['containerlist'] 	= 'Agoat\PostsnPagesBundle\Contao\ModuleContainerList';


/**
 * Page types
 */
$GLOBALS['TL_PTY'] = array_merge(
    array_slice($GLOBALS['TL_PTY'], 0, 1, true),
    ['post' => 'PageRegular'],
    array_slice($GLOBALS['TL_PTY'], 1, null, true)
);


/**
 * Pattern types (CustomContentElements extension)
 */
if (isset($GLOBALS['TL_CTP'])) {
    $GLOBALS['TL_CTP']['input'] = array_merge(
        array_slice($GLOBALS['TL_CTP']['input'], 0, $insertPos = (array_flip(array_keys($GLOBALS['TL_CTP']['input']))['pagetree'] + 1), true),
        ['posttree' => [
            'class'     => 'Agoat\PostsnPagesBundle\Contao\PatternPostTree',
            'data'      => true,
            'output'    => true,
        ]],
        array_slice($GLOBALS['TL_CTP']['input'], $insertPos, null, true)
    );
}


/**
 * Back end form fields (widgets)
 */
$GLOBALS['BE_FFL']['inputselect'] 	= InputSelect::class;
$GLOBALS['BE_FFL']['moduleWizard'] 	= ModuleWizard::class;
$GLOBALS['BE_FFL']['archiveTree'] 	= ArchiveTree::class;
$GLOBALS['BE_FFL']['postTree'] 		= PostTree::class;
$GLOBALS['BE_FFL']['staticTree'] 	= StaticTree::class;


/**
 * Register the auto_item keywords
 */
$GLOBALS['TL_AUTO_ITEM'][] = 'posts';
$GLOBALS['TL_AUTO_ITEM'][] = 'tags';


/**
 * Backend style sheet
 */
if (TL_MODE == 'BE')
{
	$GLOBALS['TL_CSS'][] = 'bundles/agoatpostsnpages/style.css|static';
}


/**
 * Models
 */

$GLOBALS['TL_MODELS']['tl_archive'] = \Agoat\PostsnPagesBundle\Model\ArchiveModel::class;
$GLOBALS['TL_MODELS']['tl_container'] = \Agoat\PostsnPagesBundle\Model\ContainerModel::class;
$GLOBALS['TL_MODELS']['tl_post'] = \Agoat\PostsnPagesBundle\Model\PostModel::class;
$GLOBALS['TL_MODELS']['tl_static'] = \Agoat\PostsnPagesBundle\Model\StaticModel::class;
$GLOBALS['TL_MODELS']['tl_tags'] = \Agoat\PostsnPagesBundle\Model\TagsModel::class;

/**
 * Register HOOKS
 */

//$GLOBALS['TL_HOOKS']['getArticles'][] = array('Agoat\\PostsnPagesBundle\\Contao\\Controller', 'renderPageContent');

//$GLOBALS['TL_HOOKS']['initializeSystem'][] = array('Agoat\\PostsnPagesBundle\\Contao\\DataContainer', 'hideArticles');
//$GLOBALS['TL_HOOKS']['executePostActions'][] = array('Agoat\\PostsnPagesBundle\\Contao\\Ajax','postActions');
//$GLOBALS['TL_HOOKS']['replaceInsertTags'][] = array('Agoat\\PostsnPagesBundle\\Contao\\InsertTags','doReplace');

//$GLOBALS['TL_HOOKS']['getLayoutId'][] = array('Agoat\\PostsnPagesBundle\\Contao\\Controller','getLayoutId'); //TODO needed?? from customcontentelementsbundle
//$GLOBALS['TL_HOOKS']['getPageStatusIcon'][] = array('Agoat\\PostsnPagesBundle\\Contao\\Controller','getPostsPageStatusIcon');



//if (array_key_exists('AgoatLanguageRelationBundle', $bundles))
//{
//	$GLOBALS['TL_HOOKS']['loadDataContainer'][] = array('Agoat\\PostsnPagesBundle\\DataContainer\\LanguageRelationAssembler', 'buildDca');
//}

//if (array_key_exists('ContaoCommentsBundle', $bundles))
//{
//	$GLOBALS['TL_HOOKS']['listComments'][] = array('Agoat\\PostsnPagesBundle\\Contao\\Controller', 'listPatternComments');
//}

//if (array_key_exists('AgoatContentElementsBundle', $bundles))
//{
//	$GLOBALS['TL_HOOKS']['getRootPageId'][] = array('Agoat\\PostsnPagesBundle\\Contao\\Controller', 'getRootPageId');
//}

//if (array_key_exists('changelanguage', $bundles))
//{
//	$GLOBALS['TL_HOOKS']['loadDataContainer'][] = array('Agoat\\PostsnPagesBundle\\Contao\\ChangeLanguage', 'addPostsLanguage');
//	$GLOBALS['TL_HOOKS']['changelanguageNavigation'][] = array('Agoat\\PostsnPagesBundle\\Contao\\ChangeLanguage', 'getPostsNavigation');
//}

