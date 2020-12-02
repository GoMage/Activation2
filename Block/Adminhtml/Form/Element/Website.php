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


namespace GoMage\Core\Block\Adminhtml\Form\Element;

/**
 * Class Website
 *
 * @package GoMage\Core\Block\Adminhtml\Form\Element
 */
class Website extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var
     */
    private $helper;

    /**
     * Website constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \GoMage\Core\Helper\Data                $helper
     * @param array                                   $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \GoMage\Core\Helper\Data $helper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->helper = $helper;
    }

    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return $this->helper->getC($element);
    }

}
