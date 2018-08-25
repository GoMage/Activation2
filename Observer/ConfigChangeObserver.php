<?php
/**
 * Created by PhpStorm.
 * User: Димасик
 * Date: 22.07.2018
 * Time: 20:38
 */

namespace GoMage\Core\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\App\RequestInterface;
use GoMage\Core\Helper\Data;
use Magento\Framework\App\Config\ScopeConfigInterface;


class ConfigChangeObserver implements ObserverInterface
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
    //private $processorA;

    private $helperData;

    /**
     * ConfigEdit constructor.
     * @param Curl $curl
     * @param RequestInterface $request
     */
    public function __construct(
        Curl $curl,
        RequestInterface $request,
        Data $helperData,
        ScopeConfigInterface $configEdit
       /// \GoMage\Core\Model\Processors\ProcessorA $processorA
    )
    {
        $this->helperData = $helperData;
        $this->configEdit = $configEdit;
        $this->curl = $curl;
        $this->request = $request;
        //$this->processorA = $processorA;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $a = $this->helperData->process2($this->curl, $this->configEdit->getValue('gomage_processor/a'));
        if($a) {
            $a->process3($this->curl);
        }
        //$this->processorA->process3($this->curl);
    }
}