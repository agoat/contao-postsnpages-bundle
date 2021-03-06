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


use Agoat\PostsnPagesBundle\Model\PostModel;
use Contao\Config;
use Contao\Database;
use Contao\Image;
use Contao\Input;
use Contao\StringUtil;
use Contao\System;
use Contao\Widget;

/**
 * Provide methods to handle input field "post tree"
 */
class PostTree extends Widget
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
        $this->database = Database::getInstance();

        parent::__construct($arrAttributes);

        // Prepare the order field
        if ($this->orderField != '') {
            $this->strOrderId = $this->orderField . str_replace($this->strField, '', $this->strId);
            $this->strOrderName = $this->orderField . str_replace($this->strField, '', $this->strName);

            // Don't try to load virtual pattern fields from database
            if ($this->database->fieldExists($this->orderField, $this->strTable)) {
                // Retrieve the order value
                $objRow =
                    $this->database->prepare("SELECT {$this->orderField} FROM {$this->strTable} WHERE id=?")
                                   ->limit(1)
                                   ->execute($this->activeRecord->id);

                $tmp = StringUtil::deserialize($objRow->{$this->orderField});
                $this->{$this->orderField} = (!empty($tmp) && is_array($tmp)) ? array_filter($tmp) : [];
            }
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
                // Don't try to load virtual pattern fields from database
                if ($this->database->fieldExists($this->orderField, $this->strTable)) {
                    $this->database->prepare("UPDATE {$this->strTable} SET tstamp=?, {$this->orderField}=? WHERE id=?")
                                   ->execute(time(), serialize($arrNew), $this->activeRecord->id);
                }

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
            return $this->multiple ? [(int)$varInput] : (int)$varInput;
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

        // TODO: Add check vor valid post selection
        //		$arrPids = $this->database->prepare("SELECT pid FROM tl_post WHERE id IN (?)")
        //                    ->execute($varInput)->fetchAssoc();

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
        $blnHasOrder = ($this->orderField != '' && is_array($this->{$this->orderField}));

        if (!empty($this->varValue)) // Can be an array
        {
            $objPosts = PostModel::findMultipleByIds((array)$this->varValue);

            if ($objPosts !== null) {
                while ($objPosts->next()) {
                    $arrSet[] = $objPosts->id;
                    $arrValues[$objPosts->id] =
                        Image::getHtml('bundles/agoatpostsnpages/posts' . ((!$objPosts->published || ($objPosts->start != '' && $objPosts->start > time(
                                    )) || ($objPosts->stop != '' && $objPosts->stop < time())) ? '_' : '') . '.svg'
                        ) . ' ' . $objPosts->title . ' (' . $objPosts->alias . Config::get('urlSuffix') . ')';
                }
            }

            // Apply a custom sort order
            if ($blnHasOrder) {
                $arrNew = [];

                foreach ((array)$this->{$this->orderField} as $i) {
                    if (isset($arrValues[$i])) {
                        $arrNew[$i] = $arrValues[$i];
                        unset($arrValues[$i]);
                    }
                }

                if (!empty($arrValues)) {
                    foreach ($arrValues as $k => $v) {
                        $arrNew[$k] = $v;
                    }
                }

                $arrValues = $arrNew;
                unset($arrNew);
            }
        }

        $return =
            '<input type="hidden" name="' . $this->strName . '" id="ctrl_' . $this->strId . '" value="' . implode(',',
                $arrSet
            ) . '">' . ($blnHasOrder ? '
  <input type="hidden" name="' . $this->strOrderName . '" id="ctrl_' . $this->strOrderId . '" value="' . $this->{$this->orderField} . '">' : '') . '
  <div class="selector_container">' . (($blnHasOrder && count($arrValues) > 1) ? '
    <p class="sort_hint">' . $GLOBALS['TL_LANG']['MSC']['dragItemsHint'] . '</p>' : '') . '
    <ul id="sort_' . $this->strId . '" class="' . ($blnHasOrder ? 'sortable' : '') . '">';

        foreach ($arrValues as $k => $v) {
            $return .= '<li data-id="' . $k . '">' . $v . '</li>';
        }

        $return .= '</ul>';

        if (!System::getContainer()->get('contao.picker.builder')->supportsContext('post')) {
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
	<p><a href="' . ampersand(System::getContainer()->get('contao.picker.builder')->getUrl('post', $extras)
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
			}).post({"action":"reloadPosttree", "name":"' . $this->strId . '", "value":value.join("\t"), "REQUEST_TOKEN":"' . REQUEST_TOKEN . '"});
		  }
		});
	  });
	</script>' . ($blnHasOrder ? '
	<script>Backend.makeMultiSrcSortable("sort_' . $this->strId . '", "ctrl_' . $this->strOrderId . '", "ctrl_' . $this->strId . '")</script>' : '');
        }

        $return = '<div>' . $return . '</div></div>';

        return $return;
    }

}
