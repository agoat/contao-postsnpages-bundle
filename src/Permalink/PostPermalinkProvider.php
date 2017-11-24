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

namespace Agoat\PostsnPagesBundle\Permalink;

use Agoat\PermalinkBundle\Permalink\PermalinkProviderFactory;
use Agoat\PermalinkBundle\Permalink\PermalinkProviderInterface;
use Agoat\PermalinkBundle\Permalink\PermalinkUrl;
use Contao\CoreBundle\Exception\AccessDeniedException;


/**
 * Permalink provider for Posts
 *
 * @author Arne Stappen <https://github.com/agoat>
 */
class PostPermalinkProvider extends PermalinkProviderFactory implements PermalinkProviderInterface
{
	
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
	public function generate($context, $source)
	{
		$objPost = \PostsModel::findByPk($source);

		if (null === $objPost)
		{
			// Todo: throw fatal error;
		}

		$objPost->refresh(); // Fetch current from database (maybe modified from other onsubmit_callbacks)

		$objArchive = \ArchiveModel::findByPk($objPost->pid);
		$objPage = \PageModel::findByPk($objArchive->pid);

		if (null === $objPage)
		{
			// Todo: throw fatal error;
		}

		$objPage->refresh(); // Fetch current from database
		$objPage->loadDetails();

		$permalink = new PermalinkUrl();

		$permalink->setScheme($objPage->rootUseSSL ? 'https' : 'http')
				  ->setHost($objPage->domain)
				  ->setPath($this->validatePath($this->resolvePattern($objPost)))
				  ->setSuffix($this->suffix);

		$this->registerPermalink($permalink, $context, $source);
	}


	/**
     * {@inheritdoc}
     */	
	public function remove($context, $source)
	{
		return $this->unregisterPermalink($context, $source);
	}

	
	/**
     * {@inheritdoc}
     */	
	public function getUrl($context, $source)
	{
		$objPost = \PostsModel::findByPk($source);

		if (null === $objPost)
		{
			// Todo: throw fatal error;
		}

		$objArchive = \ArchiveModel::findByPk($objPost->pid);
		$objPage = \PageModel::findWithDetails($objArchive->pid);

		if (null === $objPage)
		{
			// Todo: throw fatal error;
		}

		$objPermalink = \PermalinkModel::findByContextAndSource($context, $source);
	
		$permalink = new PermalinkUrl();
		
		$permalink->setScheme($objPage->rootUseSSL ? 'https' : 'http')
				  ->setGuid((null !== $objPermalink) ? $objPermalink->guid : $objPage->domain)
				  ->setSuffix((strpos($permalink->getGuid(), '/')) ? $this->suffix : '');

		return $permalink;
	}


	/**
	 * Resolve pattern to strings
	 *
	 * @param \PostsModel $objPost
	 *
	 * @return String
	 *
	 * @throws AccessDeniedException
	 */
	protected function resolvePattern($objPost)
	{
		$tags = preg_split('~{{([\pL\pN][^{}]*)}}~u', $objPost->permalink, -1, PREG_SPLIT_DELIM_CAPTURE);
		
		if (count($tags) < 2)
		{
			return $objPost->permalink;
		}
		
		$buffer = '';
		
		for ($_rit=0, $_cnt=count($tags); $_rit<$_cnt; $_rit+=2)
		{
			$buffer .= $tags[$_rit];
			list($tag,$addition) = explode ('+', $tags[$_rit+1]);

			// Skip empty tags
			if ($tag == '')
			{
				continue;
			}

			// Replace the tag
			switch (strtolower($tag))
			{
				// Alias
				case 'alias':
					$buffer .= \StringUtil::generateAlias($objPost->title) . $addition;
					break;
			
				// Alias
				case 'author':
					$objUser = \UserModel::findByPk($objPost->author);
					
					if ($objUser)
					{
						$buffer .= \StringUtil::generateAlias($objUser->name) . $addition;
					}
					break;
			
				// Parent (alias)
				case 'parent':
					$objArchive = \ArchiveModel::findByPk($objPost->pid);
					$objParent = \PageModel::findByPk($objArchive->pid);
				
					if ($objParent && 'root' != $objParent->type)
					{
						$buffer .= $objParent->alias . $addition;
					}
					break;
					
				// Date
				case 'date':
					$objArchive = \ArchiveModel::findByPk($objPost->pid);
					$objPage = \PageModel::findWithDetails($objArchive->pid);
	
					if (!($format = $objPage->dateFormat))
					{
						$format = \Config::get('dateFormat');
					}
			
					$buffer .= \StringUtil::generateAlias(date($format, $objPost->date)) . $addition;
					break;
					
				// Time
				case 'time':
					$objArchive = \ArchiveModel::findByPk($objPost->pid);
					$objPage = \PageModel::findWithDetails($objArchive->pid);
	
					if (!($format = $objPage->timeFormat))
					{
						$format = \Config::get('timeFormat');
					}
				
					$buffer .= \StringUtil::generateAlias(str_replace(':', '-', date($format, $objPost->date))) . $addition;
					break;

				// Year
				case 'year':
					$buffer .= date('Y', $objPost->date) . $addition;
					break;
			
				// Month
				case 'month':
					$buffer .= date('m', $objPost->date) . $addition;
					break;
			
				// Month
				case 'day':
					$buffer .= date('d', $objPost->date) . $addition;
					break;
			
				// Location
				case 'location':
					$buffer .= ('' != $objPost->location) ? \StringUtil::generateAlias($objPost->location) . $addition : '';
					break;
			
				// Latitude/Longitude
				case 'latlong':
					list($lat, $long) = \StringUtil::deserialize($objPost->latlong);

					$buffer .= ('' != $lat && '' != $long) ? \StringUtil::generateAlias($lat . '-' . $long) . $addition : '';
					break;
			
				// Category
				case 'category':
					$buffer .= ('' != $objPost->category) ? \StringUtil::generateAlias($objPost->category) . $addition : '';
					break;
			
				// Language
				case 'language':
					$objArchive = \ArchiveModel::findByPk($objPost->pid);
					$objPage = \PageModel::findWithDetails($objArchive->pid);
					
					if ($objPage)
					{
						$buffer .= $objPage->rootLanguage . $addition;
					}
					break;
				
				default:
					throw new AccessDeniedException(sprintf($GLOBALS['TL_LANG']['ERR']['unknownInsertTag'], $tag)); 
			}
		}
		
		return $buffer;
	}
}
