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

namespace Agoat\PostsnPagesBundle\LanguageRelation;

use Agoat\LanguageRelationBundle\LanguageRelation\AbstractLanguageRelationProvider;
use Agoat\LanguageRelationBundle\LanguageRelation\LanguageRelationProviderInterface;
use Agoat\LanguageRelationBundle\LanguageRelation\LanguageRelation;
use Contao\Backend;
use Contao\ArchiveModel;
use Contao\PageModel;



class ArchiveLanguageRelationProvider extends AbstractLanguageRelationProvider implements LanguageRelationProviderInterface
{
	
	/**
     * {@inheritdoc}
     */	
	public function getContext()
	{
		return 'archive';
	}
	
	/**
     * {@inheritdoc}
     */	
	public function getDcaTable()
	{
		return 'tl_archive';
	}
	
	
	public function build($id, $published)
	{
		$this->currentEntity = ArchiveModel::findByPk($id);

		if (null === $this->currentEntity) {
			return null;
		} 
		
		$this->parentEntity = PageModel::findByPk($this->currentEntity->pid);
		
		$this->setRootLanguages($this->parentEntity, $published);

		return new LanguageRelation(
			$this, 
			$this->currentLanguage,
			array_keys($this->rootPages), 
			$this->getRelations($published)
		);
	}

	
	public function getFrontendUrl($related)
	{
		return null;
	}


	public function getAlternativeUrl($language, $onlyRoot)
	{
		return null;
	}


	public function getAlternativeTitle($language, $onlyRoot)
	{
		return null;
	}


	public function getEditUrl($related)
	{
		return Backend::addToUrl('id='.$related->id);
	}
	
	
	public function getViewUrl($related)
	{
		return Backend::addToUrl('id='.$related->id);
	}
	
	
	public function supportsPicker()
	{
		return true;
	}

	
	public function getPickerUrl($language)
	{
		$options = [
			'rootNodes' => $this->rootPages[$language]->id
		];
		
		return \System::getContainer()->get('contao.picker.builder')->getUrl('archive', $options);
	}
	
	
	public function getCreateUrl($language)
	{
		return false; // Post archives shouldn't be copied to another language (?)
	}


	private function setParentRelations($published)
	{
		if (!isset($this->parentRelations)) {
			$this->parentRelations = array();
			
			$relation = $this->getRelations($published, $this->parentEntity);
		
			if (null !== $relation) {
				foreach ($relation as $model) {
					$this->parentRelations[$model->language] = $model;
				}
			}
		}
	}

}
