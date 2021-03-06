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

namespace Agoat\PostsnPagesBundle\Contao;


use Agoat\PostsnPagesBundle\Model\ArchiveModel;
use Contao\Database;
use Contao\Image;
use Contao\Input;
use Contao\StringUtil;
use Contao\System;
use Contao\Widget;

/**
 * Provide methods to handle input field "archive tree".
 */
class ArchiveTree extends Widget
{

    /**
     * Submit user input
     *
     * @var boolean
     */
    protected $blnSubmitInput = true;

    /**
     * Template
     *
     * @var string
     */
    protected $strTemplate = 'be_widget';

    /**
     * Order ID
     *
     * @var string
     */
    protected $strOrderId;

    /**
     * Order name
     *
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
     * @param  array  $arrAttributes
     */
    public function __construct($arrAttributes = null)
    {
        parent::__construct($arrAttributes);

        $this->database = Database::getInstance();

        // Prepare the order field
        if ($this->orderField != '') {
            $this->strOrderId = $this->orderField . str_replace($this->strField, '', $this->strId);
            $this->strOrderName = $this->orderField . str_replace($this->strField, '', $this->strName);

            // Retrieve the order value
            $objRow =
                $this->database->prepare("SELECT {$this->orderField} FROM {$this->strTable} WHERE id=?")
                               ->limit(1)
                               ->execute($this->activeRecord->id);

            $tmp = StringUtil::deserialize($objRow->{$this->orderField});
            $this->{$this->orderField} = (!empty($tmp) && is_array($tmp)) ? array_filter($tmp) : [];
        }
    }


    /**
     * Return an array if the "multiple" attribute is set
     *
     * @param  mixed  $varInput
     *
     * @return mixed
     */
    protected function validator($varInput)
    {
        $this->checkValue($varInput);

        if ($this->hasErrors()) {
            return '';
        }

        // Store the order value
        if ($this->orderField != '') {
            $arrNew = [];

            if ($order = Input::post($this->strOrderName)) {
                $arrNew = explode(',', $order);
            }

            // Only proceed if the value has changed
            if ($arrNew !== $this->{$this->orderField}) {
                $this->database->prepare("UPDATE {$this->strTable} SET tstamp=?, {$this->orderField}=? WHERE id=?")
                               ->execute(time(), serialize($arrNew), $this->activeRecord->id);

                $this->objDca->createNewVersion = true; // see #6285
            }
        }

        // Return the value as usual
        if ($varInput == '') {
            if ($this->mandatory) {
                $this->addError(sprintf($GLOBALS['TL_LANG']['ERR']['mandatory'], $this->strLabel));
            }

            return '';
        } elseif (strpos($varInput, ',') === false) {
            return $this->multiple ? [intval($varInput)] : intval($varInput);
        } else {
            $arrValue = array_map('intval', array_filter(explode(',', $varInput)));

            return $this->multiple ? $arrValue : $arrValue[0];
        }
    }


    /**
     * Check the selected value
     *
     * @param  mixed  $varInput
     */
    protected function checkValue($varInput)
    {
        if ($varInput == '' || !is_array($this->rootNodes)) {
            return;
        }

        // TODO: Add check vor valid archive selection
        //        $arrPids = $this->Database->prepare("SELECT pid FROM tl_archive WHERE id IN (?)")
        //							      ->execute($varInput)->fetchAssoc();
        //
        //        if (count(array_diff($arrPids, array_merge($this->rootNodes, $this->Database->getChildRecords($this->rootNodes, 'tl_page')))) > 0)
        //		{
        //			$this->addError($GLOBALS['TL_LANG']['ERR']['invalidArticles']);
        //		}
    }


    /**
     * Generate the widget and return it as string
     *
     * @return string
     */
    public function generate()
    {
        $arrSet = [];
        $arrValues = [];

        if (!empty($this->varValue)) // Can be an array
        {
            $objArchive = ArchiveModel::findMultipleByIds((array)$this->varValue, ['order' => 'title']);

            if ($objArchive !== null) {
                while ($objArchive->next()) {
                    $arrSet[] = $objArchive->id;
                    $arrValues[$objArchive->id] = Image::getHtml('iconPLAIN.svg') . ' ' . $objArchive->title;
                }
            }
        }

        $return =
            '<input type="hidden" name="' . $this->strName . '" id="ctrl_' . $this->strId . '" value="' . implode(',',
                $arrSet
            ) . '"><div class="selector_container"><ul id="sort_' . $this->strId . '">';

        foreach ($arrValues as $k => $v) {
            $return .= '<li data-id="' . $k . '">' . $v . '</li>';
        }

        $return .= '</ul>';

        if (!System::getContainer()->get('contao.picker.builder')->supportsContext('archive')) {
            $return .= '
	<p><button class="tl_submit" disabled>' . $GLOBALS['TL_LANG']['MSC']['changeSelection'] . '</button></p>';
        } else {
            $extras = [
                'fieldType' => $this->fieldType,
                'source'    => $this->strTable . '.' . $this->currentRecord,
            ];

            if (is_array($this->rootNodes)) {
                $extras['rootNodes'] = array_values($this->rootNodes);
            }

            $return .= '
	<p><a href="' . ampersand(System::getContainer()->get('contao.picker.builder')->getUrl('archive', $extras)
                ) . '" class="tl_submit" id="pt_' . $this->strName . '">' . $GLOBALS['TL_LANG']['MSC']['changeSelection'] . '</a></p>
	<script>
	  $("pt_' . $this->strName . '").addEvent("click", function(e) {
		e.preventDefault();
		Backend.openModalSelector({
		  "id": "tl_listing",
		  "title": "' . StringUtil::specialchars(str_replace("'",
                    "\\'",
                    $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['label'][0]
                )
                ) . '",
		  "url": this.href + document.getElementById("ctrl_' . $this->strId . '").value,
		  "callback": function(table, value) {
			new Request.Contao({
			  evalScripts: false,
			  onSuccess: function(txt, json) {
				$("ctrl_' . $this->strId . '").getParent("div").set("html", json.content);
				json.javascript && Browser.exec(json.javascript);
			  }
			}).post({"action":"reloadArchivetree", "name":"' . $this->strId . '", "value":value.join("\t"), "REQUEST_TOKEN":"' . REQUEST_TOKEN . '"});
		  }
		});
	  });
	</script>';
        }

        $return = '<div>' . $return . '</div></div>';

        return $return;
    }

}
