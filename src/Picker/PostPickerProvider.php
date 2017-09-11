<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Agoat\PostsnPagesBundle\Picker;

use Contao\CoreBundle\Framework\FrameworkAwareInterface;
use Contao\CoreBundle\Framework\FrameworkAwareTrait;
use Contao\CoreBundle\Picker\AbstractPickerProvider;
use Contao\CoreBundle\Picker\DcaPickerProviderInterface;
use Contao\CoreBundle\Picker\PickerConfig;


/**
 * Provides the article picker.
 *
 * @author Andreas Schempp <https://github.com/aschempp>
 */
class PostPickerProvider extends AbstractPickerProvider implements DcaPickerProviderInterface, FrameworkAwareInterface
{
    use FrameworkAwareTrait;

	/**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'postsPicker';
    }

    /**
     * {@inheritdoc}
     */
    public function supportsContext($context)
    {
        return in_array($context, ['posts', 'link'], true) && $this->getUser()->hasAccess('posts', 'modules');
    }

    /**
     * {@inheritdoc}
     */
    public function supportsValue(PickerConfig $config)
    {
		if ('posts' === $config->getContext()) {
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

        if ('posts' === $config->getContext()) {
            $attributes = ['fieldType' => $config->getExtra('fieldType')];

            if (is_array($rootNodes = $config->getExtra('rootNodes'))) {
                $attributes['rootNodes'] = $rootNodes;
            }

            if ($value) {
                $attributes['value'] = array_map('intval', explode(',', $value));
            }

            return $attributes;
        }

        $attributes = ['fieldType' => 'radio'];

        if ($value && false !== strpos($value, '{{posts_url::')) {
            $attributes['value'] = str_replace(['{{posts_url::', '}}'], '', $value);
        }

        return $attributes;
    }

    /**
     * {@inheritdoc}
     */
    public function convertDcaValue(PickerConfig $config, $value)
    {
        if ('posts' === $config->getContext()) {
            return (int) $value;
        }

        return '{{posts_url::'.$value.'}}';
    }

    /**
     * {@inheritdoc}
     */
    protected function getRouteParameters(PickerConfig $config = null)
    {
        return ['do' => 'posts'];
    }
}