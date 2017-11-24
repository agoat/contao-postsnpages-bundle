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
use Contao\FrontendIndex;
use Contao\CoreBundle\Exception\PageNotFoundException;
use Symfony\Component\HttpFoundation\Request;


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