<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Agoat\PostsnPagesBundle\Frontend;

use Agoat\PermalinkBundle\Frontend\ControllerInterface;
use Contao\FrontendIndex;
use Contao\CoreBundle\Exception\PageNotFoundException;
use Symfony\Component\HttpFoundation\Request;


/**
 * Main front end controller.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class PostController implements ControllerInterface
{

	/**
     * {@inheritdoc}
     */	
	public function getTable()
	{
		return 'tl_posts';
	}


	/**
	 * Run the controller
	 *
	 * @return Response
	 *
	 * @throws PageNotFoundException
	 */
	public function run($source, Request $request)
	{
		$objPost = \PostsModel::findByPk($source);

		// Throw a 404 error if the event could not be found
		if (null === $objPost)
		{
			throw new PageNotFoundException('Post not found: ' . $request->getUri());
		}

		// Set the posts id as get attribute
		\Input::setGet('posts', $objPost->id, true);

		$objArchive = \ArchiveModel::FindByPk($objPost->pid);
		$objPage = \PageModel::findByPk($objArchive->pid);
	
		// Render the corresponding page from the calender setting
		$frontendIndex = new FrontendIndex();
		return $frontendIndex->renderPage($objPage);
	}
}