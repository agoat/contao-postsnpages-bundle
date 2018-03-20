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

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Agoat\PostsnPagesBundle\Contao\Frontend;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;

class AddToSearchIndexListener
{
    /**
     * @var ContaoFrameworkInterface
     */
    private $framework;

    /**
     * @var string
     */
    private $fragmentPath;

    /**
     * @param ContaoFrameworkInterface $framework
     * @param string                   $fragmentPath
     */
    public function __construct(ContaoFrameworkInterface $framework, string $fragmentPath = '_fragment')
    {
        $this->framework = $framework;
        $this->fragmentPath = $fragmentPath;
    }

    /**
     * Checks if the request can be indexed and forwards it accordingly.
     *
     * @param PostResponseEvent $event
     */
    public function onKernelTerminate(PostResponseEvent $event): void
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

        /** @var Frontend $frontend */
        $frontend = $this->framework->getAdapter(Frontend::class);
        $frontend->indexPageIfApplicable($event->getResponse());
    }
}
