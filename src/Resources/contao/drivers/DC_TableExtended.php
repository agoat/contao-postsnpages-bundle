<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao;

use Contao\CoreBundle\Exception\AccessDeniedException;
use Contao\CoreBundle\Exception\InternalServerErrorException;
use Contao\CoreBundle\Exception\ResponseException;
use Contao\CoreBundle\Picker\PickerInterface;
use Patchwork\Utf8;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;


/**
 * Provide extended methods to modify the database.
 *
 * @property integer $id
 * @property string  $parentTable
 * @property array   $childTable
 * @property boolean $createNewVersion
 *
 * @author Arne Stappen (alias aGoat) <https://github.com/agoat>
 */
class DC_TableExtended extends \DC_Table implements \listable, \editable
{
	/**
	 * List all records of the current table as tree and return them as HTML string
	 *
	 * @return string
	 */
	protected function treeView()
	{
		$table = $this->strTable;
		$treeClass = 'tl_tree';

		if ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] == 6)
		{
			$table = $this->ptable;
			$treeClass = 'tl_tree_xtnd';

			\System::loadLanguageFile($table);
			$this->loadDataContainer($table);
		}

		/** @var SessionInterface $objSession */
		$objSession = \System::getContainer()->get('session');

		/** @var AttributeBagInterface $objSessionBag */
		$objSessionBag = $objSession->getBag('contao_backend');

		$session = $objSessionBag->all();

		// Toggle the nodes
		if (\Input::get('ptg') == 'all')
		{
			$node = ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] == 6) ? $this->strTable.'_'.$table.'_tree' : $this->strTable.'_tree';

			// Expand tree
			if (!is_array($session[$node]) || empty($session[$node]) || current($session[$node]) != 1)
			{
				$session[$node] = array();
				$objNodes = $this->Database->execute("SELECT DISTINCT id FROM " . $table);

				while ($objNodes->next())
				{
					$session[$node][$objNodes->id] = 1;
				}
			}

			// Collapse tree
			else
			{
				$session[$node] = array();
			}

			$objSessionBag->replace($session);
			$this->redirect(preg_replace('/(&(amp;)?|\?)ptg=[^& ]*/i', '', \Environment::get('request')));
		}

		// Return if a mandatory field (id, pid, sorting) is missing
		if ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] == 5 && (!$this->Database->fieldExists('id', $table) || !$this->Database->fieldExists('pid', $table) || !$this->Database->fieldExists('sorting', $table)))
		{
			return '
<p class="tl_empty">Table "'.$table.'" can not be shown as tree, because the "id", "pid" or "sorting" field is missing!</p>';
		}

		// Return if there is no parent table
		if ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] == 6 && !strlen($this->ptable))
		{
			return '
<p class="tl_empty">Table "'.$table.'" can not be shown as extended tree, because there is no parent table!</p>';
		}

		$blnClipboard = false;
		$arrClipboard = $objSession->get('CLIPBOARD');

		// Check the clipboard
		if (!empty($arrClipboard[$this->strTable]))
		{
			$blnClipboard = true;
			$arrClipboard = $arrClipboard[$this->strTable];
		}

		$label = $GLOBALS['TL_DCA'][$table]['config']['label'];
		$icon = $GLOBALS['TL_DCA'][$table]['list']['sorting']['icon'] ?: 'pagemounts.svg';
		$label = \Image::getHtml($icon).' <label>'.$label.'</label>';

		// Begin buttons container
		$return = \Message::generate() . '
