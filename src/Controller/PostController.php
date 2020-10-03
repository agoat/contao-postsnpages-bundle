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

namespace Agoat\PostsnPagesBundle\Controller;

use Agoat\PermalinkBundle\Controller\ControllerInterface;
use Agoat\PostsnPagesBundle\Model\ArchiveModel;
use Agoat\PostsnPagesBundle\Model\PostModel;
use Contao\FrontendIndex;
use Contao\CoreBundle\Exception\PageNotFoundException;
use Contao\PageModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


/**
 * Main front end controller.
 *
 * @author Arne Stappen <https://github.com/agoat>
 */
class PostController implements ControllerInterface
{

	/**
     * {@inheritdoc}
     */
	public function getDcaTable()
	{
		return 'tl_post';
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
		$objPost = PostModel::findByPk($source);

		// Throw a 404 error if the post could not be found
		if (null === $objPost)
		{
			throw new PageNotFoundException('Post not found: ' . $request->getUri());
		}

		// Set the post id as get attribute
		\Input::setGet('posts', $objPost->id, true);

		$objArchive = ArchiveModel::FindByPk($objPost->pid);
		$objPage = PageModel::findPublishedById($objArchive->pid);

		// Throw a 404 error if the page is not visible
		if (null === $objPage)
		{
			throw new PageNotFoundException('Page not found: ' . $request->getUri());
		}

		// Render the corresponding page
		$frontendIndex = new FrontendIndex();
		return $frontendIndex->renderPage($objPage);
	}
}
