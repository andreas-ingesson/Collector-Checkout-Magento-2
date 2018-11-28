<?php

namespace Collector\Iframe\Block;

class Success extends \Magento\Checkout\Block\Onepage
{
    protected $languageArray = [
        "NO" => "nb-NO",
        "SE" => "sv",
        "FI" => "fi-FI",
        "DK" => "en-DK",
        "DE" => "en-DE"
    ];
    
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
     * @var \Magento\Checkout\Model\Cart
     */
    protected $cart;
    /**
     * @var \Collector\Base\Model\Config
     */
    protected $collectorConfig;
    /**
     * @var \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory
     */
    protected $quoteCollectionFactory;
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;
    
    /**
     * @var \Magento\Framework\App\Response\Http
     */
    protected $response;
    /**
     * @var \Magento\Sales\Api\Data\OrderInterface
     */
    protected $orderInterface;
    
    /**
     * Success constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Quote\Model\Quote\Address\Rate $shippingRate
     * @param \Magento\Framework\Data\Form\FormKey $formKey
     * @param \Magento\Checkout\Model\CompositeConfigProvider $configProvider
     * @param \Collector\Iframe\Helper\Data $_helper
     * @param \Magento\Checkout\Model\Cart $_cart
     * @param \Collector\Base\Model\Config $collectorConfig
     * @param \Collector\Base\Logger\Collector $logger
     * @param \Collector\Base\Model\Session $_collectorSession
     * @param \Magento\Checkout\Model\Session $_checkoutSession
     * @param \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory $_quoteCollectionFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param array $data
     * @param array $layoutProcessors
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Quote\Model\Quote\Address\Rate $shippingRate,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Magento\Sales\Api\Data\OrderInterface $_orderInterface,
        \Magento\Checkout\Model\CompositeConfigProvider $configProvider,
        \Collector\Iframe\Helper\Data $_helper,
        \Magento\Checkout\Model\Cart $_cart,
        \Collector\Base\Model\Config $collectorConfig,
        \Collector\Base\Logger\Collector $logger,
        \Collector\Base\Model\Session $_collectorSession,
        \Magento\Framework\App\Response\Http $response,
        \Magento\Checkout\Model\Session $_checkoutSession,
        \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory $_quoteCollectionFactory,
        \Magento\Customer\Model\Session $customerSession,
        array $data = [],
        array $layoutProcessors = []
    ) {
        //ugly hack to remove compilation errors in Magento 2.1.x
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->redirect = $objectManager->get('\Magento\Framework\App\Response\RedirectInterface');
        //end of hack
        parent::__construct($context, $formKey, $configProvider, $layoutProcessors, $data);
        $this->collectorSession = $_collectorSession;
        $this->logger = $logger;
        $this->helper = $_helper;
        $this->checkoutSession = $_checkoutSession;
        $this->request = $request;
        $this->collectorConfig = $collectorConfig;
        $this->response = $response;
        $this->shippingRate = $shippingRate;
        $this->customerSession = $customerSession;
        $this->quoteCollectionFactory = $_quoteCollectionFactory;
        $this->cart = $_cart;
        $this->orderInterface = $_orderInterface;
        $this->storeManager = $context->getStoreManager();
    }

    public function &getCollectorSession()
    {
        return $this->collectorSession;
    }
    
    public function getLanguage()
    {
        $lang = $this->collectorConfig->getCountryCode();
        if (!empty($this->languageArray[$lang])) {
            return $this->languageArray[$lang];
        }
        return null;
    }

    public function getStoreBaseUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl();
    }
    
    public function getQuote(){
        
        $quote = $this->quoteCollectionFactory->create()->addFieldToFilter(
            "reserved_order_id",
            $this->request->getParam('OrderNo')
        )->getFirstItem();
        return $quote;
    }
    
    public function getCheckoutUrl()
    {
        return $this->collectorConfig->getCheckoutUrl();
    }
    
    public function clearSession(){
        $this->checkoutSession->clearStorage();
        $this->checkoutSession->clearQuote();
        $this->collectorSession->unsCollectorPublicToken();
        $this->collectorSession->expireSessionCookie();
        $this->collectorSession->destroy();
        $quote = $this->getQuote();
        $quote->setIsActive(0);
    }
}
