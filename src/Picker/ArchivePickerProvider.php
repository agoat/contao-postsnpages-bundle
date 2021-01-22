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

namespace Agoat\PostsnPagesBundle\Picker;

use Contao\CoreBundle\Framework\FrameworkAwareInterface;
use Contao\CoreBundle\Framework\FrameworkAwareTrait;
use Contao\CoreBundle\Picker\AbstractInsertTagPickerProvider;
use Contao\CoreBundle\Picker\DcaPickerProviderInterface;
use Contao\CoreBundle\Picker\PickerConfig;
use Knp\Menu\FactoryInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Translation\TranslatorInterface;


/**
 * Provides the archive picker
 *
 * @author Arne Stappen <https://github.com/agoat>
 */
class ArchivePickerProvider extends AbstractInsertTagPickerProvider implements DcaPickerProviderInterface,
    FrameworkAwareInterface
{

    use FrameworkAwareTrait;

    /**
     * @var Security
     */
    private $security;


    public function __construct(
        FactoryInterface $menuFactory,
        RouterInterface $router,
        ?TranslatorInterface $translator,
        Security $security
    ) {
        parent::__construct($menuFactory, $router, $translator);

        $this->security = $security;
    }


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
        return in_array($context, ['archive'], true) && $this->security->isGranted('contao_user.modules', 'archive');
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
        $attributes = ['fieldType' => 'radio'];

        if ('archive' === $config->getContext()) {
            if ($fieldType = $config->getExtra('fieldType')) {
                $attributes['fieldType'] = $fieldType;
            }

            if (is_array($rootNodes = $config->getExtra('rootNodes'))) {
                $attributes['rootNodes'] = $rootNodes;
            }

            if ($source = $config->getExtra('source')) {
                $attributes['preserveRecord'] = $source;
            }

            if ($value) {
                $intval = function ($val) {
                    return (int)$val;
                };

                $attributes['value'] = array_map('intval', explode(',', $value));
            }

            return $attributes;
        }

        return $attributes;
    }


    /**
     * {@inheritdoc}
     */
    public function convertDcaValue(PickerConfig $config, $value)
    {
        return (int)$value;
    }


    /**
     * {@inheritdoc}
     */
    protected function getRouteParameters(PickerConfig $config = null)
    {
        return ['do' => 'posts'];
    }


    protected function getDataContainer(): string
    {
        return 'Table';
    }


    protected function getDefaultInsertTag(): string
    {
        return '{{non_existing_insert_tag::%s}}';
    }

}