<div id="tl_buttons">'.((\Input::get('act') == 'select') ? '
<a href="'.$this->getReferer(true).'" class="header_back" title="'.\StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['backBTTitle']).'" accesskey="b" onclick="Backend.getScrollOffset()">'.$GLOBALS['TL_LANG']['MSC']['backBT'].'</a> ' : (isset($GLOBALS['TL_DCA'][$this->strTable]['config']['backlink']) ? '
<a href="contao/main.php?'.$GLOBALS['TL_DCA'][$this->strTable]['config']['backlink'].'" class="header_back" title="'.\StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['backBTTitle']).'" accesskey="b" onclick="Backend.getScrollOffset()">'.$GLOBALS['TL_LANG']['MSC']['backBT'].'</a> ' : '')) . ((\Input::get('act') != 'select' && !$blnClipboard && !$GLOBALS['TL_DCA'][$this->strTable]['config']['closed'] && !$GLOBALS['TL_DCA'][$this->strTable]['config']['notCreatable']) ? '
<a href="'.$this->addToUrl('act=paste&amp;mode=create').'" class="header_new" title="'.\StringUtil::specialchars($GLOBALS['TL_LANG'][$this->strTable]['new'][1]).'" accesskey="n" onclick="Backend.getScrollOffset()">'.$GLOBALS['TL_LANG'][$this->strTable]['new'][0].'</a> ' : '') . ($blnClipboard ? '
<a href="'.$this->addToUrl('clipboard=1').'" class="header_clipboard" title="'.\StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['clearClipboard']).'" accesskey="x">'.$GLOBALS['TL_LANG']['MSC']['clearClipboard'].'</a> ' : $this->generateGlobalButtons()) . '
</div>';

		$tree = '';
		$blnHasSorting = $this->Database->fieldExists('sorting', $table);
		$strFound = '';

		if (!empty($this->procedure))
		{
			$fld = ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] == 6) ? 'pid' : 'id';

			$objRoot = $this->Database->prepare("SELECT DISTINCT $fld FROM {$this->strTable} WHERE " . implode(' AND ', $this->procedure))
									  ->execute($this->values);

			if ($objRoot->numRows < 1)
			{
				$this->root = array();
			}
			else
			{
				// Respect existing limitations (root IDs)
				if (!empty($this->root))
				{
					$arrRoot = array();

					while ($objRoot->next())
					{
						$arrRoot = array_merge($arrRoot, $this->Database->getParentRecords($objRoot->$fld, $table));
					}

					$arrFound = $arrRoot;
					$this->root = $this->eliminateNestedPages($arrFound, $table, $blnHasSorting);
				}
				else
				{
					$arrFound = $objRoot->fetchEach($fld);
					$this->root = $this->eliminateNestedPages($arrFound, $table, $blnHasSorting);
				}

				$strFound = implode(',', array_map('intval', $arrRoot));
			}
		}

		$strPFilter = '';
		
		if (!empty($filter = $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['pfilter']) && is_array($filter) && $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] == 6)
		{
			$objPFilter = $this->Database->prepare("SELECT DISTINCT id FROM ". $table . " WHERE " . $filter[0])
										 ->execute($filter[1]);

			if ($objPFilter->numRows)
			{
				$arrPFilter = array();
			
				while ($objPFilter->next())
				{
					$arrPFilter = array_merge($arrPFilter, $this->Database->getParentRecords($objPFilter->id, $table));
				}

				$arrPFilter = array_unique($arrPFilter);
				$strPFilter = implode(',', array_map('intval', $arrPFilter));
			}
		}

		if (!empty($this->root))
		{
			$objRoots = $this->Database->query("SELECT * FROM " . $table . " WHERE id IN (" . implode(',', $this->root) . ")" . ($this->Database->fieldExists('sorting', $table) ? ' ORDER BY sorting' : ''));

			// Call a recursive function that builds the tree
			while ($objRoots->next())
			{
				$tree .= $this->generateXTree($table, $objRoots->id, $objRoots->row(), array('p'=>$this->root[($count-1)], 'n'=>$arrIds[($count+1)]), $blnHasSorting, 0, ($blnClipboard ? $arrClipboard : false), ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] == 5 && $blnClipboard && $objRoots->id == $arrClipboard['id']), false, false, $strFound, $strPFilter);
				$count++;
			}

		}

		// Return if there are no records
		if ($tree == '' && \Input::get('act') != 'paste')
		{
			return $return . '
<p class="tl_empty">'.$GLOBALS['TL_LANG']['MSC']['noResult'].'</p>';
		}

		$return .= ((\Input::get('act') == 'select') ? '
<form action="'.ampersand(\Environment::get('request'), true).'" id="tl_select" class="tl_form'.((\Input::get('act') == 'select') ? ' unselectable' : '').'" method="post" novalidate>
<div class="tl_formbody_edit">
<input type="hidden" name="FORM_SUBMIT" value="tl_select">
<input type="hidden" name="REQUEST_TOKEN" value="'.REQUEST_TOKEN.'">' : '').($blnClipboard ? '
<div id="paste_hint">
  <p>'.$GLOBALS['TL_LANG']['MSC']['selectNewPosition'].'</p>
</div>' : '').'
<div class="tl_listing_container tree_view" id="tl_listing">'.(isset($GLOBALS['TL_DCA'][$table]['list']['sorting']['breadcrumb']) ? $GLOBALS['TL_DCA'][$table]['list']['sorting']['breadcrumb'] : '').((\Input::get('act') == 'select' || ($this->strPickerFieldType == 'checkbox')) ? '
<div class="tl_select_trigger">
<label for="tl_select_trigger" class="tl_select_label">'.$GLOBALS['TL_LANG']['MSC']['selectAll'].'</label> <input type="checkbox" id="tl_select_trigger" onclick="Backend.toggleCheckboxes(this)" class="tl_tree_checkbox">
</div>' : '').'
<ul class="tl_listing '.$treeClass.($this->strPickerFieldType ? ' picker unselectable' : '').'">
  <li class="tl_folder_top cf"><div class="tl_left">'.$label.'</div> <div class="tl_right">';

		$_buttons = '&nbsp;';

		// Show paste button only if there are no root records specified
		if (\Input::get('act') != 'select' && $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] == 5 && $blnClipboard && ((!count($GLOBALS['TL_DCA'][$table]['list']['sorting']['root']) && $GLOBALS['TL_DCA'][$table]['list']['sorting']['root'] !== false) || $GLOBALS['TL_DCA'][$table]['list']['sorting']['rootPaste']))
		{
			// Call paste_button_callback (&$dc, $row, $table, $cr, $childs, $previous, $next)
			if (is_array($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['paste_button_callback']))
			{
				$strClass = $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['paste_button_callback'][0];
				$strMethod = $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['paste_button_callback'][1];

				$this->import($strClass);
				$_buttons = $this->$strClass->$strMethod($this, array('id'=>0), $table, false, $arrClipboard);
			}
			elseif (is_callable($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['paste_button_callback']))
			{
				$_buttons = $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['paste_button_callback']($this, array('id'=>0), $table, false, $arrClipboard);
			}
			else
			{
				$imagePasteInto = \Image::getHtml('pasteinto.svg', $GLOBALS['TL_LANG'][$this->strTable]['pasteinto'][0]);
				$_buttons = '<a href="'.$this->addToUrl('act='.$arrClipboard['mode'].'&amp;mode=2&amp;pid=0'.(!is_array($arrClipboard['id']) ? '&amp;id='.$arrClipboard['id'] : '')).'" title="'.\StringUtil::specialchars($GLOBALS['TL_LANG'][$this->strTable]['pasteinto'][0]).'" onclick="Backend.getScrollOffset()">'.$imagePasteInto.'</a> ';
			}
		}

		// End table
		$return .= $_buttons . '</div></li>'.$tree.'
