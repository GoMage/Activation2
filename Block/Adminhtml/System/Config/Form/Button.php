<?php
namespace GoMage\Core\Block\Adminhtml\System\Config\Form;

class Button extends \Magento\Config\Block\System\Config\Form\Field
{
    protected $helperData;
    protected $b = ['groups' => 'api', 'fields' => 'fields', 'value' =>'value', 'section' => 'gomage_core', 'group_s' => 'gomage_s'];
    const SERVER_URL = '/activate/activation/';
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
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        if($this->_scopeConfig->getValue('web/secure/use_in_frontend')) {
            $base =  $this->_scopeConfig->getValue('web/secure/base_url');
        } else {
            $base =  $this->_scopeConfig->getValue('web/unsecure/base_url');
        }

        $params = $this->_scopeConfig->getValue($this->b['section'].'/'.$this->b['groups']);
        if(!$params) {
            $params = [];
        }

        $i = '';
        foreach ($params as $item)
        {
            if(isset($item['i'])) {
                $i .= $item['i'] . ',';
            }
        }
        $i = trim($i,',');
        $url = $this->_scopeConfig->getValue('gomage_core_url/url_core').self::SERVER_URL.'?callback='.$this->getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true]);
        $url .= '&d='. $base;
        $url .= '&k='. $this->_scopeConfig->getValue('gomage/key/act');
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