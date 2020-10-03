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

use Agoat\PostsnPagesBundle\Model\ArchiveModel;
use Agoat\PostsnPagesBundle\Model\PostModel;
use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\DataContainer;
use Contao\PageModel;
use Contao\System;
use Terminal42\ChangeLanguage\PageFinder;


/**
 * @Hook("loadDataContainer")
 */
class ChangeLanguageListener
{
	private $languageRelationBundleExist = false;

    /**
     * Check if the agoat/contao-languagerelation extension is installed
     */
    public function __construct()
    {
        $bundles = System::getContainer()->getParameter('kernel.bundles');

        if (array_key_exists('changelanguage', $bundles)) {
            $this->languageRelationBundleExist = true;
        }
    }


    public function __invoke(string $table): void
    {
        if ('FE' == TL_MODE || ! $this->languageRelationBundleExist) {
            return;
        }

        if ('tl_post' === $table)
        {
            $GLOBALS['TL_DCA']['tl_posts']['config']['sql']['keys']['languageMain'] = 'index';

            $GLOBALS['TL_DCA']['tl_posts']['config']['onload_callback'][] = array(self::class, 'showLanguageMain');

            $GLOBALS['TL_DCA']['tl_posts']['fields']['languageMain'] = array
            (
                'label'                   => &$GLOBALS['TL_LANG']['tl_posts']['languageMain'],
                'exclude'                 => true,
                'inputType'               => 'postTree',
                'eval'                    => array('fieldType'=>'radio', 'multiple'=>false, 'rootNodes'=>[0], 'doNotCopy'=>true, 'tl_class'=>'w50 clr'),
                'sql'                     => "int(10) unsigned NOT NULL default '0'",
                'load_callback'           => [[self::class, 'onLoadLanguageMain']],
                'save_callback'           => [[self::class, 'onSaveLanguageMain']],
            );

        }
    }

    /**
     * Show the languageMain field depending on the current posts root node
     *
     * @param ChangelanguageNavigationEvent $event
     */
    function showLanguageMain($dc)
    {
        $post = PostModel::findById($dc->id);

        if (null === $post) {
            return;
        }

        $archive = ArchiveModel::findById($post->pid);
        $page = PageModel::findWithDetails($archive->pid);

        if (!$page->rootIsFallback) {
            $GLOBALS['TL_DCA']['tl_posts']['palettes']['default'] = preg_replace(
                '/({title_legend}.*?);/',
                '$0{language_legend},languageMain;',
                $GLOBALS['TL_DCA']['tl_posts']['palettes']['default']);
        }
    }


    /**
     * Sets rootNodes when initializing the languageMain field.
     *
     * @param mixed         $value
     * @param DataContainer $dc
     *
     * @return mixed
     */
    public function onLoadLanguageMain($value, $dc)
    {
        if (!$dc->id || 'posts' !== \Input::get('do')) {
            return $value;
        }

        $post = PostModel::findById($dc->id);

        if (null === $post) {
            return $value;
        }

        $archive = ArchiveModel::findById($post->pid);
        $page = PageModel::findById($archive->pid);

        $pageFinder = new PageFinder();
        $associated = $pageFinder->findAssociatedInMaster($page);

        $GLOBALS['TL_DCA']['tl_posts']['fields']['languageMain']['eval']['rootNodes'] = [$associated->id];

        return $value;
    }


    /**
     * Validate input value when saving tl_page.languageMain field.
     *
     * @param mixed         $value
     * @param DataContainer $dc
     *
     * @throws \RuntimeException
     *
     * @return mixed
     */
    public function onSaveLanguageMain($value, $dc)
    {
        if ($value > 0) {
            $post = PostModel::findById($dc->id);

            if (null === $post) {
                return $value;
            }

            $archive = ArchiveModel::findById($post->pid);
            $archives = ArchiveModel::findByPid($archive->pid)->fetchEach('id');

            $duplicates = PostModel::countBy(
                ['tl_posts.pid IN (' . implode(',', $archives) . ')', 'tl_posts.languageMain=?','tl_posts.id!=?'],
                [$value, $dc->id]
            );

            if ($duplicates > 0) {
                throw new \RuntimeException($GLOBALS['TL_LANG']['MSC']['duplicateMainLanguage']);
            }
        }
        return $value;
    }
}
