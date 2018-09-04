<?php

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
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return $this->helper->getC($element);
    }

}
