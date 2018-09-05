<?php

namespace GoMage\Core\Model\Processors;

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
        \Magento\Framework\Serialize\SerializerInterface $serializer
    ) {
        $this->serializer = $serializer;
        $this->jsonHelper = $jsonHelper;
        $this->scopeConfig = $scopeConfig;
        $this->jsonFactory = $jsonFactory;
        $this->reinitableConfig = $reinitableConfig;
        $this->config = $config;
        $this->fullModuleList = $fullModuleList;
        $this->storeManager = $storeManager;
        $this->random = $random;
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
        try {
            $result = $this->jsonFactory->create();
            if ($data['data_customer']['key'] && $data['data_customer']['key'] ==
                $this->scopeConfig->getValue('gomage/key/act')
            ) {
                $this->config
                    ->saveConfig(
                        'gomage/key/act',
                        substr(hash('sha512', $this->random->getRandomString(20)), -32),
                        'default',
                        0
                    );
                $this->config->saveConfig('section/gomage_client/param', $data['data_customer']['param'], 'default', 0);
                $curl->addHeader("Authorization", "Bearer ".$data['data_customer']['param']);
                $curl->addHeader("Accept", "application/json ");
                $curl->addHeader("Content-Type", "application/json ");
                $b = false;
                if (isset($data['data_customer']['cu']) && isset($data['data_customer']['d'])) {
                    $b = trim(preg_replace('/^.*?\\/\\/(.*)?\\//', '$1', $data['data_customer']['d']));
                    $ds = $this->_getDomainsAviable($b);
                    $params['ds'] = $ds;
                    $params['cu'] = $data['data_customer']['cu'];
                    $params['d'] =  $data['data_customer']['d'];
                    $params['ns'] = $this->getNames();
                    if ($params['ns']) {
                        foreach ($this->getNamesWithoutVersion() as $a) {
                            $params['a'][$a] = $this->scopeConfig->getValue('section/'.$a.'/a');
                        }
                    }
                    $params = $this->jsonHelper->jsonEncode($params);
                    $curl->post(
                        $this->scopeConfig->getValue('gomage_core_url/url_core') . self::BASE_URL .
                        '/act/add?XDEBUG_SESSION_START=PHPSTORM',
                        $params
                    );
                    $b = $this->jsonHelper->jsonDecode($curl->getBody(), true);
                    if (isset($b['p']) && isset($b['p'][0])) {
                        $b = $this->jsonHelper->jsonDecode($b['p'][0], true);
                    }
                }

                if ($b) {
                    $error = 0;
                    foreach ($b as $key => $dm) {
                        if (isset($dm['error']) && !$dm['error']) {
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

                $this->reinitableConfig->reinit();
                return $result;
            }
        } catch (\Exception $e) {
            return $result->setData(['error' => 1]);
        }
        return $result->setData(['error' => 1]);
    }

    public function getNames()
    {
        $n = [];
        $names = $this->fullModuleList->getNames();
        foreach ($names as $name) {
            $nn = strpos($name, 'GoMage');
            if (($nn || 0 === $nn) && $name != 'GoMage_Core') {
                $n[$name] = $name.'_'.$this->getVersion($name);
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
            if (($nn || 0 === $nn) && $name != 'GoMage_Core') {
                $n[$name] = $name;
            }
        }
        return $n;
    }

    /**
     * @return string
     */
    private function getVersion($name)
    {
        return $this->fullModuleList->getOne($name)['setup_version'];
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
        $resource->saveConfig('section/' . $data['name'] . '/coll', $this->serializer->serialize($data), 'default', 0);
    }
}