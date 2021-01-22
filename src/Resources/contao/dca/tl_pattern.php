<?php
/*
 * Posts'n'pages extension for Contao Open Source CMS.
 *
 * @copyright  Arne Stappen (alias aGoat) 2021
 * @package    contao-postsnpages
 * @author     Arne Stappen <mehh@agoat.xyz>
 * @link       https://agoat.xyz
 * @license    LGPL-3.0
 */

$GLOBALS['TL_DCA']['tl_pattern']['palettes']['posttree'] =
    '{type_legend},type;{post_legend},multiPost;{label_legend},label,description;{pattern_legend},alias,mandatory,classClr;{invisible_legend},invisible';

$GLOBALS['TL_DCA']['tl_pattern']['fields']['multiPost'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_pattern']['multiPost'],
    'exclude'   => true,
    'inputType' => 'checkbox',
    'eval'      => ['tl_class' => 'w50 m12'],
    'sql'       => "char(1) NOT NULL default ''",
];
