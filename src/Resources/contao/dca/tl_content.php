<?php

/**
 * Dynamically add the permission check and parent table (see #5241)
 */
if (Input::get('do') == 'posts')
{
	$GLOBALS['TL_DCA']['tl_content']['config']['ptable'] = 'tl_posts';
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
