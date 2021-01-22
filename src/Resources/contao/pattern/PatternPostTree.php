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


use Agoat\CustomContentElementsBundle\Contao\Controller;
use Agoat\CustomContentElementsBundle\Contao\Pattern;
use Agoat\PostsnPagesBundle\Model\PostModel;
use Contao\StringUtil;

/**
 * Content element pattern "posttree"
 */
class PatternPostTree extends Pattern
{

    /**
     * Creates the DCA configuration
     */
    public function create()
    {
        if ($this->multiPost) {
            $this->generateDCA('multiPost',
                [
                    'inputType'     => 'postTree',
                    'label'         => [$this->label, $this->description],
                    'eval'          => [
                        'multiple'   => true,
                        'fieldType'  => 'checkbox',
                        'orderField' => $this->virtualFieldName('orderPost'),
                        'mandatory'  => ($this->mandatory) ? true : false,
                        'tl_class'   => 'clr',
                    ],
                    'load_callback' => [
                        ['tl_content_elements', 'prepareOrderValue'],
                    ],
                    'save_callback' => [
                        ['tl_content_elements', 'saveOrderValue'],
                    ],
                ]
            );

            // The orderPage field
            $this->generateDCA('orderPost', [], false, false);
        } else {
            $this->generateDCA('singlePost',
                [
                    'inputType' => 'postTree',
                    'label'     => [$this->label, $this->description],
                    'eval'      => [
                        'fieldType' => 'radio',
                        'mandatory' => ($this->mandatory) ? true : false,
                        'tl_class'  => 'clr',
                    ],
                ]
            );
        }
    }


    /**
     * Generate the pattern preview
     *
     * @return string HTML code
     */
    public function preview()
    {
        $strPreview =
            '<div class="widget" style="padding-top:10px;"><h3 style="margin: 0;"><label>' . $this->label . '</label></h3><div class="selector_container"><ul>';

        if ($this->multiPost) {
            $strPreview .= '<li><img src="system/themes/flexible/icons/regular.svg" width="18" height="18" alt=""> Post1</li><li><img src="system/themes/flexible/icons/regular.svg" width="18" height="18" alt=""> Post2</li><li><img src="system/themes/flexible/icons/regular.svg" width="18" height="18" alt=""> Post3</li>';
        } else {
            $strPreview .= '<li><img src="system/themes/flexible/icons/regular.svg" width="18" height="18" alt=""> Post</li>';
        }

        $strPreview .= '</ul><p><a href="javascript:void(0);" class="tl_submit">Change selection</a></p></div><p title="" class="tl_help tl_tip">' . $this->description . '</p></div>';

        return $strPreview;
    }


    /**
     * Prepare the data for the template
     */
    public function compile()
    {
        if ($this->multiPost) {
            $objPosts = PostModel::findMultipleByIds(StringUtil::deserialize($this->data->multiPost));

            // Return if there are no pages
            if ($objPosts === null) {
                return;
            }

            $arrPosts = [];

            // Sort the array keys according to the given order
            if ($this->data->orderPost != '') {
                $tmp = StringUtil::deserialize($this->data->orderPost);

                if (!empty($tmp) && is_array($tmp)) {
                    $arrPosts = array_map(function () {
                    },
                        array_flip($tmp)
                    );
                }
            }

            // Add the items to the pre-sorted array
            while ($objPosts->next()) {
                $arrPosts[$objPosts->id] = $objPosts->row();
            }

            $arrPosts = array_values(array_filter($arrPosts));

            $this->writeToTemplate($arrPosts);
        } else {
            if (($objPost = PostModel::findById($this->data->singlePost)) !== null) {
                $this->writeToTemplate($objPost->row());
            }
        }
    }

}
