<?php
 
 /**
 * Contao Open Source CMS - Content management extension
 *
 * Copyright (c) 2017 Arne Stappen (aGoat)
 *
 *
 * @package   contentblocks
 * @author    Arne Stappen <http://agoat.de>
 * @license	  LGPL-3.0+
 */

namespace Agoat\PostsnPages;


class InsertTags extends \Controller
{
	
	/**
	 * Render page content
	 *
	 * @param mixed  $intId     The page id
	 * @param string $strColumn The name of the column
	 *
	 * @return string The module HTML markup
	 */	
	public function doReplace ($strTag)
	{
		
		$elements = explode('::', $strTag);
		
		switch ($elements[0])
		{
			// Insert post
			case 'insert_post':
				// use the Post:render method
				if (($strOutput = $this->getArticle($elements[1], false, true)) !== false)
				{
					$return = ltrim($strOutput);
				}
				else
				{
					$return = '<p class="error">' . sprintf($GLOBALS['TL_LANG']['MSC']['invalidPage'], $elements[1]) . '</p>';
				}
				break;

			// Post
			case 'post':
			case 'post_open':
			case 'post_url':
			case 'post_title':
				if (($objPost = \PostsModel::findByIdOrAlias($elements[1])) === null)
				{
					break;
				}

				/** @var PageModel $objPage */
				$strUrl = Posts::generatePostUrl($objPost);

				// Replace the tag
				switch (strtolower($elements[0]))
				{
					case 'post':
						$return = sprintf('<a href="%s" title="%s">%s</a>', $strUrl, \StringUtil::specialchars($objPost->title), $objPost->title);
						break;
						
					case 'post_open':
						$return = sprintf('<a href="%s" title="%s">', $strUrl, \StringUtil::specialchars($objPost->title));
						break;
						
					case 'post_url':
						$return = $strUrl;
						break;
						
					case 'post_title':
						$return = \StringUtil::specialchars($objPost->title);
						break;
				}
				
				break;
				
			// Post teaser
			case 'article_teaser':
				// use the Post:render method
				$objTeaser = \ArticleModel::findByIdOrAlias($elements[1]);
				
				if ($objTeaser !== null)
				{
					$return = \StringUtil::toHtml5($objTeaser->teaser);
				}
				
				break;

					
		}
	
		return $return;
	}
}