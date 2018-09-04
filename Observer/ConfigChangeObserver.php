<?php

namespace GoMage\Core\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\App\RequestInterface;
use GoMage\Core\Helper\Data;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class ConfigChangeObserver
 * @package GoMage\Core\Observer
 */
class ConfigChangeObserver implements ObserverInterface
{
    /**
     * @var ScopeConfigInterface
     */
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
     * @var \GoMage\Core\Model\Processors\ProcessorA
     */
    private $processorA;

    /**
     * @var Data
     */
    private $helperData;

    /**
     * ConfigChangeObserver constructor.
     * @param Curl $curl
     * @param RequestInterface $request
     * @param Data $helperData
     * @param ScopeConfigInterface $configEdit
     * @param \GoMage\Core\Model\Processors\ProcessorA $processorA
     */
    public function __construct(
        Curl $curl,
        RequestInterface $request,
        Data $helperData,
        ScopeConfigInterface $configEdit,
        \GoMage\Core\Model\Processors\ProcessorA $processorA
    ) {
        $this->helperData = $helperData;
        $this->configEdit = $configEdit;
        $this->curl = $curl;
        $this->request = $request;
        $this->processorA = $processorA;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->processorA->process3($this->curl);
    }
}