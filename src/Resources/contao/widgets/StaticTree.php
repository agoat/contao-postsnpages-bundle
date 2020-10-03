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

namespace Agoat\PostsnPagesBundle\Contao;


use Agoat\PostsnPagesBundle\Model\StaticModel;
use Contao\Database;
use Contao\Image;
use Contao\Input;
use Contao\StringUtil;
use Contao\System;
use Contao\Widget;

/**
 * Provide methods to handle input field "archive tree"
 */
class StaticTree extends Widget
{

	/**
	 * Submit user input
	 * @var boolean
	 */
	protected $blnSubmitInput = true;

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'be_widget';

	/**
	 * Order ID
	 * @var string
	 */
	protected $strOrderId;

	/**
	 * Order name
	 * @var string
	 */
	protected $strOrderName;

    /**
     * @var Database
     */
    private $database;


	/**
	 * Load the database object
	 *
	 * @param array $arrAttributes
	 */
	public function __construct($arrAttributes=null)
	{
        parent::__construct($arrAttributes);

        $this->database = Database::getInstance();

        // Prepare the order field
		if ($this->orderField != '')
		{
			$this->strOrderId = $this->orderField . str_replace($this->strField, '', $this->strId);
			$this->strOrderName = $this->orderField . str_replace($this->strField, '', $this->strName);

			// Retrieve the order value
			$objRow = $this->database->prepare("SELECT {$this->orderField} FROM {$this->strTable} WHERE id=?")
						   ->limit(1)
						   ->execute($this->activeRecord->id);

			$tmp = StringUtil::deserialize($objRow->{$this->orderField});
			$this->{$this->orderField} = (!empty($tmp) && is_array($tmp)) ? array_filter($tmp) : array();
		}
	}


	/**
	 * Return an array if the "multiple" attribute is set
	 *
	 * @param mixed $varInput
	 *
	 * @return mixed
	 */
	protected function validator($varInput)
	{
		$this->checkValue($varInput);

		if ($this->hasErrors())
		{
			return '';
		}

		// Store the order value
		if ($this->orderField != '')
		{
			$arrNew = array();

			if ($order = Input::post($this->strOrderName))
			{
				$arrNew = explode(',', $order);
			}

			// Only proceed if the value has changed
			if ($arrNew !== $this->{$this->orderField})
			{
				$this->database->prepare("UPDATE {$this->strTable} SET tstamp=?, {$this->orderField}=? WHERE id=?")
							   ->execute(time(), serialize($arrNew), $this->activeRecord->id);

			    $this->objDca->createNewVersion = true; // see #6285
			}
		}

		// Return the value as usual
		if ($varInput == '')
		{
			if ($this->mandatory)
			{
				$this->addError(sprintf($GLOBALS['TL_LANG']['ERR']['mandatory'], $this->strLabel));
			}

			return '';
		}
		elseif (strpos($varInput, ',') === false)
		{
			return $this->multiple ? array(intval($varInput)) : intval($varInput);
		}
		else
		{
			$arrValue = array_map('intval', array_filter(explode(',', $varInput)));

			return $this->multiple ? $arrValue : $arrValue[0];
		}
	}


	/**
	 * Check the selected value
	 *
	 * @param mixed $varInput
	 */
	protected function checkValue($varInput)
	{
		if ($varInput == '')
		{
			return;
		}

		// TODO Check if valid static selection
//		$arrPids = $this->database->prepare("SELECT pid FROM tl_static WHERE id IN (?)")
//							      ->execute($varInput)->fetchAssoc();
	}


	/**
	 * Generate the widget and return it as string
	 *
	 * @return string
	 */
	public function generate()
	{
		$arrSet = array();
		$arrValues = array();

		if (! empty($this->varValue) ) // Should be an array with one value or a single value
		{
			$objStatic = StaticModel::findOneById((int) is_array($this->varValue) ? $this->varValue[0] : $this->varValue);

			if (null !== $objStatic)
			{
				$arrSet[] = $objStatic->id;
				$arrValues[$objStatic->id] = Image::getHtml('articles.svg') . ' ' . $objStatic->title;
			}
		}

		$return = '<input type="hidden" name="'.$this->strName.'" id="ctrl_'.$this->strId.'" value="'.implode(',', $arrSet).'"><div class="selector_container"><ul id="sort_'.$this->strId.'">';

		foreach ($arrValues as $k=>$v)
		{
			$return .= '<li data-id="'.$k.'">'.$v.'</li>';
		}

		$return .= '</ul>';

		if (! System::getContainer()->get('contao.picker.builder')->supportsContext('static'))
		{
			$return .= '
	<p><button class="tl_submit" disabled>'.$GLOBALS['TL_LANG']['MSC']['changeSelection'].'</button></p>';
		}
		else
		{
			$extras = array
			(
				'fieldType' => $this->fieldType,
				'filesOnly' => $this->filesOnly,
				'source' => $this->strTable.'.'.$this->currentRecord,
			);

			$return .= '
	<p><a href="' . ampersand(System::getContainer()->get('contao.picker.builder')->getUrl('static', $extras)) . '" class="tl_submit" id="st_' . $this->strName . '">'.$GLOBALS['TL_LANG']['MSC']['changeSelection'].'</a></p>
	<script>
	  $("st_' . $this->strName . '").addEvent("click", function(e) {
		e.preventDefault();
		Backend.openModalSelector({
		  "id": "tl_listing",
		  "title": "' . StringUtil::specialchars(str_replace("'", "\\'", $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['label'][0])) . '",
		  "url": this.href + document.getElementById("ctrl_'.$this->strId.'").value,
		  "callback": function(table, value) {
			new Request.Contao({
			  evalScripts: false,
			  onSuccess: function(txt, json) {
				$("ctrl_' . $this->strId . '").getParent("div").set("html", json.content);
				json.javascript && Browser.exec(json.javascript);
			  }
			}).post({"action":"reloadStatictree", "name":"' . $this->strId . '", "value":value.join("\t"), "REQUEST_TOKEN":"' . REQUEST_TOKEN . '"});
		  }
		});
	  });
	</script>';
		}

		$return = '<div>' . $return . '</div></div>';

		return $return;
	}
}
