<?php

namespace Collector\Iframe\Cron;

class RemoveOrders
{
    /**
     * @var \Collector\Base\Logger\Collector
     */
    protected $logger;
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $orderCollectionFactory;
    /**
     * @var \Magento\Framework\App\State
     */
    protected $appState;
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * RemoveOrders constructor.
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\State $appState
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     * @param \Collector\Base\Logger\Collector $logger
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\State $appState,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Collector\Base\Logger\Collector $logger
    ) {
        $this->logger = $logger;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->appState = $appState;
        $this->registry = $registry;
    }
    
    public function execute()
    {
        $tempRegistry = $this->registry->registry('isSecureArea');
        if (!$tempRegistry){
            $this->registry->unregister('isSecureArea');
            $this->registry->register('isSecureArea', true);
        }
        $orders = $this->orderCollectionFactory->create()->addFieldToSelect('*')->
            addFieldToFilter('status', array('eq' => 'collector_pending'))->
            addFieldToFilter('created_at', ['to' => new \Zend_Db_Expr('DATE_ADD(NOW(), INTERVAL -5 HOUR)')]);
        foreach ($orders as $order){
            $order->delete();
            $this->collectorLogger->log('info',"deleted order: " . $order->getIncrementId() . " due to not reciving notificaion callback");
        }
        if (!$tempRegistry){
            $this->registry->unregister('isSecureArea');
            $this->registry->register('isSecureArea', false);
        }
    }
}
