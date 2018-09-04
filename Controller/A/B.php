<?php


namespace GoMage\Core\Controller\A;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Action\Context;
use GoMage\Core\Helper\Data;
use Magento\Framework\HTTP\Client\Curl;

/**
 * Class B
 *
 * @package GoMage\Core\Controller\A
 */
class B extends \Magento\Framework\App\Action\Action
{
    /**
     * @var Data
     */
    private $helperData;
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $resultJsonFactory;
    /**
     * @var Curl
     */
    private $curl;
    /**
     * @var \GoMage\Core\Model\Processors\ProcessorAct
     */
    private $act;

    /**
     * B constructor.
     *
     * @param Context                                          $context
     * @param Data                                             $helperData
     * @param ScopeConfigInterface                             $scopeConfig
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \GoMage\Core\Model\Processors\ProcessorAct       $act
     * @param Curl                                             $curl
     */
    public function __construct(
        Context $context,
        Data $helperData,
        ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \GoMage\Core\Model\Processors\ProcessorAct $act,
        Curl $curl
    ) {
        $this->curl = $curl;
        $this->helperData = $helperData;
        $this->act = $act;
        $this->scopeConfig = $scopeConfig;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {

        try {
            $dataCustomer = $this->getRequest()->getParams();
            $this->act->process3($dataCustomer, $this->curl);
        } catch (\Exception $e) {
            $result = $this->resultJsonFactory->create();
            return $result->setData(['error' => 1]);
        }
    }
}
