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

namespace Agoat\PostsnPagesBundle\DataContainer;

use Contao\System;


/**
 * Add language relation configuration to DCA
 */
class LanguageRelationAssembler
{

	private $constructors = [
		'tl_archive' => [
			'Agoat\LanguageRelationBundle\DataContainer\PageNodeViewConstructor',
			'Agoat\LanguageRelationBundle\DataContainer\RelationFieldConstructor',
			'Agoat\LanguageRelationBundle\DataContainer\NoRelationCallbackConstructor'
		],
		'tl_post' => [
			'Agoat\PostsnPagesBundle\DataContainer\PostArchiveViewConstructor',
			'Agoat\LanguageRelationBundle\DataContainer\RelationFieldConstructor',
			'Agoat\LanguageRelationBundle\DataContainer\NoRelationCallbackConstructor'
		],
		'tl_container' => [
			'Agoat\LanguageRelationBundle\DataContainer\PageNodeViewConstructor',
			'Agoat\LanguageRelationBundle\DataContainer\RelationFieldConstructor',
			'Agoat\LanguageRelationBundle\DataContainer\NoRelationCallbackConstructor'
		]
	];

	
	/**
	 * Add language relation field for supported tables
	 *
	 * @param string $strTable
	 */
	public function buildDca ($table)
	{
		if ('FE' == TL_MODE)
		{
			return;
		}
		
		foreach ($this->constructors as $context=>$constructors) {
			if ($table == $context) {
				foreach ($constructors as $constructor) {
					$worker = new $constructor($table);
					$worker->buildDca();
				}
			}
		}
	}
}
