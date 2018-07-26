<?php
/**
 * Created by PhpStorm.
 * User: Димасик
 * Date: 02.07.2018
 * Time: 10:51
 */

namespace GoMage\Core\Controller\Activation;

use Magento\Framework\App\Action\Context;
use \Magento\Framework\App\Config\ConfigResource\ConfigInterface ;
use GoMage\Core\Helper\Data;

class Save extends \Magento\Framework\App\Action\Action
{
    private $helperData;

    public function __construct(Context $context, Data $helperData)
    {
        $this->helperData = $helperData;
        parent::__construct($context);
    }

    public function execute()
    {
        $dataCustomer = $this->getRequest()->getParams('data_customer');
        if(isset($dataCustomer['data_customer'])
            && isset($dataCustomer['data_customer']['content'])
            && isset($dataCustomer['data_customer']['class'])
        )
        $this->helperData->proccess($dataCustomer['data_customer']['content'], $dataCustomer['data_customer']['class']);
    }
}