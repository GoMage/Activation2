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
/**
 * Class ProcessorN
 * @package GoMage\Core\Model\Processors
 */
class ProcessorN
{

    const BASE_URL = '/api/rest';
    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    private $jsonHelper;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * ProcessorN constructor.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Psr\Log\LoggerInterface $logger
    )
    {
        $this->jsonHelper = $jsonHelper;
        $this->logger = $logger;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param $curl
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function process($curl, $h)
    {
        try {
            $curl->addHeader("Accept", "application/json");
            $curl->addHeader("Content-Type", "application/json");
            $params = $this->jsonHelper->jsonEncode($this->getParams($h));
            $curl->post(
                $this->scopeConfig->getValue('gomage_core_url/url_core') . self::BASE_URL .
                '/act/notify',
                $params
            );
        } catch (\Exception $e) {
            $this->logger->critical('Error message', ['exception' => $e]);
        }
    }

    /**
     * @param $h
     * @return array
     */
    private function getParams($h)
    {
        return [
            'bu' => $h->getBU(),
            'n' => $h->getN(),
            'e' => $h->getE(),
            'v' => $h->getV($h->getN()),
        ];
    }
}