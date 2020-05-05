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

namespace GoMage\Core\Model\Processors;

use Magento\Framework\Exception\FileSystemException;

/**
 * Class ProcessorA
 * @package GoMage\Core\Model\Processors
 */
class ProcessorA
{

    const BASE_URL = '/api/rest';
    /**
     * @var array
     */
    private $b = [
        'groups' => 'api', 'fields' => 'fields', 'value' => 'value', 'section' => 'gomage_core', 'group_s' => 'gomage_s'
    ];
    /**
     * @var array
     */
    private $w = [];
    /**
     * @var array
     */
    private $s = [];
    /**
     * @var array
     */
    private $r =[];
    /**
     * @var array
     */
    private $messagess = [
        0 => 'Module is Activated',
        1 => 'The number of purchased domains is lower than the number of selected domains',
        2 => 'Incorrect license data. Your license is blocked. Please contact support@gomage.com',
        3 => 'Incorrect license key. Your license is blocked. Please contact support@gomage.com',
        4 => 'Incorrect license data. Please contact support@gomage.com',
        6 => 'Your demo license expired. Please contact support@gomage.com ',
        7 => 'The number of purchased domains is lower than the number of selected domains.'
            . 'Your license is blocked. Please contact support@gomage.com',
        8 => 'Exceeds the number of available domains for the license demo',
        'default' => 'Module is not Activated',
    ];
    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    private $jsonHelper;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $jsonFactory;
    /**
     * @var \Magento\Framework\App\Config\ReinitableConfigInterface
     */
    private $reinitableConfig;
    /**
     * @var \Magento\Config\Model\ResourceModel\Config
     */
    private $config;
    /**
     * @var \Magento\Framework\Module\ModuleListInterface
     */
    private $fullModuleList;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $dateTime;

