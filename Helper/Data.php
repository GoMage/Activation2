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

namespace GoMage\Core\Helper;

use Magento\Framework\Exception\FileSystemException;

/**
 * Class Data
 *
 * @package GoMage\Core\Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     *
     */
    const BASE_URL = '/api/rest';
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory
     */
    private $attributeCollectionFactory;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var \Magento\Store\Model\System\Store
     */
    private $systemStore;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $dateTime;
    /**
     * @var \Magento\Framework\Module\ModuleListInterface
     */
    private $moduleList;
    /**
     * @var \Magento\Framework\Encryption\Encryptor
     */
    private $encryptor;
    /**
     * @var \Magento\Framework\HTTP\Client\Curl
     */
    private $curl;
    /**
     * @var array
     */
    private $inf = [];
    /**
     * @var \Magento\Framework\View\Helper\Js
     */
    private $jsHelper;
    /**
     * @var \Magento\Config\Model\ResourceModel\Config
     */
    private $configResource;
    /**
     * @var \Magento\Framework\Module\FullModuleList
     */
    private $fullModuleList;
    /**
     * @var \Magento\Framework\App\State
     */
    private $state;

    private $backendUrl;

    /**
     * @var \GoMage\Core\Model\Processors\ProcessorR
     */
    private $processorR;

    /**
     * @var array
     */
    private $b = [
        'groups' => 'api', 'fields' => 'fields', 'value' => 'value', 'section' => 'gomage_core', 'group_s' => 'gomage_s'
    ];

    /**
     * @var \Magento\Framework\Component\ComponentRegistrarInterface
     */
    private $componentRegistrar;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadFactory
     */
    private $readFactory;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    private $serializer;

    /**
     * Data constructor.
     * @param \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $attributeCollectionFactory
     * @param \Magento\Framework\Module\FullModuleList $fullModuleList
     * @param \Magento\Framework\App\State $state
     * @param \Magento\Config\Model\ResourceModel\Config $configResource
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param \Magento\Framework\Module\ModuleListInterface $moduleList
     * @param \GoMage\Core\Model\CurlFix $curl
     * @param \Magento\Framework\Encryption\Encryptor $encryptor
     * @param \Magento\Framework\View\Helper\Js $jsHelper
     * @param \Magento\Backend\Model\UrlInterface $backendUrl
     * @param \GoMage\Core\Model\Processors\ProcessorR $processorR
     * @param \Magento\Framework\App\Cache\TypeListInterface $typeList
     * @param \Magento\Framework\Component\ComponentRegistrarInterface $componentRegistrar
     * @param \Magento\Framework\Filesystem\Directory\ReadFactory $readFactory
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     */
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $attributeCollectionFactory,
        \Magento\Framework\Module\FullModuleList $fullModuleList,
        \Magento\Framework\App\State $state,
        \Magento\Config\Model\ResourceModel\Config $configResource,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\System\Store $systemStore,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \GoMage\Core\Model\CurlFix $curl,
        \Magento\Framework\Encryption\Encryptor $encryptor,
        \Magento\Framework\View\Helper\Js $jsHelper,
        \Magento\Backend\Model\UrlInterface $backendUrl,
        \GoMage\Core\Model\Processors\ProcessorR $processorR,
        \Magento\Framework\App\Cache\TypeListInterface $typeList,
        \Magento\Framework\Component\ComponentRegistrarInterface $componentRegistrar,
        \Magento\Framework\Filesystem\Directory\ReadFactory $readFactory,
        \Magento\Framework\Serialize\SerializerInterface $serializer
    ) {
        parent::__construct($context);
        $this->state = $state;
        $this->backendUrl = $backendUrl;
        $this->attributeCollectionFactory = $attributeCollectionFactory;
        $this->configResource = $configResource;
        $this->storeManager = $storeManager;
        $this->systemStore = $systemStore;
        $this->dateTime = $dateTime;
        $this->moduleList = $moduleList;
        $this->curl = $curl;
        $this->encryptor = $encryptor;
        $this->jsHelper = $jsHelper;
        $this->jsHelper = $jsHelper;
        $this->fullModuleList = $fullModuleList;
        $this->processorR = $processorR;
        $this->typeList = $typeList;
        $this->componentRegistrar = $componentRegistrar;
        $this->readFactory = $readFactory;
        $this->serializer = $serializer;
    }

    /**
     * @param $param
     * @return array
     */
    public function getAvailableWebsites($param)
    {
        $w = [];
        if (!$param) {
            $param = [];
        }
        foreach ($param as $key => $item) {
            $w[$item] = $this->scopeConfig->getValue($this->b['section'] . '/' . $this->b['section'] . '/' . $item);
        }
        return $w;
    }

    /**
     * @param $param
     * @return array
     */
    public function getAvailableStores($param)
    {
        $s = [];
        if (!$param) {
            $param = [];
        }
        foreach ($param as $key => $item) {
            $s[$item] = $this->scopeConfig->getValue($this->b['section'] . '/' . $this->b['group_s'] . '/' . $item);
        }
        return $s;
    }

    /**
     * @return array
     */
    public function getN()
    {
        $n = [];
        $names = $this->fullModuleList->getNames();
        foreach ($names as $name) {
            $nn = strpos($name, 'GoMage');
            if (0 === $nn) {
                if ($this->processorR->isD($name)) {
                    $n[] = $name;
                }
            }
        }
        return $n;
    }

    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getC(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $html = '';
        $id = $element->getId();
        $param = $this->getN();
        $websites = $this->getAvailableWebsites($param);
        $stores = $this->getAvailableStores($param);

        $html .= '<div class="div-refresh-domain" style="width: 100%; height: 20px; text-align: left;  font-size: 10px;
                  margin-bottom: 35px;"><button class="refresh-domain"
                  onclick="event.preventDefault();">' . __('Show available domains')
            . '</button></div>';
        $counter = [];
        $partHtml = '';
        if ($param) {
            foreach ($param as $key => $item) {
                if (!$this->getVersion($item)) {
                    continue;
                }
                $e = $this->scopeConfig->getValue('section/' . $item . '/e');

                switch ($e) {
                    case 1:
                        $htmlHeader = '<div  class="error-header-' . $item . '" style="width: 100%; color: red;
                        text-align: left;  font-size: 1.2em; margin-bottom: 5px; margin-top: 10px;  ">' .
                            __('The number of purchased domains is lower than the number of selected domains')
                            . '</div>';
                        $html .= '<div data-element="' . $item . '" class="module-name-header" style="width: 100%;
                        cursor:pointer; text-align: left; font-weight: bold; font-size: 1.2em; margin-bottom: 5px;
                        margin-top: 10px;  ">' . $item . ' v' . $this->getVersion($item) .
                            ' <div class="expander-gomage-root-' . $item . '" style="width: 0;height: 0;
                        margin-top: 5px; border: 8px solid transparent; border-top-color: #696969; border-bottom: 0;
                        float:left; margin-right: 3%"></div>'
                            . '<div class="expander-gomage-top-root-' . $item . '" style="width: 0;height: 0;
                             margin-top: 5px; border: 8px solid transparent; border-bottom-color: #696969;
                             border-top: 0; float:left; display:none;"></div></div>' . $htmlHeader;
                        break;
                    case '0':
                        $partHtmlHeader = '<div  class="accordion error-header-' . $item . '" style="width: 100%;
                        color: green; text-align: left;  font-size: 1.2em; margin-bottom: 5px; margin-top: 10px;  ">
                        <span class="error-header-span-' . $item . '">' . __('Module is Activated') .
                            '<div style="color:green;">' . __('Available domains') . ': ' .
                            '</span><span class="' . $item . '"> %%counter%%</span></div></div>';
                        $partHtml .= '<div data-element="' . $item . '" class="module-name-header" style="width: 100%;
                        cursor:pointer; text-align: left; font-weight: bold; font-size: 1.2em; margin-bottom: 5px;
                        margin-top: 10px;  ">' . $item . ' v' . $this->getVersion($item) .
                            ' <div class="expander-gomage-root-' . $item . '" style="width: 0;height: 0;
                        margin-top: 5px; border: 8px solid transparent; border-top-color: #696969; border-bottom: 0;
                        float:left; margin-right: 3%"></div>'
                            . '<span class="expander-gomage-top-root-' . $item . '" style="width: 0;height: 0;
                              margin-top: 5px; border: 8px solid transparent; border-bottom-color: #696969;
                              border-top: 0; float:left; display:none; margin-right: 3%"></span></div>' . $partHtmlHeader;
                        break;
                    case 2:
                        $htmlHeader = '<div class="error-header-' . $item . '" style="width: 100%; color: red;
                        text-align: left;  font-size: 1.2em; margin-bottom: 5px; margin-top: 10px;  ">'
                            . __('Incorrect license data. Your license is blocked. Please contact support@gomage.com')
                            . '</div>';
                        $html .= '<div data-element="' . $item . '" class="module-name-header" style="width: 100%;
                        cursor:pointer; text-align: left; font-weight: bold; font-size: 1.2em; margin-bottom: 5px;
                        margin-top: 10px;  ">' . $item . ' v' . $this->getVersion($item) . '
                        <div class="expander-gomage-root-' . $item . '" style="width: 0;height: 0; margin-top: 5px;
                        border: 8px solid transparent; border-top-color: #696969; border-bottom: 0; float:left "></div>
                             <div class="expander-gomage-top-root-' . $item . '" style="width: 0;height: 0;
                             margin-top: 5px; border: 8px solid transparent; border-bottom-color: #696969;
                             border-top: 0; float:left; display:none;"></div></div>' . $htmlHeader;
                        break;
                    case 3:
                        $htmlHeader = '<div class="error-header-' . $item . '" class="error-header-' . $item . '"
                        style="width: 100%; color: red; text-align: left;  font-size: 1.2em; margin-bottom: 5px;
                        margin-top: 10px;  ">' .
                            __('Incorrect license key. Your license is blocked. Please contact support@gomage.com')
                            . '</div>';
                        $html .= '<div data-element="' . $item . '" class="module-name-header" style="width: 100%;
                        cursor:pointer; text-align: left; font-weight: bold; font-size: 1.2em; margin-bottom: 5px;
                        margin-top: 10px;  ">' . $item . ' v' . $this->getVersion($item) .
                            ' <div class="expander-gomage-root-' . $item .
                            '" style="width: 0;height: 0; margin-top: 5px; border: 8px solid transparent;
                        border-top-color: #696969; border-bottom: 0; float:left "></div>
                             <div class="expander-gomage-top-root-' . $item . '" style="width: 0;height: 0;
                             margin-top: 5px; border: 8px solid transparent; border-bottom-color: #696969;
                             border-top: 0; float:left; display:none;"></div></div>' . $htmlHeader;
                        break;
                    case 4:
                        $htmlHeader = '<div class="error-header-' . $item . '" style="width: 100%; color: red;
                        text-align: left;  font-size: 1.2em; margin-bottom: 5px; margin-top: 10px;  ">' .
                            __('Incorrect license data. Please contact support@gomage.com.') . '</div>';
                        $html .= '<div data-element="' . $item . '" class="module-name-header" style="width: 100%;
                        cursor:pointer; text-align: left; font-weight: bold; font-size: 1.2em; margin-bottom: 5px;
                        margin-top: 10px;  ">' . $item . ' v' . $this->getVersion($item) .
                            ' <div class="expander-gomage-root-' . $item . '" style="width: 0;height: 0;
                        margin-top: 5px; border: 8px solid transparent; border-top-color: #696969; border-bottom: 0;
                        float:left "></div>
                             <div class="expander-gomage-top-root-' . $item . '" style="width: 0;height: 0;
                             margin-top: 5px; border: 8px solid transparent; border-bottom-color: #696969;
                             border-top: 0; float:left; display:none;"></div></div>' . $htmlHeader;
                        break;
                    case 5:
                        $mess = __('The' . $item . ' version' . $this->getVersion($item) . ' is not available within your'
                            . 'license upgrade period. Your license is blocked. Please contact support@gomage.com');
                        $htmlHeader = '<div class="error-header-' . $item . '" style="width: 100%; color: red;
                        text-align: left;  font-size: 1.2em; margin-bottom: 5px; margin-top: 10px;  ">' . $mess .
                            '</div>';
                        $html .=
                            '<div data-element="' . $item . '" class="module-name-header" style="width: 100%;
                            cursor:pointer; text-align: left; font-weight: bold; font-size: 1.2em; margin-bottom: 5px;
                            margin-top: 10px;  ">' . $item . ' v' . $this->getVersion($item) .
                            ' <div class="expander-gomage-root-' . $item . '" style="width: 0;height: 0;
                            margin-top: 5px; border: 8px solid transparent; border-top-color: #696969; border-bottom: 0;
                            float:left "></div>
                             <div class="expander-gomage-top-root-' . $item . '" style="width: 0;height: 0;
                             margin-top: 5px; border: 8px solid transparent; border-bottom-color: #696969;
                             border-top: 0; float:left; display:none;"></div></div>' . $htmlHeader;
                        break;
                    case 6:
                        $htmlHeader = '<div class="error-header-' . $item . '" style="width: 100%; color: red;
                        text-align: left;  font-size: 1.2em; margin-bottom: 5px; margin-top: 10px;  ">' .
                            __('Your demo license expired. Please contact support@gomage.com') . '</div>';
                        $html .= '<div data-element="' . $item . '" class="module-name-header" style="width: 100%;
                        cursor:pointer; text-align: left; font-weight: bold; font-size: 1.2em; margin-bottom: 5px;
                        margin-top: 10px;  ">' . $item . ' v' . $this->getVersion($item) .
                            ' <div class="expander-gomage-root-' . $item . '" style="width: 0;height: 0;
                        margin-top: 5px; border: 8px solid transparent; border-top-color: #696969; border-bottom: 0;
                        float:left "></div>
                             <div class="expander-gomage-top-root-' . $item . '"
                             style="width: 0;height: 0; margin-top: 5px; border: 8px solid transparent;
                             border-bottom-color: #696969; border-top: 0; float:left; display:none;"></div></div>'
                            . $htmlHeader;

                        break;
                    case 7:
                        $htmlHeader = '<div class="error-header-' . $item . '" style="width: 100%; color: red;
                        text-align: left;  font-size: 1.2em; margin-bottom: 5px; margin-top: 10px;  ">'
                            . __(
                                'The number of purchased domains is lower than the number of selected domains.' .
                                'Your license is blocked. Please contact support@gomage.com'
                            ) . '</div>';
                        $html .= '<div data-element="' . $item . '" class="module-name-header" style="width: 100%;
                        cursor:pointer; text-align: left; font-weight: bold; font-size: 1.2em; margin-bottom: 5px;
                        margin-top: 10px;  ">' . $item . ' v' . $this->getVersion($item) .
                            ' <div class="expander-gomage-root-' . $item . '" style="width: 0;height: 0;
                        margin-top: 5px; border: 8px solid transparent; border-top-color: #696969; border-bottom: 0;
                        float:left "></div>
                             <div class="expander-gomage-top-root-' . $item . '" style="width: 0;height: 0;
                             margin-top: 5px; border: 8px solid transparent; border-bottom-color: #696969;
                             border-top: 0; float:left; display:none;"></div></div>' . $htmlHeader;
                        break;

                    case 8:
                        $htmlHeader = '<div class="error-header-' . $item . '" style="width: 100%; color: red;
                        text-align: left;  font-size: 1.2em; margin-bottom: 5px; margin-top: 10px;  ">'
                            . __('Exceeds the number of available domains for the license demo') . '</div>';
                        $html .= '<div data-element="' . $item . '" class="module-name-header" style="width: 100%;
                        cursor:pointer; text-align: left; font-weight: bold; font-size: 1.2em; margin-bottom: 5px;
                        margin-top: 10px;  ">' . $item . ' v' . $this->getVersion($item) .
                            ' <div class="expander-gomage-root-' . $item . '" style="width: 0;height: 0;
                        margin-top: 5px; border: 8px solid transparent; border-top-color: #696969; border-bottom: 0;
                        float:left "></div>
                             <div class="expander-gomage-top-root-' . $item . '" style="width: 0;height: 0;
                             margin-top: 5px; border: 8px solid transparent; border-bottom-color: #696969;
                             border-top: 0; float:left; display:none;"></div></div>' . $htmlHeader;
                        break;
                    default:
                        $htmlHeader = '<div class="error-header-' . $item . '" style="width: 100%; color: red;
                        text-align: left;  font-size: 1.2em; margin-bottom: 5px; margin-top: 10px;  ">'
                            . __('Module is not Activated') . '</div>';
                        $html .= '<div data-element="' . $item . '"class="module-name-header" style="width: 100%;
                        cursor:pointer; text-align: left; font-weight: bold; font-size: 1.2em; margin-bottom: 5px;
                        margin-top: 10px;  ">' . $item . ' v' . $this->getVersion($item) .
                            ' <div class="expander-gomage-root-' . $item . '" style="width: 0;height: 0;
                         margin-top: 5px; border: 8px solid transparent; border-top-color: #696969; border-bottom: 0;
                         float:left "></div>
                             <div class="expander-gomage-top-root-' . $item . '" style="width: 0;height: 0;
                             margin-top: 5px; border: 8px solid transparent; border-bottom-color: #696969;
                             border-top: 0; float:left; display:none;"></div></div>' . $htmlHeader;
                }
                if ($e) {
                    $html .= '<div id="content-' . $item . '" class="content" >';
                } else {
                    $partHtml .= '<div id="content-' . $item . '" class="content">';
                }
                $c = $this->scopeConfig->getValue('section/' . $item . '/c');
                $counter[$item] = $this->scopeConfig->getValue('section/' . $item . '/c') ?: 0;
                $allDomains = [];
                $name = 'groups[gomage_core][' . $this->b['fields'] . '][' . $item . '][' . $this->b['value'] . ']';
                $namePrefix = 'groups[' .
                    $this->b['group_s'] . '][' . $this->b['fields'] . '][' . $item . '][' . $this->b['value'] . ']';
                $websiteHtml = '';
                if ($this->scopeConfig->getValue('web/secure/use_in_frontend')) {
                    $base = $this->scopeConfig->getValue('web/secure/base_url');
                } else {
                    $base = $this->scopeConfig->getValue('web/unsecure/base_url');
                }
                foreach ($this->storeManager->getWebsites() as $website) {
                    $website->getConfig('web/unsecure/base_url');
                    $element->setName($name . '[]');
                    $element->setId($id . '_' . $website->getId());
                    $element->setChecked(
                        in_array(
                            $website->getId(),
                            $websites[$item] ? explode(',', $websites[$item]) : []
                        )
                    );
                    $element->setValue($website->getId());
                    $elementHtml = $element->getElementHtml();
                    $secure = $website->getConfig('web/secure/use_in_frontend');
                    if ($secure) {
                        if (in_array($website->getConfig('web/secure/base_url'), $allDomains)) {
                            continue;
                        }
                        $conditionW = $base != $website->getConfig('web/secure/base_url');
                        $allDomains[] = $website->getConfig('web/secure/base_url');
                    } else {
                        if (in_array($website->getConfig('web/unsecure/base_url'), $allDomains)) {
                            continue;
                        }
                        $conditionW = $base != $website->getConfig('web/unsecure/base_url');
                        $allDomains[] = $website->getConfig('web/unsecure/base_url');
                    }
                    if (in_array($website->getId(), $websites[$item] ? explode(',', $websites[$item]) : [])) {
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
                            $allDomains[] = $store->getConfig('web/secure/base_url');
                        } else {
                            $allDomains[] = $store->getConfig('web/unsecure/base_url');
                        }
                        if (in_array($store->getId(), isset($stores[$item]) ? explode(',', $stores[$item]) : [])) {
                            --$counter[$item];
                        }
                        $element->setName($namePrefix . '[]');
                        $element->setId($id . '_store_' . $store->getId());
                        $element->setChecked(
                            in_array($store->getId(), isset($stores[$item]) ? explode(',', $stores[$item]) : [])
                        );
                        $element->setValue($store->getId());
                        $storeHtml .= '<div data-namespace="'
                            . $item . '" class=" label-store field choice admin__field admin__field-option"
                                style="margin-left: 10%">' . $element->getElementHtml() .
                            ' <label for="' .
                            $id . '_' . $store->getId() .
                            '" class="admin__field-label labels-elements"><span>' .
                            $this->storeManager->getStore($store->getId())->getName() .
                            '</span></label>';
                        $storeHtml .= '</div>' . "\n";
                    }
                    if ($conditionW || $storeHtml !== '') {
                        $websiteHtml .= '<div class="field website-checkbox-' . $item .
                            ' choice admin__field admin__field-option"></span>' . $elementHtml .
                            ' <label data-content-website="' . $item . '"  data-website-id="' . $website->getId() . '" for="' .
                            $id . '_' . $website->getId() .
                            '" class="admin__field-label website-div-top"><span>' .
                            $website->getName() .
                            '
                            <div class="expander-gomage expander-gomage-' . $item . '-' . $website->getId()
                            . '" style="width: 0;height: 0; margin-top: 5px;
                             border: 6px solid transparent; border-top-color: #adadad; border-bottom: 0; float: left; margin-right: 3% ;
                             ">
                             </div>
                             <span class="expander-gomage-top expander-gomage-top-' . $item . '-'
                            . $website->getId() . '" style="width: 0;height: 0; margin-top: 5px;
                             border: 6px solid transparent; border-bottom-color: #adadad; border-top: 0;
                             float:left; display:none; margin-right: 3%"></span></label>
                             <div class="content content-key-' . $item . '-' . $website->getId() . '" style="display: none" >';
                    }
                    if ($storeHtml !== '') {
                        $websiteHtml .= $storeHtml;
                    }
                    if ($conditionW || $storeHtml !== '') {
                        $websiteHtml .= '</div></div>' . "\n";
                    }
                }
                if ($e) {
                    $counter[$item] = 0;
                }
                $partHtml = str_replace('%%counter%%', $counter[$item], $partHtml);
                $html .= $partHtml . $websiteHtml;
                $html .= '</div>';
                $partHtml = '';
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
                if (!$this->getVersion($item)) {
                    continue;
                }
                $nameS .= "'$item',";
                $e = $this->scopeConfig->getValue('section/' . $item . '/e');
                $c = (int)$this->scopeConfig->getValue('section/' . $item .
                    '/c') ? ((int)$this->scopeConfig->getValue('section/' . $item . '/c')) : 0;
                if ($e !== '0') {
                    $c = 0;
                }
                $name = 'groups[' . $this->b['section'] . '][' .
                    $this->b['fields'] . '][' . $item . '][' . $this->b['value'] . '][]';

                $namePrefix = 'groups[' . $this->b['group_s'] . '][' . $this->b['fields'] . '][' .
                    $item . '][' . $this->b['value'] . '][]';
                $jsString .= '
                             counter["' . $item . '"] = ' . $counter[$item] . ';
                             counterAll["' . $item . '"] = ' . $c . ';
                        if($$(".website-checkbox-' . $item . ' input[name=\'' .
                    $namePrefix . '\']:checked , .website-checkbox-' .
                    $item . ' input[name=\'' . $name . '\']:checked").length >= counterAll["' . $item . '"]){
                        $$(".website-checkbox-' . $item . ' input[name=\'' .
                    $namePrefix . '\'], .website-checkbox-' . $item . ' input[name=\'' .
                    $name . '\']").each(function(e){
                            if(!e.checked){
                                e.disabled = "disabled";
                            }
                        });
    			    }else {
                        $$(".website-checkbox-' . $item . ' input[name=\'' .
                    $namePrefix . '\'], .website-checkbox-' . $item . ' input[name=\'' .
                    $name . '\'] ").each(function(e){
                            if(!e.checked){
                                e.disabled = "";
                            }
                        });
    			    }
            $$(".website-checkbox-' . $item . ' input[name=\'' . $namePrefix
                    . '\'], .website-checkbox-' . $item . ' input[name=\'' .
                    $name . '\']").each(function(element) {
               element.observe("click", function (event) {
               event.stopPropagation();
                      counter["' . $item . '"] = counterAll["' . $item . '"] - (+$$(".website-checkbox-' . $item
                    . ' input[name=\'' . $namePrefix . '\']:checked , .website-checkbox-' . $item .
                    ' input[name=\'' . $name . '\']:checked").length);
                      $$("span.' . $item . '").first().innerHTML=" " +counter["' . $item . '"];
                    if($$(".website-checkbox-' . $item . ' input[name=\'' . $namePrefix
                    . '\']:checked , .website-checkbox-' . $item . ' input[name=\'' . $name
                    . '\']:checked").length >= counterAll["' . $item
                    . '"]){
                        $$(".website-checkbox-' . $item . ' input[name=\'' . $namePrefix
                    . '\'], .website-checkbox-' . $item . ' input[name=\'' . $name . '\']").each(function(e){
                            if(!e.checked){

                                e.disabled = "disabled";
                            }
                        });
    			    }else {
                        $$(".website-checkbox-' . $item
                    . ' input[name=\'' . $namePrefix . '\'], .website-checkbox-' . $item
                    . ' input[name=\'' . $name . '\'] ").each(function(e){
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
        $nameS = trim($nameS, ',');
        return $html .
            $this->jsHelper
                ->getScript('require([\'prototype\'], function(){document.observe("dom:loaded", function() {
                    $$(".website-div-top").each(function(el) {
                             el.on("click", function (e) {
                              e.stopPropagation();
                             var stC = el.readAttribute("data-content-website");
                             var wId = el.readAttribute("data-website-id");
                             elem = $("content-" + stC);
                             var  elemKey = elem.select(\'.content-key-\'+stC+"-"+wId);

                              if( el.hasClassName(\'website-div-top\')) {
                                elemKey.first().show();
                                el.removeClassName(\'website-div-top\');
                                $$(\'.expander-gomage-top-\'+stC+"-"+wId).first().show();
                                $$(\'.expander-gomage-\'+stC+"-"+wId).first().hide();
                             } else {
                                 el.addClassName(\'website-div-top\');
                                 $$(\'.content-key-\'+stC+"-"+wId).first().hide();
                                 $$(\'.expander-gomage-\'+stC+"-"+wId).first().show();
                                 $$(\'.expander-gomage-top-\'+stC+"-"+wId).first().hide();
                             }

                             });


                    });
                    $$(".label-store").each(function(el) {
                         el.on("click", function (event) {
                          event.stopPropagation();;
                          var n = el.readAttribute("data-namespace");
                          var c = counterAll[n];
                          elem = $("content-" + n);
                          var t = elem.select("input:checked").length;
                         if(elem.select("input:checked").length < c ) {
                             if(el.select("input").first().checked) {
                                    el.select("input").first().checked="";
                                } else {
                                    el.select("input").first().checked="checked";
                                }
                            } else {
                                el.select("input").first().checked="";
                            }
                            counter[n] = counterAll[n] - (+(elem.select("input:checked").length))
                            $$("."+n).first().innerHTML=" "+counter[n];
                            if(elem.select("input:checked").length >= c ) {
                                 elem.select("input").each(function(e) {
                                 if(!e.checked) {
                                     e.disabled = "disabled";
                                 }
                            });
                           }  else {
                                 elem.select("input").each(function(e) {
                                     e.disabled = "";
                            });
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

                    $$(".refresh-domain").first().observe("click", function (event) {
                      event.stopPropagation();
                        new Ajax.Request("' . $this->backendUrl->getUrl('gomage_activator/a/b') . '", {
                                  onSuccess: function(response) {
                                       var result = response.responseJSON.data;
                                            nameS.each(function(el) {

                                                if (result.hasOwnProperty(el)) {
                                                     if(result[el]["error"]) {
                                                        $$(".website-checkbox-" + result[el]["name"]+
                                                        " input").each(function(e) {
                                                            e.disabled = "disabled";
                                                            e.checked = false;
                                                        });
                                                        counter[el]=0;
                                                        counterAll[el]=0;

                                                        $$(".error-header-" +
                                                        result[el]["name"]).first().innerHTML=result[el]["message"];
                                                        $$(".error-header-" +
                                                        result[el]["name"]).first().style.color="red";
                                                     } else {
                                                          var diff =  result[el]["c"] - counterAll[el];
                                                          var res = counter[el] + diff;
                                                          counterAll[el] = counterAll[el] + diff;
                                                          counter[el] = counter[el] + diff;
                                                          var t = $$(".error-header-" + result[el]["name"]).first();
                                                         // $$("span." + el).first().innerHTML=counter[el];
                                                          $$(".error-header-" +
                                                          result[el]["name"]).first().style.color="green";
                                                          $$(".error-header-" +
                                                          result[el]["name"]).first().innerHTML=
                                                          result[el]["message"] +
                                                          "<div>' . __("Available domains") . '"
                                                          + ": <span class=\'"+ el +"\'> " + res + "</span></div>";
                                                          $$(".website-checkbox-" + result[el]["name"]
                                                          +" input").each(function(e) {
                                                             if($$(".website-checkbox-" +
                                                             result[el]["name"]
                                                             +" input:checked").length >= counter[el]) {
                                                                if(!e.checked){
                                                                    e.disabled = "disabled";
                                                                }
                                                            } else {
                                                                 e.disabled = "";
                                                            }
                                                        });
                                                     }
                                                } else {
                                                      $$(".error-header-"+el).first().innerHTML="' .
                    __("Module is not Activated") . '"
                                                      $$(".error-header-"+el).first().style.color="red"
                                                      $$(".error-header-"+el).each(function(e) {
                                                            if(!e.checked){
                                                                    e.disabled = "disabled";
                                                                }
                                                        });
                                                }
                                            });
                                  }
                            });
                     });
                    var counter = {};
                    var counterAll = {};
                    var nameS = [' . $nameS . ']
                ' . $jsString . '});});');
    }

    /**
     * @param $moduleName
     * @return \Magento\Framework\Phrase|mixed
     * @throws FileSystemException
     */
    private function getVersion($moduleName)
    {
        $path = $this->componentRegistrar->getPath(
            \Magento\Framework\Component\ComponentRegistrar::MODULE,
            $moduleName
        );
        $directoryRead = $this->readFactory->create($path);
        try {
            $composerJsonData = $directoryRead->readFile('composer.json');
        } catch (FileSystemException $e) {
            throw $e;
        }
        $data = $this->serializer->unserialize($composerJsonData);

        return !empty($data['version']) ? $data['version'] : __(' Module version is not specified in the composer.json file');
    }

    /**
     * @return mixed
     */
    public function getU()
    {
        return $this->scopeConfig->getValue($this->b['section'] . '/' . $this->b['groups']);
    }

    public function cl()
    {
        $n = $this->getN();
        foreach ($n as $i) {
            $this->configResource->deleteConfig('section/' . $i . '/e', 'default', 0);
            $this->configResource->deleteConfig('section/' . $i . '/a', 'default', 0);
            $this->configResource->deleteConfig('section/' . $i . '/coll', 'default', 0);
            $this->configResource->deleteConfig('gomage_core/gomage_s/' . $i, 'default', 0);
            $this->configResource->deleteConfig($this->b['section'] . '/' .
                $this->b['section'] . '/' . $i, 'default', 0);
        }
    }

    /**
     * @param $name
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isA($name)
    {
        if (!isset($this->inf[$name])) {
            if ($this->scopeConfig->getValue('section/' . $name . '/coll')) {
                $this->inf[$name] = @unserialize(($this->scopeConfig->getValue('section/' . $name . '/coll')));
            } else {
                return false;
            }
        }

        $act = (isset($this->inf[$name]['a'])) ? $this->inf[$name]['a'] : false;
        if (!$act) {
            return false;
        }
        $matches = false;
        if ($act) {
            preg_match('/^[0-9a-f]{32}$/', $this->inf[$name]['a'], $matches);
        }
        return ($this->iAadmCom($name, $matches)
                && $this->isUseWS($this->inf[$name]['ds'])
            ) || ($this->isFrComp($name, $matches) && $this->isD($this->inf[$name]['ds']));
    }

    public function isCo()
    {
        if ($this->getVersion('GoMage_Core')) {
            return true;
        }
        return false;
    }

    /**
     * @param $ds
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isD($ds)
    {
        $dms = $this->storeManager->getStore();
        $section = $dms->getId();
        if ($dms) {
            $secure = $dms->getConfig('web/secure/use_in_frontend');
            if ($secure) {
                $d = $dms->getConfig('web/secure/base_url');
            } else {
                $d = $dms->getConfig('web/unsecure/base_url');
            }
        }
        if ($this->scopeConfig->getValue('web/secure/use_in_frontend')) {
            $base = $this->scopeConfig->getValue(
                'web/secure/base_url',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $section
            );
        } else {
            $base = $this->scopeConfig->getValue(
                'web/unsecure/base_url',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $section
            );
        }
        $d = preg_replace('/.*?\:\/\//', '', preg_replace('/www\./', '', strtolower(trim($d, '/'))));
        $base = preg_replace('/.*?\:\/\//', '', preg_replace('/www\./', '', strtolower(trim($base, '/'))));
        if ($d == $base) {
            return true;
        }
        return in_array($d, $ds);
    }

    /**
     * @param $ds
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isUseWS($ds)
    {
        return ((
            ($this->_request->getParam('store')
                    ||
                    ($this->_request->getParam('website')))
                && $this->comWS($ds)
        )) || (!$this->_request->getParam('store') && !$this->_request->getParam('website'));
    }

    /**
     * @param $name
     * @param $matches
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isFrComp($name, $matches)
    {
        return $this->getAr() === 'frontend' && $matches
            &&
            count($matches) == 1 && $this->inf[$name]['error'] === 0
            &&
            $this->fd($this->inf[$name]['db']);
    }

    /**
     * @param $ds
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function comWS($ds)
    {
        $section = 0;
        if ($this->_request->getParam('website')) {
            $dms = $this->storeManager->getWebsite($this->_request->getParam('website'));
            $section = $this->storeManager->getWebsite($this->_request->getParam('website'))->getDefaultStore()->getId();
        } elseif ($this->_request->getParam('store')) {
            $dms = $this->storeManager->getStore($this->_request->getParam('store'));
            $section = $this->_request->getParam('store');
        }

        if ($dms) {
            $secure = $dms->getConfig('web/secure/base_url');
            if ($secure) {
                $d = $dms->getConfig('web/secure/base_url');
            } else {
                $d = $dms->getConfig('web/unsecure/base_url');
            }
        }

        if ($this->scopeConfig->getValue('web/secure/use_in_frontend')) {
            $base = $this->scopeConfig->getValue(
                'web/secure/base_url',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $section
            );
        } else {
            $base = $this->scopeConfig->getValue(
                'web/unsecure/base_url',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $section
            );
        }
        $d = preg_replace('/.*?\:\/\//', '', preg_replace('/www\./', '', strtolower(trim($d, '/'))));
        $base = preg_replace('/.*?\:\/\//', '', preg_replace('/www\./', '', strtolower(trim($base, '/'))));
        if ($d == $base) {
            return true;
        }
        return in_array($d, $ds);
    }

    /**
     * @param $name
     * @param $matches
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function iAadmCom($name, $matches)
    {
        return $this->getAr() === 'adminhtml' && $matches
            &&
            count($matches) == 1 && $this->inf[$name]['error'] === 0
            &&
            $this->fd($this->inf[$name]['db']);
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAr()
    {
        return $this->state->getAreaCode();
    }

    /**
     * @param $d
     * @return bool
     */
    public function fd($d)
    {
        if ($this->scopeConfig->getValue('web/secure/use_in_frontend')) {
            $base = $this->scopeConfig->getValue('web/secure/base_url');
        } else {
            $base = $this->scopeConfig->getValue('web/unsecure/base_url');
        }
        $d = preg_replace('/.*?\:\/\//', '', preg_replace('/www\./', '', strtolower(trim($d, '/'))));
        $base = preg_replace('/.*?\:\/\//', '', preg_replace('/www\./', '', strtolower(trim($base, '/'))));
        return $d === $base;
    }

    /**
     * @return \Magento\Config\Model\ResourceModel\Config
     */
    public function getResource()
    {
        return $this->configResource;
    }

    /**
     * @param $n
     * @return mixed
     */
    public function getError($n)
    {
        return $this->scopeConfig->getValue('section/' . $n . '/e');
    }

    public function getCurl()
    {
        return $this->curl;
    }

    public function getCon()
    {
        return $this->scopeConfig;
    }

    /**
     * @return mixed
     */
    public function getBU()
    {
        if ($this->scopeConfig->getValue('web/secure/use_in_frontend')) {
            return $this->scopeConfig->getValue('web/secure/base_url');
        }
        return $this->scopeConfig->getValue('web/unsecure/base_url');
    }

    /**
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isNotifyD()
    {
        return $this->notifyD();
    }

    /**
     * @return bool
     */
    public function getE()
    {
        return $this->scopeConfig->getValue('trans_email/ident_sales/email');
    }

    /**
     * @param $names
     * @return array
     */
    public function getV($names)
    {
        $v = [];
        foreach ($names as $name) {
            $v[$name] = $this->getVersion($name);
        }
        return $v;
    }

    /**
     * @return bool
     */
    private function notifyD()
    {
        if ($d = $this->getCon()->getValue('gomage_notify/d/d')) {
            if ($d == $this->dateTime->gmtDate('Y-m-d')) {
                $this->setNotifyConD(date('Y-m-d', strtotime('+10 days')));
                return true;
            }
        } else {
            $this->setNotifyConD($this->dateTime
                ->gmtDate('Y-m-d'));
            return true;
        }

        return false;
    }

    /**
     * @param $value
     */
    private function setNotifyConD($value)
    {
        $this->getResource()->saveConfig('gomage_notify/d/d', $value, 'default', 0);
        $this->typeList->cleanType('config');
    }
}
