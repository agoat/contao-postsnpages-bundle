<?php

/*
 * This file is part of the Contao Posts'n'Pages extensino.
 *
 * Copyright (c) 2017 Arne Stappen
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
 * Provides the archive picker.
 *
 * @author Arne Stappen <https://github.com/agoat>
 */
class ArchivePickerProvider extends AbstractPickerProvider implements DcaPickerProviderInterface, FrameworkAwareInterface
{
    use FrameworkAwareTrait;

	/**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'archivePicker';
    }

    /**
     * {@inheritdoc}
     */
    public function supportsContext($context)
    {
        return in_array($context, ['archive'], true) && $this->getUser()->hasAccess('archive', 'modules');
    }

    /**
     * {@inheritdoc}
     */
    public function supportsValue(PickerConfig $config)
    {
		if ('archive' === $config->getContext()) {
            return is_numeric($config->getValue());
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getDcaTable()
    {
        return 'tl_archive';
    }

    /**
     * {@inheritdoc}
     */
    public function getDcaAttributes(PickerConfig $config)
    {
        $value = $config->getValue();

        if ('archive' === $config->getContext()) {
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

         return $attributes;
    }

    /**
     * {@inheritdoc}
     */
    public function convertDcaValue(PickerConfig $config, $value)
    {
        return (int) $value;
    }

    /**
     * {@inheritdoc}
     */
    protected function getRouteParameters(PickerConfig $config = null)
    {
        return ['do' => 'posts'];
    }
}