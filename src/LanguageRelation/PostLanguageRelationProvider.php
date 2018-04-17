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
use Agoat\PostsnPagesBundle\Contao\Posts;
use Contao\Backend;
use Contao\ArchiveModel;
use Contao\PageModel;
use Contao\PostsModel as PostModel;



class PostLanguageRelationProvider extends AbstractLanguageRelationProvider implements LanguageRelationProviderInterface
{
	
	/**
     * {@inheritdoc}
     */	
	public function getContext()
	{
		return 'post';
	}
	
	/**
     * {@inheritdoc}
     */	
	public function getDcaTable()
	{
		return 'tl_posts';
	}
	
	
	/**
     * {@inheritdoc}
     */	
	public function getQueryName()
	{
		return 'posts';
	}


	public function build($id, $published)
	{
		$this->currentEntity = Postmodel::findByPk($id);

		if (null === $this->currentEntity) {
			return null;
		} 

		$this->parentEntity = ArchiveModel::findByPk($this->currentEntity->pid);
	
		$this->setRootLanguages(PageModel::findByPk($this->parentEntity->pid), $published);

		return new LanguageRelation(
			$this, 
			$this->currentLanguage,
			array_keys($this->rootPages), 
			$this->getRelations($published)
		);
	}

	
	public function getFrontendUrl($related)
	{
		return Posts::generatePostUrl($related);
	}


	public function getAlternativeUrl($language, $onlyRoot)
	{
		$alternative = $this->getAlternative($language, $onlyRoot);
		
		if (null === $alternative) {
			return null;
		}
		
		return $alternative->getFrontendUrl();
	}


	public function getAlternativeTitle($language, $onlyRoot)
	{
		$alternative = $this->getAlternative($language, $onlyRoot);
		
		if (null === $alternative) {
			return null;
		}
		
		return $alternative->title;
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
		
		return \System::getContainer()->get('contao.picker.builder')->getUrl('post', $options);
	}
	
	
	public function getCreateUrl($language)
	{
		$this->setParentRelations(false);	

		if (!array_key_exists($language, $this->parentRelations)) {
			return null;
		}
		
		return Backend::addToUrl('act=copy&mode=2&id='.$this->currentEntity->id.'&pid='.$this->parentRelations[$language]->id);
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
	
	
	private function getAlternative($language, $onlyRoot)
	{
		if (!$onlyRoot) {
			$this->setParentRelations(false);
			
			if (!isset($this->alternativeRelations)) {
				$this->alternativeRelations = array();
				
				$relation = $this->getRelations(true, PageModel::findByPk($this->parentEntity->pid));
			
				if (null !== $relation) {
					foreach ($relation as $model) {
						$this->alternativeRelations[$model->language] = $model;
					}
				}
			}
	
			if (isset($this->alternativeRelations[$language]) && 'root' != $this->alternativeRelations[$language]->type) {
				return $this->alternativeRelations[$language];
			}
		}
	
		return PageModel::findFirstPublishedByPid($this->rootPages[$language]->id);
	}

}
