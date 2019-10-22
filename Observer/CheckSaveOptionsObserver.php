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

namespace GoMage\Core\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Class ConfigChangeObserver
 * @package GoMage\Core\Observer
 */
class CheckSaveOptionsObserver implements ObserverInterface
{
    /**
     * @var \Magento\Config\Model\ResourceModel\Config
     */
    protected $_config;

    /**
     * @var \Magento\Framework\App\Config\ReinitableConfigInterface
     */
    protected $_configRein;

    /**
     * CheckSaveOptionsObserver constructor.
     * @param \Magento\Config\Model\ResourceModel\Config $config
     * @param \Magento\Framework\App\Config\ReinitableConfigInterface $configRein
     */
    public function __construct(
        \Magento\Config\Model\ResourceModel\Config $config,
        \Magento\Framework\App\Config\ReinitableConfigInterface $configRein
    )
    {
        $this->_config = $config;
        $this->_configRein = $configRein;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $section = $observer->getRequest()->getParam('section');
        if ($section === 'gomage_core') {
            if ($groups = $observer->getRequest()->getParam('groups')) {
                foreach ($groups as $group => $data) {
                    if ($group === 'gomage_s') {
                        foreach ($data["fields"] as $field => $values) {
                            $this->setValuesConfig($section . '/' . $group . '/' . $field, $values);
                        }
                    }
                }
                $this->_reinit();
            }
        }
    }

    /**
     * @param $path
     * @param $values
     */
    private function setValuesConfig($path, $values)
    {
        if (!empty($values)) {
            foreach ($values as $value) {
                $value = implode(',',$value);
                $this->_config->saveConfig($path,
                    $value, 'default', 0);
            }
        }
    }

    /**
     * _reinit
     */
    protected function _reinit()
    {
        $this->_configRein->reinit();
    }
}