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

namespace Agoat\PostsnPagesBundle\Contao;

use \Contao\Controller as ContaoController;
use Agoat\PostsnPagesBundle\Contao\Controller;


/**
 * Controller class
 */
class InsertTags extends ContaoController
{
	
	/**
	 * Replace the insert tags
	 *
	 * @param mixed  $strTag
	 *
	 * @return string
	 */	
	public function doReplace ($strTag)
	{
		$elements = explode('::', $strTag);
		
		switch ($elements[0])
		{
			// Post
			case 'post_link':
			case 'post_open':
			case 'post_url':
			case 'post_title':
			case 'post_subtitle':
			case 'post_teaser':
			case 'post_date':
			case 'post_location':
			case 'post_latlong':
			case 'post_category':
			case 'post_tags':
				if (($objPost = \PostsModel::findByIdOrAlias($elements[1])) === null)
				{
					break;
				}

				// Check the visibility
				if (!static::isVisibleElement($objPost))
				{
					break;
				}

				// Replace the tag
				switch (strtolower($elements[0]))
				{
					case 'post_link':
						$return = sprintf(
							'<a href="%s" title="%s">%s</a>',
							Posts::generatePostUrl($objPost, ('target' == $elements[2])),
							\StringUtil::specialchars($objPost->title),
							$objPost->title
						);
						break;
						
					case 'post_open':
						$return = sprintf(
							'<a href="%s" title="%s">',
							Posts::generatePostUrl($objPost, ('target' == $elements[2])),
							\StringUtil::specialchars($objPost->title)
						);
						break;
						
					case 'post_url':
						$return = Posts::generatePostUrl($objPost, ('target' == $elements[2]));
						break;
						
					case 'post_title':
						$return = \StringUtil::specialchars($objPost->title);
						break;

					case 'post_subtitle':
						$return = \StringUtil::specialchars($objPost->subTitle);
						break;

					case 'post_teaser':
						$return = \StringUtil::toHtml5($objPost->teaser);
						break;

					case 'post_date':
						$return = \Date::parse($elements[2] ?: \Config::get('dateFormat'), $objPost->date);
						break;

					case 'post_location':
						$return = \StringUtil::specialchars($objPost->location);
						break;

					case 'post_latlong':
						$return = \StringUtil::specialchars(implode(', ', \StringUtil::deserialize($objPost->latlong)));
						break;

					case 'post_category':
						$return = \StringUtil::specialchars($objPost->category);
						break;

					case 'post_tags':
						$return = \StringUtil::specialchars($objPost->category);
						break;
				}
				
				break;
				
			// Insert post
			case 'insert_post':
				if (($objPost = \PostsModel::findByIdOrAlias($elements[1])) === null)
				{
					break;
				}
				
				$return = Controller::generatePost($objPost, true);
				break;

			// Insert static
			case 'insert_static':
				if (($objStatic = \StaticModel::findByIdOrAlias($elements[1])) === null)
				{
					break;
				}
				
				$return = Controller::generateStatic($objStatic, true);
				break;

			// Insert container
			case 'insert_container':
				if (($objContainer = \ContainerModel::findByIdOrAlias($elements[1])) === null)
				{
					break;
				}

				$return = Controller::generateContainer($objContainer, true);
				break;

		}
	
		return $return;
	}
}
