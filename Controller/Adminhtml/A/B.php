<?php

namespace GoMage\Core\Controller\Adminhtml\A;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\JsonFactory;
use GoMage\Core\Model\Processors\ProcessorA;
use Magento\Framework\HTTP\Client\Curl;
use GoMage\Core\Helper\Data;
use Magento\Framework\App\Config\ScopeConfigInterface;

class B extends Action
{
    private $resultJsonFactory;
    private $curl;
    private $helperData;
    private $scopeConfig;

    public function __construct(
        Action\Context $context,
        JsonFactory $resultJsonFactory,
        ProcessorA $processorA,
        Curl $curl,
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