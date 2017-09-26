<?php
 
 /**
 * Contao Posts'n'Pages extension
 *
 * Copyright (c) 2017 Arne Stappen (aGoat)
 *
 *
 * @package   contentblocks
 * @author    Arne Stappen <http://agoat.de>
 * @license	  LGPL-3.0+
 */

namespace Agoat\PostsnPages;
 
use Contao\CoreBundle\Exception\ResponseException;
use Symfony\Component\HttpFoundation\Response;


class Ajax extends \Backend
{

	/**
	 * Handle multi- and subpattern AJAX requests
	 */
	public function postActions ($strAction, $dc)
	{
		switch ($strAction)
		{
			case 'reloadArchivetree':
			case 'reloadPosttree':
			case 'reloadStatictree':
				$intId = \Input::get('id');
				$strField = $dc->inputName = \Input::post('name');

				// Handle the keys in "edit multiple" mode
				if (\Input::get('act') == 'editAll')
				{
					$intId = preg_replace('/.*_([0-9a-zA-Z]+)$/', '$1', $strField);
					$strField = preg_replace('/(.*)_[0-9a-zA-Z]+$/', '$1', $strField);
				}

				$dc->field = $strField;

				// The field does not exist
				if (!isset($GLOBALS['TL_DCA'][$dc->table]['fields'][$strField]))
				{
					$this->log('Field "' . $strField . '" does not exist in DCA "' . $dc->table . '"', __METHOD__, TL_ERROR);
					throw new BadRequestHttpException('Bad request');
				}

				$objRow = null;
				$varValue = null;

				// Load the value
				if (\Input::get('act') != 'overrideAll')
				{
					if ($GLOBALS['TL_DCA'][$dc->table]['config']['dataContainer'] == 'File')
					{
						$varValue = \Config::get($strField);
					}
					elseif ($intId > 0 && $this->Database->tableExists($dc->table))
					{
						$objRow = $this->Database->prepare("SELECT * FROM " . $dc->table . " WHERE id=?")
												 ->execute($intId);

						// The record does not exist
						if ($objRow->numRows < 1)
						{
							$this->log('A record with the ID "' . $intId . '" does not exist in table "' . $dc->table . '"', __METHOD__, TL_ERROR);
							throw new BadRequestHttpException('Bad request');
						}

						$varValue = $objRow->$strField;
						$dc->activeRecord = $objRow;
					}
				}

				// Call the load_callback
				if (is_array($GLOBALS['TL_DCA'][$dc->table]['fields'][$strField]['load_callback']))
				{
					foreach ($GLOBALS['TL_DCA'][$dc->table]['fields'][$strField]['load_callback'] as $callback)
					{
						if (is_array($callback))
						{
							$this->import($callback[0]);
							$varValue = $this->{$callback[0]}->{$callback[1]}($varValue, $dc);
						}
						elseif (is_callable($callback))
						{
							$varValue = $callback($varValue, $dc);
						}
					}
				}

				// Set the new value
				$varValue = \Input::post('value', true);

				switch ($strAction)
				{
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
				if ($varValue != '')
				{
					$varValue = \StringUtil::trimsplit("\t", $varValue);

					// Automatically add resources to the DBAFS
					if ($strKey == 'fileTree')
					{
						foreach ($varValue as $k=>$v)
						{
							$v = rawurldecode($v);

							if (\Dbafs::shouldBeSynchronized($v))
							{
								$objFile = \FilesModel::findByPath($v);

								if ($objFile === null)
								{
									$objFile = \Dbafs::addResource($v);
								}

								$varValue[$k] = $objFile->uuid;
							}
						}
					}

					$varValue = serialize($varValue);
				}

				/** @var FileTree|PageTree|ArticleTree $strClass */
				$strClass = $GLOBALS['BE_FFL'][$strKey];

				/** @var FileTree|PageTree $objWidget */
				$objWidget = new $strClass($strClass::getAttributesFromDca($GLOBALS['TL_DCA'][$dc->table]['fields'][$strField], $dc->inputName, $varValue, $strField, $dc->table, $dc));

				throw new ResponseException($this->convertToResponse($objWidget->generate()));
		}
	}

	/**
	 * Convert a string to a response object
	 *
	 * @param string $str
	 *
	 * @return Response
	 */
	protected function convertToResponse($str)
	{
		return new Response(\Controller::replaceOldBePaths($str));
	}
	
}
