<?php
namespace GoMage\Core\Observer;
use GoMage\Core\Model\Notification\Send;
use Magento\Framework\Event\ObserverInterface;
/**
 * Class NotificationObserver
 * @package GoMage\Core\Observer
 */
class NotificationObserver implements ObserverInterface
{
    /**
     * @var Send
     */
    protected $send;

    /**
     * NotificationObserver constructor.
     * @param Send $send
     */
    public function __construct(
        Send $send
    ) {
        $this->send = $send;
    }
    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->send->notify();
    }
}