<?php
/*
 * Posts'n'pages extension for Contao Open Source CMS.
 *
 * @copyright  Arne Stappen (alias aGoat) 2021
 * @package    contao-postsnpages
 * @author     Arne Stappen <mehh@agoat.xyz>
 * @link       https://agoat.xyz
 * @license    LGPL-3.0
 */

namespace Agoat\PostsnPagesBundle\EventListener;

use Agoat\PostsnPagesBundle\Model\ArchiveModel;
use Agoat\PostsnPagesBundle\Model\PostModel;
use Contao\CoreBundle\Event\PreviewUrlCreateEvent;
use Contao\CoreBundle\Framework\ContaoFramework;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class PostPreviewUrlCreateListener
{

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var ContaoFramework
     */
    private $framework;


    /**
     * @param  RequestStack  $requestStack
     * @param  ContaoFramework  $framework
     */
    public function __construct(RequestStack $requestStack, ContaoFramework $framework)
    {
        $this->requestStack = $requestStack;
        $this->framework = $framework;
    }


    /**
     * Adds a query to the front end preview URL.
     *
     * @param  PreviewUrlCreateEvent  $event
     *
     * @throws \RuntimeException
     */
    public function onPreviewUrlCreate(PreviewUrlCreateEvent $event): void
    {
        if (!$this->framework->isInitialized() || 'posts' !== $event->getKey()) {
            return;
        }

        $request = $this->requestStack->getCurrentRequest();

        if (null === $request) {
            throw new \RuntimeException('The request stack did not contain a request');
        }

        if (!$request->query->has('table') || 'tl_archive' === $request->query->get('table'
            ) || ('tl_post' === $request->query->get('table') && !$request->query->has('act'))) {
            if (null !== ($archiveModel = ArchiveModel::findByPk($event->getId()))) {
                $event->setQuery('page=' . $archiveModel->pid);
            }

            return;
        }

        if (null === ($postModel = PostModel::findByPk($this->getId($event, $request)))) {
            return;
        }

        $event->setQuery('post=' . $postModel->id);
    }


    /**
     * Returns the ID.
     *
     * @param  PreviewUrlCreateEvent  $event
     * @param  Request  $request
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
