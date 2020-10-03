<?php
/*
 * Language relations for Contao Open Source CMS.
 *
 * @copyright  Arne Stappen (alias aGoat) 2018
 * @package    contao-languagerelation
 * @author     Arne Stappen <mehh@agoat.xyz>
 * @link       https://agoat.xyz
 * @license    LGPL-3.0
 */

namespace Agoat\PostsnPagesBundle\DataContainer;

use Agoat\LanguageRelationBundle\DataContainer\AbstractConstructor;



/**
 * Add language switch button
 */
class PostArchiveViewConstructor extends AbstractConstructor
{
	public function buildDca ()
	{
		$GLOBALS['TL_DCA'][$this->table]['config']['onload_callback'][] = function(\DataContainer $dc) {
			if ('edit' == $_GET['act']) {
				return;
			}

			/** @var LanguageRelation */
			$languageRelation = \System::getContainer()->get('contao.language.relation')->buildFromDca($dc, true);

			if (null !== $languageRelation && $languageRelation->hasRelations()) {
				$this->createRelationButton($languageRelation);
			}
		};
	}
}