    /**
     * @var ProcessorR
     */
    private $processorR;

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
     * ProcessorA constructor.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param \Magento\Framework\App\Config\ReinitableConfigInterface $reinitableConfig
     * @param \Magento\Config\Model\ResourceModel\Config $config
     * @param \Magento\Framework\Module\ModuleListInterface $fullModuleList
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param ProcessorR $processorR
     * @param \Magento\Framework\Component\ComponentRegistrarInterface $componentRegistrar
     * @param \Magento\Framework\Filesystem\Directory\ReadFactory $readFactory
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Magento\Framework\App\Config\ReinitableConfigInterface $reinitableConfig,
        \Magento\Config\Model\ResourceModel\Config $config,
        \Magento\Framework\Module\ModuleListInterface $fullModuleList,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \GoMage\Core\Model\Processors\ProcessorR $processorR,
        \Magento\Framework\Component\ComponentRegistrarInterface $componentRegistrar,
        \Magento\Framework\Filesystem\Directory\ReadFactory $readFactory,
        \Magento\Framework\Serialize\SerializerInterface $serializer
    ) {
        $this->dateTime = $dateTime;
        $this->jsonHelper = $jsonHelper;
        $this->scopeConfig = $scopeConfig;
        $this->jsonFactory = $jsonFactory;
        $this->reinitableConfig = $reinitableConfig;
        $this->config = $config;
        $this->fullModuleList = $fullModuleList;
        $this->storeManager = $storeManager;
        $this->processorR = $processorR;
        $this->componentRegistrar = $componentRegistrar;
        $this->readFactory = $readFactory;
        $this->serializer = $serializer;
    }

    /**
     * @param $curl
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function process3($curl)
    {
        $result = $this->jsonFactory->create();
        try {
            $curl->addHeader("Authorization", "Bearer " .$this->scopeConfig
                    ->getValue('section/gomage_client/param'), 'default', 0);
            $curl->addHeader("Accept", "application/json ");
            $curl->addHeader("Content-Type", "application/json ");
            if ($this->scopeConfig->getValue('web/secure/use_in_frontend')) {
                $d =  $this->scopeConfig->getValue('web/secure/base_url');
            } else {
                $d =  $this->scopeConfig->getValue('web/unsecure/base_url');
            }
            $d = trim(preg_replace('/^.*?\\/\\/(.*)?\\//', '$1', $d));
            $ds = $this->_getDomainsAviable($d);
            $params['ds'] = $ds;
            $params['d'] = $d;
            $params['ns'] = $this->getNames();
            if ($params['ns']) {
                foreach ($this->getNamesWithoutVersion() as $a) {
                    $params['a'][$a] = $this->scopeConfig->getValue('section/' . $a . '/a');
                    $params['ns'] = $this->getNames();
                    if ($params['ns']) {
                        foreach ($this->getNamesWithoutVersion() as $a) {
                            $params['a'][$a] = $this->scopeConfig->getValue('section/'.$a.'/a');
                            if ($this->scopeConfig->getValue('section/'.$a.'/ms')) {
                                $params['ms'][$this->scopeConfig->getValue('section/'.$a.'/ms')] = $this->scopeConfig
                                    ->getValue('section/'.$a.'/ms');
                            }
                        }
                    }
                }
            }
            $params = $this->jsonHelper->jsonEncode($params);
            $curl->post(
                $this->scopeConfig->getValue('gomage_core_url/url_core') . self::BASE_URL .
                '/act/add',
                $params
            );
            $b = $this->jsonHelper->jsonDecode($curl->getBody());
            if (isset($b['p'][0])) {
                $b = $this->jsonHelper->jsonDecode($b['p'][0]);
            }
            if ($b) {
                $names = [];
                $error = 0;
                $success = 0;
                foreach ($b as $key => $dm) {
                    $names[] = $dm['name'];
                    if (isset($dm['error']) && !$dm['error']) {
                        $success++;
                        $this->config->saveConfig('section/' . $dm['name'] . '/c', $dm['c'], 'default', 0);
                        $this->config->saveConfig('section/' . $dm['name'] . '/e', $dm['error'], 'default', 0);
                        if (isset($dm['a'])) {
                            $this->config->saveConfig('section/' . $dm['name'] . '/a', $dm['a'], 'default', 0);
                        }
                        $this->coll($dm, $this->config);
                        $dm['message'] = $this->messagess[$dm['error']];
                        $this->r[$dm['name']] = $dm;
                    } else {
                        $error = 1;
                        if ($dm['error'] == 7 || $dm['error'] == 8) {
                            $this->config->deleteConfig('gomage_core/gomage_s/' . $dm['name'], 'default', 0);
                            $this->config->deleteConfig($this->b['section'] . '/' . $this->b['section'] . '/' .
                                $dm['name'], 'default', 0);
                        }
                        $this->config->deleteConfig('section/' . $dm['name'] . '/e', 'default', 0);
                        $this->config->deleteConfig('section/' . $dm['name'] . '/a', 'default', 0);
                        $this->config->saveConfig('section/' . $dm['name'] . '/e', $dm['error'], 'default', 0);
                        $this->config->deleteConfig('section/' . $dm['name'] . '/coll', 'default', 0);
                        $this->config->saveConfig('section/' . $dm['name'] . '/c', $dm['c'], 'default', 0);
                        if ($dm['error'] != 5) {
                            $dm['message'] = $this->messagess[$dm['error']];
                        }

                        if ($dm['error'] == 5) {
                            $dm['message'] = 'The '.$dm['name'] .' version'
                                .$this->getVersion($dm['name']).
                                ' is not available within your license upgrade period.';
                        }
                        $this->r[$dm['name']] = $dm;
                    }
                    if ($error) {
                        $result = $result->setData(['error' => 1]);
                    } else {
                        $result = $result->setData(['success' => 1]);
                    }
                }

           /*     Убрал удаление данных из БД при неудачной проверке на активацию, AS-131
                if ($this->getNamesWithoutVersion()) {
                    if ($names) {
                        $resultN = array_diff($this->getNamesWithoutVersion(), $names);
                    } else {
                        $resultN = $this->getNamesWithoutVersion();
                    }

                    foreach ($resultN as $iconf) {
                        if (!$this->scopeConfig->getValue('section/'.$iconf.'/e')) {
                            $this->config->deleteConfig('section/' . $iconf . '/e', 'default', 0);
                        }
                        $this->config->deleteConfig('section/' . $iconf . '/a', 'default', 0);
                        $this->config->deleteConfig('section/' . $iconf . '/c', 'default', 0);
                        $this->config->deleteConfig('section/' . $iconf . '/coll', 'default', 0);
                    }
                } */
            } else {
                $names = $this->getNamesWithoutVersion();
                if ($names) {
                    foreach ($names as $iconf) {
                        if (!$this->scopeConfig->getValue('section/'.$iconf.'/e')) {
                            $this->config->deleteConfig('section/' . $iconf . '/e', 'default', 0);
                        }
                        $this->config->deleteConfig('section/' . $iconf . '/a', 'default', 0);
                        $this->config->deleteConfig('section/' . $iconf . '/c', 'default', 0);
                        $this->config->deleteConfig('section/' . $iconf . '/coll', 'default', 0);
                    }
                }
                $result = $result->setData(['error' => 1]);
            }
            $this
                ->config
                ->saveConfig('gomage_da/da/da', $this->dateTime->gmtDate(), 'default', 0);
            $this->reinitableConfig->reinit();
            return $result;
        } catch (\Exception $e) {
            return $result->setData(['error' => 1]);
        }
    }

    /**
     * @return array
     */
    public function getNames()
    {
        $n = [];
        $names = $this->fullModuleList->getNames();
        foreach ($names as $name) {
            $nn = strpos($name, 'GoMage');
            if (0 === $nn) {
                if ($this->processorR->isD($name)) {
                    $n[$name] = $name . '_' . $this->getVersion($name);
                }
            }
        }
        return $n;
    }

    /**
     * @return array
     */
    public function getNamesWithoutVersion()
    {
        $n = [];
        $names = $this->fullModuleList->getNames();
        foreach ($names as $name) {
            $nn = strpos($name, 'GoMage');
            if (0 === $nn) {
                if ($this->processorR->isD($name)) {
                    $n[$name] = $name;
                }
            }
        }
        return $n;
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
     * @param $b
     * @return array
     */
    private function _getDomainsAviable($b)
    {
        $domains = [];
        $param = $this->getNamesWithoutVersion();
        if ($param) {
            foreach ($param as $item) {
                $domains[$item] = [];
                foreach ($this->storeManager->getWebsites() as $website) {
                    if (in_array($website->getId(), $this->getAvailableWebsites($item))) {
                        $url = $website->getConfig('web/unsecure/base_url');
                        $domain = trim(preg_replace('/^.*?\\/\\/(.*)?\\//', '$1', $url));
                        if ($domain && $b != $domain) {
                            $domains[$item][] = $domain;
                        }
                        $url = $website->getConfig('web/secure/base_url');
                        $domain = trim(preg_replace('/^.*?\\/\\/(.*)?\\//', '$1', $url));
                        if ($domain && $b != $domain) {
                            $domains[$item][] = $domain;
                        }
                    }
                    foreach ($website->getStores() as $store) {
                        if ($store->isActive()) {
                            if (in_array($store->getId(), $this->getAvailableStores($item))) {
                                $url = $store->getConfig('web/unsecure/base_url');
                                $domain = trim(preg_replace('/^.*?\\/\\/(.*)?\\//', '$1', $url));
                                if ($domain && $b != $domain) {
                                    $domains[$item][] = $domain;
                                }
                                $url = $store->getConfig('web/secure/base_url');
                                $domain = trim(preg_replace('/^.*?\\/\\/(.*)?\\//', '$1', $url));
                                if ($domain && $b != $domain) {
                                    $domains[$item][] = $domain;
                                }
                            }
                        }
                    }
                }
                $domains[$item] = array_unique($domains[$item]);
            }
        }
        return $domains;
    }

    /**
     * @param $item
     * @return array|mixed
     */
    public function getAvailableWebsites($item)
    {
        if (!isset($this->w[$item])) {
            $this->w[$item] = explode(',', $this->scopeConfig->getValue($this->b['section'] . '/' .
                $this->b['section'] . '/' . $item));
        }
        return isset($this->w[$item]) ? $this->w[$item] : [];
    }

    /**
     * @param $item
     * @return array|mixed
     */
    public function getAvailableStores($item)
    {
        if (!isset($this->s[$item])) {
            $this->s[$item] = explode(',', $this->scopeConfig->getValue('gomage_core/' .
                $this->b['group_s'] . '/' . $item));
        }
        return isset($this->s[$item]) ? $this->s[$item] : [];
    }

    /**
     * @param $data
     * @param $resource
     */
    public function coll($data, $resource)
    {
        $resource->saveConfig('section/' . $data['name'] . '/coll', @serialize($data), 'default', 0);
    }

    /**
     * @param $r
     */
    public function setR($r)
    {
        $this->r = $r;
    }

    /**
     * @return array
     */
    public function getR()
    {
        return $this->r;
    }
}
