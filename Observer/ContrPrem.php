<?php

namespace GoMage\Core\Observer;

use Magento\Framework\Event\ObserverInterface;
use GoMage\Core\Helper\Data;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\Message\ManagerInterface;
use Magento\Config\Model\Config\Structure\Data as StructureData;

/**
 * Class ContrPrem
 * @package GoMage\Core\Observer
 */
class ContrPrem implements ObserverInterface
{

    /**
     * @var Data
     */
    private $helperData;
    /**
     * @var ActionFlag
     */
    private $actionFlag;
    /**
     * @var ManagerInterface
     */
    private $messageManager;
    /**
     * @var StructureData
     */
    private $structureData;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $dateTime;

    /**
     * @var \GoMage\Core\Model\Processors\ProcessorA
     */
    private $processors;


    /**
     * ContrPrem constructor.
     * @param Data $helperData
     * @param ActionFlag $actionFlag
     * @param ManagerInterface $messageManager
     * @param StructureData $structureData
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param \GoMage\Core\Model\Processors\ProcessorA $processors
     */
    public function __construct(
        Data $helperData,
        ActionFlag $actionFlag,
        ManagerInterface $messageManager,
        StructureData $structureData,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \GoMage\Core\Model\Processors\ProcessorA $processors
    ) {
        $this->processors = $processors;
        $this->helperData = $helperData;
        $this->structureData = $structureData;
        $this->dateTime  = $dateTime;
        $this->actionFlag = $actionFlag;
        $this->messageManager = $messageManager;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->da();
        $action = $observer->getControllerAction();
        if ($action->getRequest()->getParam('section') == 'gomage_core' ||
            $action->getRequest()->getParam('section') == 'gomage_settings'
            ||
            strpos(get_class($action), 'GoMage\Core') === 0) {
            return;
        }
        $controller = $action->getRequest()->getControllerName();
        if ($controller == 'system_config'
            && $action->getRequest()->getParam('section')
            && strpos($action->getRequest()->getParam('section'), 'gomage') === 0
        ) {
            $section = $this->structureData->get();
            $section = $section['sections'][$action->getRequest()->getParam('section')];
            $resource = $section['resource'];
            $resource = explode('::', $resource);
            $resource = $resource[0];
        }

        if (strpos(get_class($action), 'GoMage') === 0) {
            $resource = explode('\\', get_class($action));
            $resource = $resource[0].'_'.$resource['1'];
        }
        if (isset($resource) && !$this->helperData->isA($resource)
            && (strpos(get_class($action), 'GoMage') === 0
            || (            $controller == 'system_config'
            && $action->getRequest()->getParam('section')
            && strpos($action->getRequest()->getParam('section'), 'gomage') === 0                        ))
        ) {
            if ($this->helperData->getAr()==='adminhtml') {
                if ($this->helperData->getError($resource) !== '0' && $this->helperData->getError($resource)) {
                    $this->messageManager
                        ->addErrorMessage(
                            'Module is blocked please reactivate extension or contact support@gomage.com'
                        );
                } else {
                    $errorMsg = __(
                        'Please activate extension in stores -> config -> gomage -> activation' .
                        ' <a href="%1">Back to activation</a> .',
                        $action->getUrl('adminhtml/system_config/edit/section/gomage_core')
                    );
                    $this->messageManager->addError($errorMsg);
                }
            }
            $action->getRequest()->initForward();
            $action->getRequest()->setActionName('noroute');
            $action->getRequest()->setDispatched(false);
        }
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function da()
    {
        $t= ((strtotime($this->dateTime->gmtDate())
                - $this->helperData->getCon()->getValue('gomage_da/da/da')))/360;
        if ($this->helperData->getAr() === 'adminhtml') {
            if (!$this->helperData->getCon()->getValue('gomage_da/da/da')
                ||
                (((strtotime($this->dateTime->gmtDate())
                        - $this->helperData->getCon()->getValue('gomage_da/da/da')))/360) > 24) {
                $this->processors->process3($this->helperData->getCurl());
                if (!$this->helperData->getCon()->getValue('gomage_da/da/da')) {
                    $this->helperData->getResource()->saveConfig('gomage_da/da/da', $this->dateTime->gmtDate());
                }
            }
        }
    }
}