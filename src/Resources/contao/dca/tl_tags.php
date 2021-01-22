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


$GLOBALS['TL_DCA']['tl_tags'] = [
    // Config
    'config' => [
        'dataContainer' => 'Table',
        'ptable'        => 'tl_post',

        'sql' => [
            'keys' => [
                'id'                      => 'primary',
                'archive'                 => 'index',
                'archive,published,label' => 'index',
            ],
        ],
    ],

    // Fields
    'fields' => [
        'id'        => [
            'sql' => "int(10) unsigned NOT NULL auto_increment",
        ],
        'pid'       => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'tstamp'    => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'label'     => [
            'sql' => "varchar(256) NOT NULL default ''",
        ],
        'archive'   => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'root'      => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'published' => [
            'sql' => "char(1) NOT NULL default ''",
        ],
    ],
];
