<?php

namespace GoMage\Core\Model\Notification;

use GoMage\Core\Helper\Data;
use GoMage\Core\Model\Processors\ProcessorN;
use GoMage\Core\Model\CurlFix as Curl;

class Send
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var ProcessorN
     */
    protected $processorN;

    /**
     * @var Curl
     */
    protected $curl;

    /**
     * Send constructor.
     * @param Data $helper
     * @param ProcessorN $processorN
     * @param Curl $curl
     */
    public function __construct(
        Data $helper,
        ProcessorN $processorN,
        Curl $curl

    )
    {
        $this->helper = $helper;
        $this->processorN = $processorN;
        $this->curl = $curl;

    }

    /**
     * send notify
     */
    public function notify()
    {
        if($this->helper->isNotifyD()) {
            $this->processorN->process($this->curl, $this->helper);
        }
    }
}