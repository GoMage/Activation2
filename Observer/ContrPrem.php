<?php
/**
 * Created by PhpStorm.
 * User: dimasik
 * Date: 24.8.18
 * Time: 7.21
 */

namespace GoMage\Core\Observer;

use Magento\Framework\Event\ObserverInterface;
use GoMage\Core\Helper\Data;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\Message\ManagerInterface;
use Magento\Config\Model\Config\Structure\Data as StructureData;

class ContrPrem implements ObserverInterface
{

    private $helperData;
    private $actionFlag;
    private $messageManager;
    private $structureData;

    /**
     * ConfigEdit constructor.
     * @param Curl $curl
     * @param RequestInterface $request
     */
    public function __construct(
        Data $helperData,
        ActionFlag $actionFlag,
        ManagerInterface $messageManager,
        StructureData $structureData
    )
    {
        $this->helperData = $helperData;
        $this->structureData = $structureData;
        $this->actionFlag = $actionFlag;
        $this->messageManager = $messageManager;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $action = $observer->getControllerAction();
        if($action->getRequest()->getParam('section') == 'gomage_core' || get_class($action)=='GoMage\Core') {
            return;
        }
        $controller = $action->getRequest()->getControllerName();
        if($controller == 'system_config'
            &&
            $action->getRequest()->getParam('section')
            && strpos($action->getRequest()->getParam('section'),'gomage') === 0
        ) {
            $section = $this->structureData->get();
            $section = $section['sections'][$action->getRequest()->getParam('section')];
            $resource = $section['resource'];
            $resource = explode('::', $resource);
            $resource = $resource[0];
        }

        if(strpos(get_class($action),'GoMage') === 0) {
            $resource = explode('\\', get_class($action));
            $resource = $resource[0].'_'.$resource['1'];
        }
        if(
        isset($resource) && !$this->helperData->isA($resource) &&
        (strpos(get_class($action),'GoMage') === 0
            ||
         (
             $controller == 'system_config'
             &&
             $action->getRequest()->getParam('section')
             &&
             strpos($action->getRequest()->getParam('section'),'gomage') === 0

         ))
        ) {
            $this->messageManager->addErrorMessage('Please activate extension in stores -> config -> gomage -> activation');
            $action->getRequest()->initForward();
            $action->getRequest()->setActionName('noroute');
            $action->getRequest()->setDispatched(false);
        }
    }
}