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

use Agoat\PostsnPagesBundle\Contao\Posts;
use Agoat\PostsnPagesBundle\Model\PostModel;
use Contao\CoreBundle\Event\PreviewUrlConvertEvent;
use Contao\CoreBundle\Framework\ContaoFramework;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class PostPreviewUrlConvertListener
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
     * @param RequestStack    $requestStack
     * @param ContaoFramework $framework
     */
    public function __construct(RequestStack $requestStack, ContaoFramework $framework)
    {
        $this->requestStack = $requestStack;
        $this->framework = $framework;
    }

    /**
     * Modifies the front end preview URL.
     *
     * @param PreviewUrlConvertEvent $event
     */
    public function onPreviewUrlConvert(PreviewUrlConvertEvent $event): void
    {
        if (!$this->framework->isInitialized()) {
            return;
        }

        $request = $this->requestStack->getCurrentRequest();

        if (null === $request || null === ($postModel = $this->getPostModel($request))) {
            return;
        }

        /** @var Posts $postAdapter */
        $postAdapter = $this->framework->getAdapter(Posts::class);

        $event->setUrl($request->getSchemeAndHttpHost().'/'.$postAdapter->generatePostUrl($postModel));
    }

    /**
     * Returns the event model.
     *
     * @param Request $request
     *
     * @return PostModel|null
     */
    private function getPostModel(Request $request): ?PostModel
    {
        if (!$request->query->has('post')) {
            return null;
        }

        /** @var PostModel $adapter */
        $adapter = $this->framework->getAdapter(PostModel::class);

        return $adapter->findByPk($request->query->get('post'));
    }
}
