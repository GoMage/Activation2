<?php
/**
 * Created by PhpStorm.
 * User: Димасик
 * Date: 23.07.2018
 * Time: 14:53
 */

namespace GoMage\Core\Controller\A;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Action\Context;
use GoMage\Core\Helper\Data;
use Magento\Framework\HTTP\Client\Curl;

class T extends \Magento\Framework\App\Action\Action
{
    private $helperData;
    private $scopeConfig;
    private $resultJsonFactory;
    private $curl;
    public function __construct(
        Context $context, Data $helperData,
        ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        Curl $curl
    )
{
    $this->curl = $curl;
    $this->helperData = $helperData;
    $this->scopeConfig = $scopeConfig;
    parent::__construct($context);
}

    public function execute()
    {
        try {
            $dataCustomer = $this->getRequest()->getParams('data_customer');
            $a = $this->helperData->proccess3($this->curl, $dataCustomer);
            if ($a) {
                return $a->process3($dataCustomer, $this->curl);
            } else {
                $result = $this->resultJsonFactory->create();
                return $result->setData(['error' => 1]);
            }
        } catch (\Exception $e) {
            return $result->setData(['error' => 1]);
        }
        $a = $this->helperData->proccess3($this->curl);
        return $a->process3();
    }
}