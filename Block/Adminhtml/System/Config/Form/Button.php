<?php
namespace GoMage\Core\Block\Adminhtml\System\Config\Form;

class Button extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * Get the button contents
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        if ($this->_cache->load('product_' . $this->_scopeConfig->getValue('gomage_client/api/product_id'))) {
            return __('Activated');
        }
        return $this->getButtonHtml();
    }

    /**
     * Generate button html
     *
     * @return string
     */
    protected function getButtonHtml()
    {
        $url = 'http://serveractivatem2.loc/activate/activation/';
        $url = $url.'?callback='.$this->_urlBuilder->getBaseUrl();
        /** @var \Magento\Backend\Block\Widget\Button $button */
        $button = $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Button')
            ->setData([
                    'label'          => __('Click for activate'),
                    'class'          => 'activate',
                ]
            );
        ;
        $button->setOnClick('setLocation(\' ' . $url . '\')');
        return $button->toHtml();
    }

}