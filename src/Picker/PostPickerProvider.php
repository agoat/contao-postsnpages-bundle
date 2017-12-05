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

namespace Agoat\PostsnPagesBundle\Picker;

use Contao\CoreBundle\Framework\FrameworkAwareInterface;
use Contao\CoreBundle\Framework\FrameworkAwareTrait;
use Contao\CoreBundle\Picker\AbstractPickerProvider;
use Contao\CoreBundle\Picker\DcaPickerProviderInterface;
use Contao\CoreBundle\Picker\PickerConfig;


/**
 * Provides the posts picker
 *
 * @author Arne Stappen <https://github.com/agoat>
 */
class PostPickerProvider extends AbstractPickerProvider implements DcaPickerProviderInterface, FrameworkAwareInterface
{
    use FrameworkAwareTrait;

	
	/**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'postPicker';
    }

	
    /**
     * {@inheritdoc}
     */
    public function supportsContext($context)
    {
        return in_array($context, ['post', 'link'], true) && $this->getUser()->hasAccess('post', 'modules');
    }

	
    /**
     * {@inheritdoc}
     */
    public function supportsValue(PickerConfig $config)
    {
		if ('post' === $config->getContext()) {
            return is_numeric($config->getValue());
        }

        return false !== strpos($config->getValue(), '{{post_url::');
    }

	
    /**
     * {@inheritdoc}
     */
    public function getDcaTable()
    {
        return 'tl_posts';
    }

	
    /**
     * {@inheritdoc}
     */
    public function getDcaAttributes(PickerConfig $config)
    {
        $value = $config->getValue();

        if ('post' === $config->getContext()) {
            $attributes = ['fieldType' => $config->getExtra('fieldType')];

			if ($source = $config->getExtra('source')) {
                $attributes['preserveRecord'] = $source;
            }

            if ($value) {
                $attributes['value'] = array_map('intval', explode(',', $value));
            }

            return $attributes;
        }

        $attributes = ['fieldType' => 'radio'];

        if ($value && false !== strpos($value, '{{post_url::')) {
            $attributes['value'] = str_replace(['{{post_url::', '}}'], '', $value);
        }

        return $attributes;
    }

	
    /**
     * {@inheritdoc}
     */
    public function convertDcaValue(PickerConfig $config, $value)
    {
        if ('post' === $config->getContext()) {
            return (int) $value;
        }

        return '{{post_url::'.$value.'}}';
    }

	
    /**
     * {@inheritdoc}
     */
    protected function getRouteParameters(PickerConfig $config = null)
    {
        return ['do' => 'posts'];
    }
}
