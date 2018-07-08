<?php

namespace GoMage\Core\Plugin;

use Magento\Config\Controller\Adminhtml\System\Config\Edit as CoreConfigEdit;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\App\RequestInterface;

/**
 * Class ConfigEdit
 */
class ConfigEdit
{
    private $configEdit;
    /**
     * @var Curl
     */
    private $curl;
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * ConfigEdit constructor.
     * @param Curl $curl
     * @param RequestInterface $request
     */
    public function __construct(Curl $curl, RequestInterface $request)
    {
        $this->curl = $curl;
        $this->request = $request;
    }

    /**
     * @param CoreConfigEdit $configEdit
     * @param $result
     * @return mixed
     */
    public function afterExecute(CoreConfigEdit $configEdit, $result) {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        /** @var \Magento\Framework\App\Config\ConfigResource\ConfigInterface $config */
        $config = $objectManager
            ->get('Magento\Framework\App\Config\ScopeConfigInterface');
        $param = $config->getValue('section/gomage_client/param');
        $this->curl->addHeader("Authorization", "Bearer {$param}");
        $this->curl->get('http://serveractivatem2.loc/api/rest/activates');
        return $result;
    }
}