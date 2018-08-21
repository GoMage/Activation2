<?php
/**
 * Created by PhpStorm.
 * User: Димасик
 * Date: 01.08.2018
 * Time: 17:03
 */

namespace GoMage\Core\Controller\A;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Action\Context;
use GoMage\Core\Helper\Data;
use Magento\Framework\HTTP\Client\Curl;

class B extends \Magento\Framework\App\Action\Action
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
        $this->resultJsonFactory = $resultJsonFactory;
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

    }
}