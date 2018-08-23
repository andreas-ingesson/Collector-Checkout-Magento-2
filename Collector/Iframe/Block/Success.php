<?php

namespace Collector\Iframe\Block;

class Success extends \Magento\Checkout\Block\Onepage
{
    protected $storeManager;
    /**
     * @var \Collector\Iframe\Helper\Data
     */
    protected $helper;
    /**
     * @var \Magento\Quote\Model\Quote\Address\Rate
     */
    protected $shippingRate;

    /**
     * @var \Collector\Base\Model\Session
     */
    protected $collectorSession;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;
    
    /**
     * @var \Collector\Base\Logger\Collector
     */
    protected $logger;

    /**
     * Success constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Quote\Model\Quote\Address\Rate $shippingRate
     * @param \Magento\Framework\Data\Form\FormKey $formKey
     * @param \Magento\Checkout\Model\CompositeConfigProvider $configProvider
     * @param \Collector\Iframe\Helper\Data $_helper
     * @param \Collector\Base\Logger\Collector $logger
     * @param \Collector\Base\Model\Session $_collectorSession
     * @param \Magento\Checkout\Model\Session $_checkoutSession
     * @param array $data
     * @param array $layoutProcessors
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Quote\Model\Quote\Address\Rate $shippingRate,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Magento\Checkout\Model\CompositeConfigProvider $configProvider,
        \Collector\Iframe\Helper\Data $_helper,
        \Collector\Base\Logger\Collector $logger,
        \Collector\Base\Model\Session $_collectorSession,
        \Magento\Checkout\Model\Session $_checkoutSession,
        array $data = [],
        array $layoutProcessors = []
    ) {
        parent::__construct($context, $formKey, $configProvider, $layoutProcessors, $data);
        $this->collectorSession = $_collectorSession;
        $this->logger = $logger;
        $this->helper = $_helper;
        $this->checkoutSession = $_checkoutSession;
        $this->shippingRate = $shippingRate;
        $this->storeManager = $context->getStoreManager();
    }

    public function &getCollectorSession()
    {
        return $this->collectorSession;
    }

    public function getStoreBaseUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl();
    }
    
    public function clearSession(){
        $this->checkoutSession->clearStorage();
        $this->checkoutSession->clearQuote();
    }
}
