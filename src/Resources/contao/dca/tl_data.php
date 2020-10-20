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


$GLOBALS['TL_DCA']['tl_data']['fields']['singlePost'] = ['sql' => "binary(16) NULL"];
$GLOBALS['TL_DCA']['tl_data']['fields']['multiPost'] = ['sql' => "blob NULL"];
$GLOBALS['TL_DCA']['tl_data']['fields']['orderPost'] = ['sql' => "blob NULL"];
