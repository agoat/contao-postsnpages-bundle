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
 * Dynamically set the parent tabel and onload_callback
 */
if (Input::get('do') == 'posts')
{
	$GLOBALS['TL_DCA']['tl_content']['config']['ptable'] = 'tl_post';
	$GLOBALS['TL_DCA']['tl_content']['config']['onload_callback'][] = array('tl_content', 'checkPermission');
}
else if (Input::get('do') == 'pages')
{
	$GLOBALS['TL_DCA']['tl_content']['config']['ptable'] = 'tl_container';
	$GLOBALS['TL_DCA']['tl_content']['config']['onload_callback'][] = array('tl_content', 'checkPermission');
}
if (Input::get('do') == 'static')
{
	$GLOBALS['TL_DCA']['tl_content']['config']['ptable'] = 'tl_static';
	$GLOBALS['TL_DCA']['tl_content']['config']['onload_callback'][] = array('tl_content', 'checkPermission');
}
