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
use Contao\ContainerModel;
use Contao\PageModel;



class ContainerLanguageRelationProvider extends AbstractLanguageRelationProvider implements LanguageRelationProviderInterface
{
	
	/**
     * {@inheritdoc}
     */	
	public function getContext()
	{
		return 'container';
	}
	
	/**
     * {@inheritdoc}
     */	
	public function getDcaTable()
	{
		return 'tl_container';
	}
	
	
	/**
     * {@inheritdoc}
     */	
	public function getQueryName()
	{
		return 'articles';
	}


	public function build($id, $published)
	{
		$this->currentEntity = ContainerModel::findByPk($id);

		if (null === $this->currentEntity) {
			return null;
		} 
		
		$this->parentEntity = PageModel::findByPk($this->currentEntity->pid);
		
		$this->setRootLanguages($published, $this->parentEntity);

		return new LanguageRelation(
			$this, 
			$this->currentLanguage,
			array_keys($this->rootPages), 
			$this->getRelations($published)
		);
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
		return false;
	}

	
	public function getSelectOptions($language)
	{
		$options = array();

		$this->setParentRelations(false);	

		if (!array_key_exists($language, $this->parentRelations)) {
			return $options;
		}
	
		$containers = ContainerModel::findByPid($this->parentRelations[$language]->id, ['order'=>'sorting']);
		
		if (null === $containers) {
			return $options;
		}
		
		foreach ($containers as $container) {
			$options[] = array(
				'value' => $container->id,
				'label' => $container->title
			);
		}
	
		return $options;
	}
	
	
	public function getCreateUrl($language)
	{
		$this->setParentRelations(false);	
	
		if (!array_key_exists($language, $this->parentRelations)) {
			return null;
		}

		$container = ContainerModel::findByPid($this->parentRelations[$language]->id, ['order'=>'sorting']);
	
		$query = 'act=copy&id='.$this->currentEntity->id.'&rid='.$this->currentEntity->relation;
		
		$query .= (null === $container) ?
			'&mode=2&pid='.$this->parentRelations[$language]->id :
			'&mode=1&pid='.$container->last()->id;

		return Backend::addToUrl($query);
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
