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

    private $helperData;
    /**
     * ConfigEdit constructor.
     * @param Curl $curl
     * @param RequestInterface $request
     */
    public function __construct(Curl $curl, RequestInterface $request,  Data $helperData)
    {
        $this->helperData = $helperData;
        $this->curl = $curl;
        $this->request = $request;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->helperData->proccess2($this->curl);
    }
}