</ul>'.($this->strPickerFieldType == 'radio' ? '
<div class="tl_radio_reset">
<label for="tl_radio_reset" class="tl_radio_label">'.$GLOBALS['TL_LANG']['MSC']['resetSelected'].'</label> <input type="radio" name="picker" id="tl_radio_reset" value="" class="tl_tree_radio">
</div>' : '').'
</div>';

		// Close the form
		if (\Input::get('act') == 'select')
		{
			// Submit buttons
			$arrButtons = array();

			if (!$GLOBALS['TL_DCA'][$this->strTable]['config']['notEditable'])
			{
				$arrButtons['edit'] = '<button type="submit" name="edit" id="edit" class="tl_submit" accesskey="s">'.$GLOBALS['TL_LANG']['MSC']['editSelected'].'</button>';
			}

			if (!$GLOBALS['TL_DCA'][$this->strTable]['config']['notDeletable'])
			{
				$arrButtons['delete'] = '<button type="submit" name="delete" id="delete" class="tl_submit" accesskey="d" onclick="return confirm(\''.$GLOBALS['TL_LANG']['MSC']['delAllConfirm'].'\')">'.$GLOBALS['TL_LANG']['MSC']['deleteSelected'].'</button>';
			}

			if (!$GLOBALS['TL_DCA'][$this->strTable]['config']['notCopyable'])
			{
				$arrButtons['copy'] = '<button type="submit" name="copy" id="copy" class="tl_submit" accesskey="c">'.$GLOBALS['TL_LANG']['MSC']['copySelected'].'</button>';
			}

			if (!$GLOBALS['TL_DCA'][$this->strTable]['config']['notSortable'])
			{
				$arrButtons['cut'] = '<button type="submit" name="cut" id="cut" class="tl_submit" accesskey="x">'.$GLOBALS['TL_LANG']['MSC']['moveSelected'].'</button>';
			}

			if (!$GLOBALS['TL_DCA'][$this->strTable]['config']['notEditable'])
			{
				$arrButtons['override'] = '<button type="submit" name="override" id="override" class="tl_submit" accesskey="v">'.$GLOBALS['TL_LANG']['MSC']['overrideSelected'].'</button>';
			}

			// Call the buttons_callback (see #4691)
			if (is_array($GLOBALS['TL_DCA'][$this->strTable]['select']['buttons_callback']))
			{
				foreach ($GLOBALS['TL_DCA'][$this->strTable]['select']['buttons_callback'] as $callback)
				{
					if (is_array($callback))
					{
						$this->import($callback[0]);
						$arrButtons = $this->{$callback[0]}->{$callback[1]}($arrButtons, $this);
					}
					elseif (is_callable($callback))
					{
						$arrButtons = $callback($arrButtons, $this);
					}
				}
			}

			if (count($arrButtons) < 3)
			{
				$strButtons = implode(' ', $arrButtons);
			}
			else
			{
				$strButtons = array_shift($arrButtons) . ' ';
				$strButtons .= '<div class="split-button">';
				$strButtons .= array_shift($arrButtons) . '<button type="button" id="sbtog">' . \Image::getHtml('navcol.svg') . '</button> <ul class="invisible">';

				foreach ($arrButtons as $strButton)
				{
					$strButtons .= '<li>' . $strButton . '</li>';
				}

				$strButtons .= '</ul></div>';
			}

			$return .= '
</div>
<div class="tl_formbody_submit" style="text-align:right">
<div class="tl_submit_container">
  ' . $strButtons . '
</div>
</div>
</form>';
		}

		return $return;
	}


	/**
	 * Generate a particular subpart of the tree and return it as HTML string
	 *
	 * @param integer $id
	 * @param integer $level
	 *
	 * @return string
	 */
	public function ajaxTreeView($id, $level)
	{
		if (!\Environment::get('isAjaxRequest'))
		{
			return '';
		}

		$return = '';
		$table = $this->strTable;
		$blnPtable = false;

		// Load parent table
		if ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] == 6)
		{
			$table = $this->ptable;

			\System::loadLanguageFile($table);
			$this->loadDataContainer($table);

			$blnPtable = true;
		}

		$blnProtected = false;

		// Check protected pages
		if ($table == 'tl_page')
		{
			$objParent = \PageModel::findWithDetails($id);
			$blnProtected = $objParent->protected ? true : false;
		}

		/** @var SessionInterface $objSession */
		$objSession = \System::getContainer()->get('session');

		$blnClipboard = false;
		$arrClipboard = $objSession->get('CLIPBOARD');

		// Check clipboard
		if (!empty($arrClipboard[$this->strTable]))
		{
			$blnClipboard = true;
			$arrClipboard = $arrClipboard[$this->strTable];
		}

		$hasSorting = $this->Database->fieldExists('sorting', $table);
		$intCount = 0;

		$return .= ' ' . trim($this->generateXTree($table, $id, false, array('p'=>$childs[($count-1)], 'n'=>$childs[($count+1)]), $hasSorting, $level, ($blnClipboard ? $arrClipboard : false), ($id == $arrClipboard ['id'] || (is_array($arrClipboard ['id']) && in_array($id, $arrClipboard ['id'])) || (!$blnPtable && !is_array($arrClipboard['id']) && in_array($id, $this->Database->getChildRecords($arrClipboard['id'], $table)))), $blnProtected, false, array(), $intCount));

		return $return;
	}


	/**
	 * Recursively generate the tree and return it as HTML string
	 *
	 * @param string  $table
	 * @param integer $id
	 * @param array   $arrRow
	 * @param array   $arrPrevNext
	 * @param boolean $blnHasSorting
	 * @param integer $level
	 * @param array   $arrClipboard
	 * @param boolean $blnCircularReference
	 * @param boolean $protectedPage
	 * @param boolean $blnNoRecursion
	 * @param string  $strFound
	 * @param string  $strPFilter
	 *
	 * @return string
	 */
	protected function generateXTree($table, $id, $arrRow, $arrPrevNext, $blnHasSorting, $level=0, $arrClipboard=null, $blnCircularReference=false, $protectedPage=false, $blnNoRecursion=false, $strFound='', $strPFilter='')
	{
		static $session;

		/** @var AttributeBagInterface $objSessionBag */
		$objSessionBag = \System::getContainer()->get('session')->getBag('contao_backend');

		$session = $objSessionBag->all();
		$node = ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] == 6) ? $this->strTable.'_'.$table.'_tree' : $this->strTable.'_tree';
	
		// Toggle nodes
		if (\Input::get('ptg'))
		{
			$session[$node][\Input::get('ptg')] = (isset($session[$node][\Input::get('ptg')]) && $session[$node][\Input::get('ptg')] == 1) ? 0 : 1;
			$objSessionBag->replace($session);
			$this->redirect(preg_replace('/(&(amp;)?|\?)ptg=[^& ]*/i', '', \Environment::get('request')));
		}
		
		$return = '';
		$intSpacing = 20;
		$blnIsOpen = (!empty($arrFound) || $session[$node][$id] == 1);

		$childs = array();
		$subChilds = array();

		// Add the ID to the list of current IDs
		if ($this->strTable == $table && is_numeric($id))
		{
			$this->current[] = $id;
		}

		// Check whether there are child records
		if (!$blnNoRecursion)
		{
			// Get the table child records
			if ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] == 5 || $this->strTable != $table)
			{
				$query = "SELECT * FROM " . $table . " WHERE pid=?";
				$varValues = array($id);
				
				if (!empty($strFound))
				{
					$query .= " AND id IN(" . $strFound . ")";
				}
			
				if (!empty($strPFilter))
				{
					$query .= " AND id IN (" . $strPFilter . " )";
				}

				if ($blnHasSorting)
				{
					$query .= " ORDER BY sorting";
				}

				$objChilds = $this->Database->prepare($query)
											->execute($varValues);
													
				if ($objChilds->numRows)
				{
					$childs = $objChilds->fetchEach('id');
					$objChilds->reset();
				}
			}

			// Get the child records of the table itself
			if ($this->strTable != $table)
			{
				if (is_array($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['fields']) && strlen($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['fields'][0]))
				{
					$arrOrder = $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['fields'];
				}
				elseif ($blnHasSorting)
				{
					$arrOrder = array('sorting');
				}
				else
				{
					$arrOrder = array();
				}
				
				if (strlen($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['group']))
				{
					$groupField = $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['group'];
					$flag = !empty($GLOBALS['TL_DCA'][$this->strTable]['fields'][$groupField]['flag']) ? $GLOBALS['TL_DCA'][$this->strTable]['fields'][$groupField]['flag'] : 11;
					array_unshift($arrOrder, $groupField . (($flag % 2) == 0 ? ' DESC' : ''));
				}
	
				// Also apply the filter settings to the child table (see #716)
				if ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] == 6 && !empty($this->procedure))
				{
					$arrValues = $this->values;
					array_unshift($arrValues, $id);

					$objSubChilds = $this->Database->prepare("SELECT * FROM " . $this->strTable . " WHERE pid=? AND " . (implode(' AND ', $this->procedure)) . (!empty($arrOrder) ? " ORDER BY " . implode(',', $arrOrder) : ''))
												   ->execute($arrValues);
				}
				else
				{
					$objSubChilds = $this->Database->prepare("SELECT * FROM " . $this->strTable . " WHERE pid=?" . (!empty($arrOrder) ? " ORDER BY " . implode(',', $arrOrder) : ''))
												   ->execute($id);
				}
		
				if ($objSubChilds->numRows)
				{
					$subChilds = $objSubChilds->fetchEach('id');
					$objSubChilds->reset();
				}

			}
		}

		if ($arrRow !== false)
		{
			$blnProtected = false;

			// Check whether the page is protected
			if ($table == 'tl_page')
			{
				$blnProtected = ($arrRow['protected'] || $protectedPage) ? true : false;
			}

			$session[$node][$id] = (is_int($session[$node][$id])) ? $session[$node][$id] : 0;
			$mouseover = ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] == 5 || ($table == $this->strTable && is_numeric($id))) ? ' toggle_select hover-div' : '';

			
			$return .= "\n  " . '<li class="'.(($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] == 5 && (!empty($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['folders']) && in_array($arrRow['type'], (array)$GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['folders'])) || $table != $this->strTable) ? 'tl_folder' : 'tl_file').' click2edit'.$mouseover.' cf"><div class="tl_left" style="padding-left:'.($level * $intSpacing).'px">';

			// Calculate label and add a toggle button
			$args = array();
			$folderAttribute = 'style="margin-left:20px"';
			$showFields = $GLOBALS['TL_DCA'][$table]['list']['label']['fields'];

			// Always show selected nodes
			if (!$blnIsOpen && !empty($this->arrPickerValue) && !empty(array_intersect($this->Database->getChildRecords([$id], $this->strTable), $this->arrPickerValue)))
			{
				$blnIsOpen = true;
			}

			if ($objChilds->numRows || $objSubChilds->numRows)
			{
				$folderAttribute = '';
				$img = $blnIsOpen ? 'folMinus.svg' : 'folPlus.svg';
				$alt = $blnIsOpen ? $GLOBALS['TL_LANG']['MSC']['collapseNode'] : $GLOBALS['TL_LANG']['MSC']['expandNode'];
				$return .= '<a href="'.$this->addToUrl('ptg='.$id).'" title="'.\StringUtil::specialchars($alt).'" onclick="Backend.getScrollOffset();return AjaxRequest.toggleStructure(this,\''.$node.'_'.$id.'\','.$level.','.$GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'].')">'.\Image::getHtml($img, '', 'style="margin-right:2px"').'</a>';
			}

			foreach ($showFields as $k=>$v)
			{
				// Decrypt the value
				if ($GLOBALS['TL_DCA'][$table]['fields'][$v]['eval']['encrypt'])
				{
					$arrRow[$v] = \Encryption::decrypt(\StringUtil::deserialize($arrRow[$v]));
				}

				if (strpos($v, ':') !== false)
				{
					list($strKey, $strTable) = explode(':', $v);
					list($strTable, $strField) = explode('.', $strTable);

					$objRef = $this->Database->prepare("SELECT " . $strField . " FROM " . $strTable . " WHERE id=?")
											 ->limit(1)
											 ->execute($arrRow[$strKey]);

					$args[$k] = $objRef->numRows ? $objRef->$strField : '';
				}
				elseif (in_array($GLOBALS['TL_DCA'][$table]['fields'][$v]['flag'], array(5, 6, 7, 8, 9, 10)))
				{
					$args[$k] = \Date::parse(\Config::get('datimFormat'), $arrRow[$v]);
				}
				elseif ($GLOBALS['TL_DCA'][$table]['fields'][$v]['inputType'] == 'checkbox' && !$GLOBALS['TL_DCA'][$table]['fields'][$v]['eval']['multiple'])
				{
					$args[$k] = ($arrRow[$v] != '') ? (isset($GLOBALS['TL_DCA'][$table]['fields'][$v]['label'][0]) ? $GLOBALS['TL_DCA'][$table]['fields'][$v]['label'][0] : $v) : '';
				}
				else
				{
					$args[$k] = $GLOBALS['TL_DCA'][$table]['fields'][$v]['reference'][$arrRow[$v]] ?: $arrRow[$v];
				}
			}

			$label = vsprintf(strlen($GLOBALS['TL_DCA'][$table]['list']['label']['format']) ? $GLOBALS['TL_DCA'][$table]['list']['label']['format'] : '%s', $args);

			// Shorten the label if it is too long
			if ($GLOBALS['TL_DCA'][$table]['list']['label']['maxCharacters'] > 0 && $GLOBALS['TL_DCA'][$table]['list']['label']['maxCharacters'] < Utf8::strlen(strip_tags($label)))
			{
				$label = trim(\StringUtil::substrHtml($label, $GLOBALS['TL_DCA'][$table]['list']['label']['maxCharacters'])) . ' …';
			}

			$label = preg_replace('/\(\) ?|\[\] ?|\{\} ?|<> ?/', '', $label);

			// Call the label_callback ($row, $label, $this)
			if (is_array($GLOBALS['TL_DCA'][$table]['list']['label']['label_callback']))
			{
				$strClass = $GLOBALS['TL_DCA'][$table]['list']['label']['label_callback'][0];
				$strMethod = $GLOBALS['TL_DCA'][$table]['list']['label']['label_callback'][1];

				$this->import($strClass);
				$return .= $this->$strClass->$strMethod($arrRow, $label, $this, $folderAttribute, false, $blnProtected);
			}
			elseif (is_callable($GLOBALS['TL_DCA'][$table]['list']['label']['label_callback']))
			{
				$return .= $GLOBALS['TL_DCA'][$table]['list']['label']['label_callback']($arrRow, $label, $this, $folderAttribute, false, $blnProtected);
			}
			else
			{
				$return .= \Image::getHtml('iconPLAIN.svg', '') . ' ' . $label;
			}

			$return .= '</div> <div class="tl_right">';
			$previous = ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] == 6) ? $arrPrevNext['pp'] : $arrPrevNext['p'];
			$next = ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] == 6) ? $arrPrevNext['nn'] : $arrPrevNext['n'];
			$_buttons = '';

			// Regular buttons ($row, $table, $root, $blnCircularReference, $childs, $previous, $next)
			if ($this->strTable == $table)
			{
				$_buttons .= (\Input::get('act') == 'select') ? '<input type="checkbox" name="IDS[]" id="ids_'.$id.'" class="tl_tree_checkbox" value="'.$id.'">' : $this->generateButtons($arrRow, $table, $this->root, $blnCircularReference, $childs, $previous, $next);

				if ($this->strPickerFieldType)
				{
					$_buttons .= $this->getPickerInputField($id, (isset($this->blnFilesOnly) && $this->blnFilesOnly && $objChilds->numRows) ? ' disabled' : '');
				}
			}

			// Paste buttons
			if ($arrClipboard !== false && \Input::get('act') != 'select')
			{
				$_buttons .= ' ';

				// Call paste_button_callback(&$dc, $row, $table, $blnCircularReference, $arrClipboard, $childs, $previous, $next)
				if (is_array($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['paste_button_callback']))
				{
					$strClass = $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['paste_button_callback'][0];
					$strMethod = $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['paste_button_callback'][1];

					$this->import($strClass);
					$_buttons .= $this->$strClass->$strMethod($this, $arrRow, $table, $blnCircularReference, $arrClipboard, $childs, $previous, $next);
				}
				elseif (is_callable($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['paste_button_callback']))
				{
					$_buttons .= $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['paste_button_callback']($this, $arrRow, $table, $blnCircularReference, $arrClipboard, $childs, $previous, $next);
				}
				else
				{
					$imagePasteAfter = \Image::getHtml('pasteafter.svg', sprintf($GLOBALS['TL_LANG'][$this->strTable]['pasteafter'][1], $id));
					$imagePasteInto = \Image::getHtml('pasteinto.svg', sprintf($GLOBALS['TL_LANG'][$this->strTable]['pasteinto'][1], $id));

					// Regular tree (on cut: disable buttons of the page all its childs to avoid circular references)
					if ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] == 5)
					{
						$_buttons .= ($arrClipboard['mode'] == 'cut' && ($blnCircularReference || $arrClipboard['id'] == $id) || $arrClipboard['mode'] == 'cutAll' && ($blnCircularReference || in_array($id, $arrClipboard['id'])) || (!empty($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['root']) && !$GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['rootPaste'] && in_array($id, $this->root))) ? \Image::getHtml('pasteafter_.svg').' ' : '<a href="'.$this->addToUrl('act='.$arrClipboard['mode'].'&amp;mode=1&amp;pid='.$id.(!is_array($arrClipboard['id']) ? '&amp;id='.$arrClipboard['id'] : '')).'" title="'.\StringUtil::specialchars(sprintf($GLOBALS['TL_LANG'][$this->strTable]['pasteafter'][1], $id)).'" onclick="Backend.getScrollOffset()">'.$imagePasteAfter.'</a> ';
						$_buttons .= ($arrClipboard['mode'] == 'cut' && ($blnCircularReference || $arrClipboard['id'] == $id) || $arrClipboard['mode'] == 'cutAll' && ($blnCircularReference || in_array($id, $arrClipboard['id']))) ? \Image::getHtml('pasteinto_.svg').' ' : '<a href="'.$this->addToUrl('act='.$arrClipboard['mode'].'&amp;mode=2&amp;pid='.$id.(!is_array($arrClipboard['id']) ? '&amp;id='.$arrClipboard['id'] : '')).'" title="'.\StringUtil::specialchars(sprintf($GLOBALS['TL_LANG'][$this->strTable]['pasteinto'][1], $id)).'" onclick="Backend.getScrollOffset()">'.$imagePasteInto.'</a> ';
					}

					// Extended tree
					else
					{
						if ($this->strTable == $table)
						{
							$_buttons .=($arrClipboard['mode'] == 'cut' && ($blnCircularReference || $arrClipboard['id'] == $id) || $arrClipboard['mode'] == 'cutAll' && ($blnCircularReference || in_array($id, $arrClipboard['id']))) ? \Image::getHtml('pasteafter_.svg') : '<a href="'.$this->addToUrl('act='.$arrClipboard['mode'].'&amp;mode=1&amp;pid='.$id.(!is_array($arrClipboard['id']) ? '&amp;id='.$arrClipboard['id'] : '')).'" title="'.\StringUtil::specialchars(sprintf($GLOBALS['TL_LANG'][$this->strTable]['pasteafter'][1], $id)).'" onclick="Backend.getScrollOffset()">'.$imagePasteAfter.'</a> ';
						}
						else
						{
							$_buttons .= '<a href="'.$this->addToUrl('act='.$arrClipboard['mode'].'&amp;mode=2&amp;pid='.$id.(!is_array($arrClipboard['id']) ? '&amp;id='.$arrClipboard['id'] : '')).'" title="'.\StringUtil::specialchars(sprintf($GLOBALS['TL_LANG'][$this->strTable]['pasteinto'][1], $id)).'" onclick="Backend.getScrollOffset()">'.$imagePasteInto.'</a> ';
						}
						
						
					}
				}
			}

			$return .= ($_buttons ?: '&nbsp;') . '</div></li>';
		}
		
		// Begin a new submenu
		if (!$blnNoRecursion)
		{
			$subReturn = '';
		
			// Add the records of the parent table
			if ($blnIsOpen && $objChilds->numRows)
			{
				$count = 0;
				
				// Call a recursive function that builds the tree
				while ($objChilds->next())
				{
					$subReturn .= $this->generateXTree($table, $objChilds->id, $objChilds->row(), array('p'=>$childs[($count-1)], 'n'=>$childs[($count+1)]), $blnHasSorting, ($level+1), $arrClipboard, ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] == 5 && $objChilds->id == $arrClipboard['id']), false, false, $strFound, $strPFilter);
					$count++;
				}
			}
			
			// Add the records of the table itself
			if ($table != $this->strTable && $blnIsOpen && $objSubChilds->numRows)
			{
				$count = 0;
				$group = false;
			
				// Call a recursive function that builds the tree
				while ($objSubChilds->next())
				{
					// Begin a new group
					if (strlen($groupField) && $group != $objSubChilds->$groupField)
					{
						// Close the group
						if ($group)
						{
							$subReturn .= '</ul></li>';
						}
						
						$group = $objSubChilds->$groupField;

						$sortingMode  = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$groupField]['flag'] ? $GLOBALS['TL_DCA'][$this->strTable]['fields'][$groupField]['flag'] : 11;
						$groupValue = $this->formatCurrentValue($groupField, $group, $sortingMode);
						$groupLabel = $this->formatGroupHeader($groupField, $groupValue, $sortingMode, $objSubChilds->row());

						$subReturn .= "\n  " . '<li class="tl_group click2edit cf"><div class="tl_left" style="padding-left:'.(($level+2) * $intSpacing).'px">';
						$subReturn .= \Image::getHtml('iconPLAIN.svg', '') . ' ' . $groupLabel;
						$subReturn .= '</div><li class="parent"><ul class="level_'.($level+1).'">';
					}

					$subReturn .= $this->generateXTree($this->strTable, $objSubChilds->id,  $objSubChilds->row(), array('pp'=>$subChilds[($count-1)], 'nn'=>$subChilds[($count+1)]), $blnHasSorting, ($level+(($groupField) ? 3 : 2)), $arrClipboard, false, ($count<(count($subChilds)-1) || !empty($subChilds)), $blnNoRecursion, $strFound, $strPFilter);
			
					$count++;
				}
				
				// Close the group
				if ($group)
				{
					$subReturn .= '</ul></li>';
				}
			}

			// Add child records
			if ($subReturn != '')
			{
				$return .= '<li class="parent" id="'.$node.'_'.$id.'"><ul class="level_'.$level.'">'.$subReturn.'</ul></li>';
			}
		}

		$objSessionBag->replace($session);
		
		return $return;
	}
	
	
	/**
	 * {@inheritdoc}
	 */
	public function initPicker(PickerInterface $picker)
	{
		$attributes = parent::initPicker($picker);
	
		if (isset($attributes['filesOnly']))
		{
			$this->blnFilesOnly = $attributes['filesOnly'];
		}
		
		return $attributes;
	}
}
