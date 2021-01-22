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

use Agoat\PostsnPagesBundle\Contao\ArchiveTree;
use Agoat\PostsnPagesBundle\Contao\PostTree;
use Agoat\PostsnPagesBundle\Contao\StaticTree;
use Contao\Config;
use Contao\CoreBundle\Exception\ResponseException;
use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\Database;
use Contao\DataContainer;
use Contao\Dbafs;
use Contao\Environment;
use Contao\FilesModel;
use Contao\Input;
use Contao\StringUtil;
use Contao\System;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @Hook("executePostActions")
 */
class PostActionsListener
{

    /**
     * @var Database
     */
    private $database;


    /**
     * PostActionsListener constructor.
     */
    public function __construct()
    {
        $this->database = Database::getInstance();
    }


    public function __invoke(string $action, DataContainer $dc): void
    {
        switch ($action) {
            case 'reloadArchivetree':
            case 'reloadPosttree':
            case 'reloadStatictree':
                $intId = Input::get('id');
                $strField = $dc->inputName = Input::post('name');

                // Handle the keys in "edit multiple" mode
                if (Input::get('act') == 'editAll') {
                    $intId = preg_replace('/.*_([0-9a-zA-Z]+)$/', '$1', $strField);
                    $strField = preg_replace('/(.*)_[0-9a-zA-Z]+$/', '$1', $strField);
                }

                $dc->field = $strField;

                // The field does not exist
                if (!isset($GLOBALS['TL_DCA'][$dc->table]['fields'][$strField])) {
                    $this->log('Field "' . $strField . '" does not exist in DCA "' . $dc->table . '"',
                        __METHOD__,
                        TL_ERROR
                    );
                    throw new BadRequestHttpException('Bad request');
                }

                $objRow = null;
                $varValue = null;

                // Load the value
                if (Input::get('act') != 'overrideAll') {
                    if ($GLOBALS['TL_DCA'][$dc->table]['config']['dataContainer'] == 'File') {
                        $varValue = Config::get($strField);
                    } elseif ($intId > 0 && $this->database->tableExists($dc->table)) {
                        $objRow =
                            $this->database->prepare("SELECT * FROM " . $dc->table . " WHERE id=?")->execute($intId);

                        // The record does not exist
                        if ($objRow->numRows < 1) {
                            $this->log('A record with the ID "' . $intId . '" does not exist in table "' . $dc->table . '"',
                                __METHOD__,
                                TL_ERROR
                            );
                            throw new BadRequestHttpException('Bad request');
                        }

                        $varValue = $objRow->$strField;
                        $dc->activeRecord = $objRow;
                    }
                }

                // Call the load_callback
                if (\is_array($GLOBALS['TL_DCA'][$dc->table]['fields'][$strField]['load_callback'])) {
                    foreach ($GLOBALS['TL_DCA'][$dc->table]['fields'][$strField]['load_callback'] as $callback) {
                        if (\is_array($callback)) {
                            $class = new $callback[0];
                            $varValue = $class->{$callback[1]}($varValue, $dc);
                        } elseif (\is_callable($callback)) {
                            $varValue = $callback($varValue, $dc);
                        }
                    }
                }

                // Set the new value
                $varValue = Input::post('value', true);

                switch ($action) {
                    case 'reloadPosttree';
                        $strKey = 'postTree';
                        break;

                    case 'reloadArchivetree';
                        $strKey = 'archiveTree';
                        break;

                    case 'reloadStatictree';
                        $strKey = 'staticTree';
                        break;
                }

                // Convert the selected values
                if ($varValue != '') {
                    $varValue = StringUtil::trimsplit("\t", $varValue);

                    // Automatically add resources to the DBAFS
                    if ($strKey == 'fileTree') {
                        foreach ($varValue as $k => $v) {
                            $v = rawurldecode($v);

                            if (Dbafs::shouldBeSynchronized($v)) {
                                $objFile = FilesModel::findByPath($v);

                                if ($objFile === null) {
                                    $objFile = Dbafs::addResource($v);
                                }

                                $varValue[$k] = $objFile->uuid;
                            }
                        }
                    }

                    $varValue = serialize($varValue);
                }

                $strClass = $GLOBALS['BE_FFL'][$strKey];

                /** @var PostTree|ArchiveTree|StaticTree $objWidget */
                $objWidget =
                    new $strClass($strClass::getAttributesFromDca($GLOBALS['TL_DCA'][$dc->table]['fields'][$strField],
                        $dc->inputName,
                        $varValue,
                        $strField,
                        $dc->table,
                        $dc
                    )
                    );

                throw new ResponseException(new Response($objWidget->generate()));
        }
    }


    /**
     * Convert a string to a response object
     *
     * @param  string  $str
     *
     * @return Response
     */
    protected function convertToResponse($str)
    {
        // TODO Not Needed anymore ???
        return new Response($this->replaceOldBePaths($str));
    }


    /**
     * Replace the old back end paths
     *
     * @param  string  $strContext  The context
     *
     * @return string The modified context
     */
    protected static function replaceOldBePaths($strContext)
    {
        $router = System::getContainer()->get('router');

        $generate = static function ($route) use ($router) {
            return substr($router->generate($route), \strlen(Environment::get('path')) + 1);
        };

        $arrMapper = [
            'contao/confirm.php'  => $generate('contao_backend_confirm'),
            'contao/file.php'     => $generate('contao_backend_file'),
            'contao/help.php'     => $generate('contao_backend_help'),
            'contao/index.php'    => $generate('contao_backend_login'),
            'contao/main.php'     => $generate('contao_backend'),
            'contao/page.php'     => $generate('contao_backend_page'),
            'contao/password.php' => $generate('contao_backend_password'),
            'contao/popup.php'    => $generate('contao_backend_popup'),
            'contao/preview.php'  => $generate('contao_backend_preview'),
        ];

        return str_replace(array_keys($arrMapper), $arrMapper, $strContext);
    }

}
