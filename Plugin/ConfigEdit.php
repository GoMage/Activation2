<?php

namespace GoMage\Core\Plugin;

use Magento\Config\Controller\Adminhtml\System\Config\Edit as CoreConfigEdit;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\App\RequestInterface;
use GoMage\Core\Helper\Data;

/**
 * Class ConfigEdit
 */
class ConfigEdit
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

    /**
     * @param CoreConfigEdit $configEdit
     * @param $result
     * @return mixed
     */
    public function afterExecute(CoreConfigEdit $configEdit, $result) {

        $dataCustomer = $this->getRequest()->getParams('data_customer');
        if(isset($dataCustomer['data_customer'])
            && isset($dataCustomer['data_customer']['content'])
            && isset($dataCustomer['data_customer']['class'])
        )
            $this->helperData->proccess(
                $dataCustomer['data_customer']['content'], $dataCustomer['data_customer']['class']
            );
    }

}