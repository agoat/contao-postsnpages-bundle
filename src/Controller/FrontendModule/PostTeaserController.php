<?php

namespace Agoat\PostsnPagesBundle\Controller\FrontendModule;


use Agoat\PostsnPagesBundle\Contao\Posts;
use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\ServiceAnnotation\FrontendModule;
use Contao\ModuleModel;
use Contao\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


/**
 * @FrontendModule(category="post", template="postteaser")
 */
class PostTeaserController extends AbstractFrontendModuleController
{
    protected function getResponse(Template $template, ModuleModel $model, Request $request): ?Response
    {
        // TODO: Implement getResponse() method.

        return $template->getResponse();
    }


}
