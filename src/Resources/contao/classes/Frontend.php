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
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller class
 */
class Frontend extends ContaoController
{
	/**
	 * Make the constructor public
	 */
	public function __construct()
	{
		parent::__construct();
	}


	/**
	 * Index a post reader page if applicable
	 *
	 * @param Response $objResponse
	 */
	public static function indexPageIfApplicable(Response $objResponse)
	{
		global $objPage;

		if ($objPage === null)
		{
			return;
		}

		// Index page if searching is allowed and there is no back end user
		if (\Config::get('enableSearch') && $objPage->type == 'post' && !BE_USER_LOGGED_IN && !$objPage->noSearch)
		{
			// Index protected pages if enabled
			if (\Config::get('indexProtected') || (!FE_USER_LOGGED_IN && !$objPage->protected))
			{
				$blnIndex = true;

				// Do not index the page if certain parameters are set
				foreach (array_keys($_GET) as $key)
				{
					if (\in_array($key, $GLOBALS['TL_NOINDEX_KEYS']) || strncmp($key, 'page_', 5) === 0)
					{
						$blnIndex = false;
						break;
					}
				}

				if ($blnIndex)
				{
					$arrData = array(
						'url'       => \Environment::get('base') . \Environment::get('relativeRequest'),
						'content'   => $objResponse->getContent(),
						'title'     => $objPage->pageTitle ?: $objPage->title,
						'protected' => ($objPage->protected ? '1' : ''),
						'groups'    => $objPage->groups,
						'pid'       => $objPage->id,
						'language'  => $objPage->language
					);

					\Search::indexPage($arrData);
				}
			}
		}
	}
}
