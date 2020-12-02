<?php

/**
 * GoMage.com
 *
 * GoMage Core M2
 *
 * @category  Extension
 * @copyright Copyright (c) 2018-2018 GoMage.com (https://www.gomage.com)
 * @author    GoMage.com
 * @license   https://www.gomage.com/licensing  Single domain license
 * @terms     of use https://www.gomage.com/terms-of-use
 * @version   Release: 2.0.0
 * @since     Class available since Release 2.0.0
 */

namespace GoMage\Core\Block\Adminhtml\System\Config\Form;

/**
 * Class Button
 *
 * @package GoMage\Core\Block\Adminhtml\System\Config\Form
 */
class Button extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var array
     */
    private $b = [
        'groups' => 'api', 'fields' => 'fields', 'value' => 'value', 'section' => 'gomage_core', 'group_s' => 'gomage_s'
    ];
    const SERVER_URL = '/activate/activation/';

    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return \Magento\Framework\Phrase|mixed|string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        if ($this->_cache->load('product_' . $this->_scopeConfig->getValue('gomage_client/api/product_id'))) {
            return __('Activated');
        }
        return $this->getButtonHtml();
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getButtonHtml()
    {
        $base = $this->_scopeConfig->getValue('web/secure/base_url');
        $params = $this->_scopeConfig->getValue($this->b['section'] . '/' . $this->b['groups']);

        if (!$params) {
            $params = [];
        }

        $i = '';
        foreach ($params as $item) {
            if (isset($item['i'])) {
                $i .= $item['i'] . ',';
            }
        }
        $url = $this->_scopeConfig
                ->getValue('gomage_core_url/url_core') . self::SERVER_URL . '?callback=' .
            $this->getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true]);
        $url .= '&d=' . $base;
        $url .= '&k=' . $this->_scopeConfig->getValue('gomage/key/act');
        $button = $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Button')
            ->setData(
                [
                    'label' => __('Activate Extensions'),
                    'class' => 'activate',
                    'style' => 'height:30px;'
                ]
            );
        $button->setOnClick('setLocation(\' ' . $url . '\')');
        return $button->toHtml();
    }

}