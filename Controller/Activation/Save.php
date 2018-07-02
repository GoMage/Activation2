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

class Save extends \Magento\Framework\App\Action\Action
{
    protected $scopeConfig;
    public function __construct(Context $context, ConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context);
    }

    public function execute()
    {
        $customerData = $this->getRequest()->getParams('data_customer');
        if($customerData) {
            $arrayAccess = json_decode($customerData['data_customer'], true);
            if(isset($arrayAccess['access_token'])) {
                $this->scopeConfig ->saveConfig(
                    'section/gomage_client/access', $arrayAccess['access_token'],
                    'default',
                    0
                );
            }
        }
        die;
    }
}