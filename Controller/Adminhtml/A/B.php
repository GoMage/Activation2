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

namespace GoMage\Core\Controller\Adminhtml\A;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\JsonFactory;
use GoMage\Core\Model\Processors\ProcessorA;
use \GoMage\Core\Model\CurlFix;
use GoMage\Core\Helper\Data;
use Magento\Framework\App\Config\ScopeConfigInterface;

class B extends Action
{
    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;
    /**
     * @var CurlFix
     */
    private $curl;
    /**
     * @var Data
     */
    private $helperData;
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * B constructor.
     * @param Action\Context $context
     * @param JsonFactory $resultJsonFactory
     * @param ProcessorA $processorA
     * @param CurlFix $curl
     * @param Data $helperData
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Action\Context $context,
        JsonFactory $resultJsonFactory,
        ProcessorA $processorA,
        CurlFix $curl,
        Data $helperData,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->curl = $curl;
        $this->helperData = $helperData;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->processorA = $processorA;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        try {
            $result = $this->resultJsonFactory->create();
            $this->processorA->process3($this->curl);
            $result->setData(['data' =>  $this->processorA->getR()]);
        } catch (\Exception $e) {
            $result->setData(['error' => 1]);
        }
        return $result;
    }
}