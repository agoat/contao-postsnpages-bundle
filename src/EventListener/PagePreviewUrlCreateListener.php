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

use Contao\CoreBundle\Event\PreviewUrlCreateEvent;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class PagePreviewUrlCreateListener
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var ContaoFrameworkInterface
     */
    private $framework;

    /**
     * @param RequestStack             $requestStack
     * @param ContaoFrameworkInterface $framework
     */
    public function __construct(RequestStack $requestStack, ContaoFrameworkInterface $framework)
    {
        $this->requestStack = $requestStack;
        $this->framework = $framework;
    }

    /**
     * Adds a query to the front end preview URL.
     *
     * @param PreviewUrlCreateEvent $event
     *
     * @throws \RuntimeException
     */
    public function onPreviewUrlCreate(PreviewUrlCreateEvent $event): void
    {
		if (!$this->framework->isInitialized() || 'pages' !== $event->getKey()) {
            return;
        }

        $request = $this->requestStack->getCurrentRequest();

        if (null === $request) {
            throw new \RuntimeException('The request stack did not contain a request');
        }

        if (null === ($containerModel = \ContainerModel::findByPk($this->getId($event, $request)))) {
            return;
        }

        $event->setQuery('page='.$containerModel->pid);
    }

    /**
     * Returns the ID.
     *
     * @param PreviewUrlCreateEvent $event
     * @param Request               $request
     *
     * @return int|string
     */
    private function getId(PreviewUrlCreateEvent $event, Request $request)
    {
        if ('tl_content' !== $request->query->get('table') && 'edit' === $request->query->get('act')) {
            return $request->query->get('id');
        }

        return $event->getId();
    }
}
