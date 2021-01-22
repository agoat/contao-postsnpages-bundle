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

use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\System;


/**
 * @Hook("loadDataContainer")
 */
class LanguageRelationListener
{

    private const CONSTRUCTORS = [
        'tl_archive'   => [
            'Agoat\LanguageRelationBundle\DataContainer\PageNodeViewConstructor',
            'Agoat\LanguageRelationBundle\DataContainer\RelationFieldConstructor',
            'Agoat\LanguageRelationBundle\DataContainer\NoRelationCallbackConstructor',
        ],
        'tl_post'      => [
            'Agoat\PostsnPagesBundle\DataContainer\PostArchiveViewConstructor',
            'Agoat\LanguageRelationBundle\DataContainer\RelationFieldConstructor',
            'Agoat\LanguageRelationBundle\DataContainer\NoRelationCallbackConstructor',
        ],
        'tl_container' => [
            'Agoat\LanguageRelationBundle\DataContainer\PageNodeViewConstructor',
            'Agoat\LanguageRelationBundle\DataContainer\RelationFieldConstructor',
            'Agoat\LanguageRelationBundle\DataContainer\NoRelationCallbackConstructor',
        ],
    ];

    private $languageRelationBundleExist = false;


    /**
     * Check if the agoat/contao-languagerelation extension is installed
     */
    public function __construct()
    {
        $bundles = System::getContainer()->getParameter('kernel.bundles');

        if (array_key_exists('AgoatLanguageRelationBundle', $bundles)) {
            $this->languageRelationBundleExist = true;
        }
    }


    public function __invoke(string $table): void
    {
        if ('FE' == TL_MODE || !$this->languageRelationBundleExist) {
            return;
        }

        foreach (self::CONSTRUCTORS as $context => $constructors) {
            if ($table === $context) {
                foreach ($constructors as $constructorClass) {
                    $constructor = new $constructorClass($table);
                    $constructor->buildDca();
                }
            }
        }
    }

}
