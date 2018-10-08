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

namespace GoMage\Core\Controller\A;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Action\Context;
use GoMage\Core\Helper\Data;
use \GoMage\Core\Model\CurlFix;

/**
 * Class B
 *
 * @package GoMage\Core\Controller\A
 */
class B extends \Magento\Framework\App\Action\Action
{
    /**
     * @var Data
     */
    private $helperData;
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $resultJsonFactory;
    /**
     * @var CurlFix
     */
    private $curl;
    /**
     * @var \GoMage\Core\Model\Processors\ProcessorAct
     */
    private $act;

    /**
     * B constructor.
     * @param Context $context
     * @param Data $helperData
     * @param ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \GoMage\Core\Model\Processors\ProcessorAct $act
     * @param CurlFix $curl
     */
    public function __construct(
        Context $context,
        Data $helperData,
        ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \GoMage\Core\Model\Processors\ProcessorAct $act,
        CurlFix $curl
    ) {
        $this->curl = $curl;
        $this->helperData = $helperData;
        $this->act = $act;
        $this->scopeConfig = $scopeConfig;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {

        try {
            $dataCustomer = $this->getRequest()->getParams();
            return $this->act->process3($dataCustomer, $this->curl);
        } catch (\Exception $e) {
            $result = $this->resultJsonFactory->create();
            return $result->setData(['error' => 1]);
        }
    }
}
