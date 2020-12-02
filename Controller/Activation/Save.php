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


namespace GoMage\Core\Controller\Activation;

use Magento\Framework\App\Action\Context;
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
        if (isset($dataCustomer['data_customer'])
            && isset($dataCustomer['data_customer']['content'])
            && isset($dataCustomer['data_customer']['class'])
        ) {
            $this->helperData
                ->proccess($dataCustomer['data_customer']['content'], $dataCustomer['data_customer']['class']);
        }
    }
}