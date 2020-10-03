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

namespace Agoat\PostsnPagesBundle\EventListener;

use Agoat\PostsnPagesBundle\Contao\Frontend;
use Contao\Config;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Environment;
use Contao\Search;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\TerminateEvent;

class SearchIndexListener
{
    /**
     * @var ContaoFramework
     */
    private $framework;

    /**
     * @var string
     */
    private $fragmentPath;

    /**
     * @param ContaoFramework $framework
     * @param string          $fragmentPath
     */
    public function __construct(ContaoFramework $framework, string $fragmentPath = '_fragment')
    {
        $this->framework = $framework;
        $this->fragmentPath = $fragmentPath;
    }

    /**
     * Checks if the request can be indexed and forwards it accordingly.
     */
    public function onKernelTerminate(TerminateEvent $event): void
    {
		if (!$this->framework->isInitialized()) {
            return;
        }

        $request = $event->getRequest();

        // Only index GET requests (see #1194)
        if (!$request->isMethod(Request::METHOD_GET)) {
            return;
        }

        // Do not index fragments
        if (preg_match('~(?:^|/)'.preg_quote($this->fragmentPath, '~').'/~', $request->getPathInfo())) {
            return;
        }

        $this->indexPageIfApplicable($event->getResponse());
    }

    /**
     * Index a post reader page if applicable
     *
     * @param Response $objResponse
     */
    public function indexPageIfApplicable(Response $objResponse)
    {
        global $objPage;

        if ($objPage === null)
        {
            return;
        }

        // Index page if searching is allowed and there is no back end user
        if (Config::get('enableSearch') && $objPage->type == 'post' && !BE_USER_LOGGED_IN && !$objPage->noSearch)
        {
            // Index protected pages if enabled
            if (Config::get('indexProtected') || (!FE_USER_LOGGED_IN && !$objPage->protected))
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
                        'url'       => Environment::get('base') . Environment::get('relativeRequest'),
                        'content'   => $objResponse->getContent(),
                        'title'     => $objPage->pageTitle ?: $objPage->title,
                        'protected' => ($objPage->protected ? '1' : ''),
                        'groups'    => $objPage->groups,
                        'pid'       => $objPage->id,
                        'language'  => $objPage->language
                    );

                    Search::indexPage($arrData);
                }
            }
        }
    }
}
