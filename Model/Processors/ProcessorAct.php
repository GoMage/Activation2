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

class ProcessorAct
{
    const BASE_URL = '/api/rest';
    private $b = [
        'groups' => 'api', 'fields' => 'fields', 'value' =>'value', 'section' => 'gomage_core', 'group_s' => 'gomage_s'
    ];
    private $w = [];
    private $s = [];
    private $scopeConfig;
    private $jsonFactory;
    private $reinitableConfig;
    private $config;
    private $fullModuleList;
    private $storeManager;
    private $jsonHelper;
    private $random;
    private $dateTime;
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

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Magento\Framework\App\Config\ReinitableConfigInterface $reinitableConfig,
        \Magento\Config\Model\ResourceModel\Config $config,
        \Magento\Framework\Module\ModuleListInterface $fullModuleList,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\Math\Random $random,
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
        $this->random = $random;
        $this->processorR = $processorR;
        $this->componentRegistrar = $componentRegistrar;
        $this->readFactory = $readFactory;
        $this->serializer = $serializer;
    }

    public function process($curl, $url)
    {
        $param = $this->scopeConfig->getValue('section/gomage_client/param');
        $curl->addHeader("Authorization", "Bearer {$param}");
        $curl->get(self::BASE_URL.$url);
        return $curl->getBody();
    }

    public function process3($data, $curl)
    {
        $result = $this->jsonFactory->create();
        try {
            if (isset($data['key']) && $data['key'] && $data['key'] ==
                $this->scopeConfig->getValue('gomage/key/act')
            ) {
                $this->config
                    ->saveConfig(
                        'gomage/key/act',
                        substr(hash('sha512', $this->random->getRandomString(20)), -32),
                        'default',
                        0
                    );
                $this->config->saveConfig('section/gomage_client/param', $data['param'], 'default', 0);
                $curl->addHeader("Authorization", "Bearer ".$data['param']);
                $curl->addHeader("Accept", "application/json ");
                $curl->addHeader("Content-Type", "application/json ");
                $b = false;
                if (isset($data['cu']) && isset($data['d'])) {
                    $b = trim(preg_replace('/^.*?\\/\\/(.*)?\\//', '$1', $data['d']));
                    $ds = $this->_getDomainsAviable($b);
                    $params['ds'] = $ds;
                    $params['cu'] = $data['cu'];
                    $params['d'] =  $data['d'];
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
                    $params = $this->jsonHelper->jsonEncode($params);
                    $curl->post(
                        $this->scopeConfig->getValue('gomage_core_url/url_core') . self::BASE_URL .
                        '/act/add',
                        $params
                    );
                    $b = $this->jsonHelper->jsonDecode($curl->getBody(), true);
                    if (isset($b['p'][0])) {
                        $b = $this->jsonHelper->jsonDecode($b['p'][0], true);
                    }
                }

                if ($b) {
                    $names = [];
                    $error = 0;
                    $success = 0;
                    foreach ($b as $key => $dm) {
                        $names[] = $dm['name'];
                        if (isset($dm['error']) && !$dm['error']) {
                            $success++;
                            $this->config->saveConfig('section/' .  $dm['name'] . '/c', $dm['c'], 'default', 0);
                            $this->config->saveConfig('section/' .  $dm['name'] . '/e', $dm['error'], 'default', 0);
                            if (isset($dm['a'])) {
                                $this->config->saveConfig('section/' .  $dm['name'] . '/a', $dm['a'], 'default', 0);
                            }
                            $this->coll($dm, $this->config);
                        } else {
                            $error = 1;
                            if ($dm['error'] == 7 || $dm['error'] == 8) {
                                $this->config->deleteConfig('gomage_core/gomage_s/'.$dm['name'], 'default', 0);
                                $this->config->deleteConfig($this->b['section'].'/'.$this->b['section'].'/'.
                                    $dm['name'], 'default', 0);
                            }
                            $this->config->deleteConfig('section/' .$dm['name'] . '/e', 'default', 0);
                            $this->config->deleteConfig('section/' . $dm['name'] . '/a', 'default', 0);
                            $this->config->deleteConfig('section/' . $dm['name'] . '/coll', 'default', 0);
                            $this->config->saveConfig('section/' .  $dm['name'] . '/e', $dm['error'], 'default', 0);
                            $this->config->saveConfig('section/' .  $dm['name'] . '/c', $dm['c'], 'default', 0);
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
             $this->config->deleteConfig('gomage_da/da/da', 'default', 0);
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
     }
         } catch (\Exception $e) {
             $result->setData(['error' => 1]);
         }
        return $result->setData(['error' => 1]);
    }

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

    private function _getDomainsAviable($b)
    {
        $domains = [];
        $param = $this->getNamesWithoutVersion();
        if ($param) {
            foreach ($param as $item) {
                $domains[$item] = [];
                foreach ($this->storeManager->getWebsites() as $website) {
                    if (in_array($website->getId(), $this->getAvailableWebsites($item))) {
                        $secure = $website->getConfig('web/secure/use_in_frontend');
                        if ($secure) {
                            $url =   $website->getConfig('web/secure/base_url');
                        } else {
                            $url = $website->getConfig('web/unsecure/base_url');
                        };
                        $domain = trim(preg_replace('/^.*?\\/\\/(.*)?\\//', '$1', $url));
                        if ($domain && $b != $domain) {
                            $domains[$item][] = $domain;
                        }
                    }
                    foreach ($website->getStores() as $store) {
                        if ($store->isActive()) {
                            if (in_array($store->getId(), $this->getAvailableStores($item))) {
                                $secure = $website->getConfig('web/secure/use_in_frontend');
                                if ($secure) {
                                    $url =   $store->getConfig('web/secure/base_url');
                                } else {
                                    $url = $store->getConfig('web/unsecure/base_url');
                                };
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

    public function getAvailableWebsites($item)
    {
        if (!isset($this->w[$item])) {
            $this->w[$item] = explode(',', $this->scopeConfig->getValue('gomage_core' . '/' . 'section' . '/' . $item));
        }
        return isset($this->w[$item]) ? $this->w[$item] : [];
    }

    public function getAvailableStores($item)
    {
        if (!isset($this->s[$item])) {
            $this->s[$item] = explode(',', $this->scopeConfig->getValue('gomage_core/' .
                $this->b['group_s'] . '/' .  $item));
        }
        return isset($this->s[$item]) ? $this->s[$item] : [];
    }

    public function coll($data, $resource)
    {
        $resource->saveConfig('section/' . $data['name'] . '/coll', @serialize($data), 'default', 0);
    }
}
