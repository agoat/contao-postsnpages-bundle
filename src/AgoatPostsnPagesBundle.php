<?php

/*
 * Contao Extended Articles Extension
 *
 * Copyright (c) 2017 Arne Stappen
 *
 * @license LGPL-3.0+
 */
 
namespace Agoat\PostsnPagesBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Agoat\PostsnPagesBundle\DependencyInjection\Compiler\RemoveArticlePickerPass;

/**
 * Configures the Agoat contentblocks bundle.
 *
 * @author Arne Stappen (alias aGoat) <https://github.com/agoat>
 */
class AgoatPostsnPagesBundle extends Bundle
{
}