<?php

namespace Collector\Iframe\Controller\Success;

class Index extends \Magento\Framework\App\Action\Action
{

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;
    /**
     * @var \Collector\Iframe\Helper\Data
     */
    protected $helper;
    /**
     * @var \Magento\Sales\Api\Data\OrderInterface
     */
    protected $orderInterface;
    /**
     * @var \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory
     */
    protected $quoteCollectionFactory;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;
    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;
    /**
     * @var \Magento\Quote\Model\Quote\Address\Rate
     */
    protected $shippingRate;
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;
    /**
     * @var \Magento\Framework\Event\Manager
     */
    protected $eventManager;
    /**
     * @var \Collector\Iframe\Model\State
     */
    protected $orderState;
    /**
     * @var \Magento\Quote\Model\QuoteManagement
     */
    protected $quoteManagement;
    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\OrderSender
     */
    protected $orderSender;
    /**
     * @var \Magento\Newsletter\Model\SubscriberFactory
     */
    protected $subscriberFactory;

    /**
     * @var \Collector\Base\Model\Session
     */
    protected $collectorSession;

    /**
     * @var \Collector\Base\Logger\Collector
     */
    protected $logger;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;


    /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    protected $redirect;

    /**
     * @var \Magento\Framework\App\Response\Http
     */
    protected $response;

    /**
     * @var \Collector\Base\Model\Config
     */
    protected $collectorConfig;

    /**
     * @var \Collector\Base\Model\ApiRequest
     */
    protected $apiRequest;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var array
     */
    protected $paymentToMethod = [
        'DirectInvoice' => 'collector_invoice',
        'PartPayment' => 'collector_partpay',
        'Account' => 'collector_account',
        'Card' => 'collector_card',
        'Bank' => 'collector_bank',
        'Campaign' => 'collector_campaign'
    ];

    /**
     * @var \Magento\Customer\Model\AddressFactory
     */
    protected $addressFactory;
    /**
     * @var \Collector\Iframe\Model\ResourceModel\Fraud\Collection
     */
    protected $fraudCollection;
	
	/**
     * @var \Magento\Customer\Api\AddressRepositoryInterface
     */
    protected $addressRepository;
	
	/**
     * @var \Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface
     */
    protected $transactionBuilder;
    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $cart;
    
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;
	
    /**
     * Index constructor.
     * @param \Collector\Base\Model\Config $collectorConfig
     * @param \Collector\Base\Model\ApiRequest $apiRequest
     * @param \Magento\Framework\App\ProductMetadataInterface $_productMetaData
     * @param \Collector\Iframe\Helper\Data $_helper
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Store\Model\StoreManagerInterface $_storeManager
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $_customerRepository
     * @param \Magento\Checkout\Model\Session $_checkoutSession
     * @param \Magento\Customer\Model\CustomerFactory $_customerFactory
     * @param \Magento\Quote\Model\QuoteManagement $quoteManagement
     * @param \Magento\Sales\Api\Data\OrderInterface $_orderInterface
     * @param \Collector\Base\Logger\Collector $logger
     * @param \Collector\Iframe\Model\ResourceModel\Fraud\Collection $fraudCollection
     * @param \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory $_quoteCollectionFactory
     * @param \Magento\Quote\Model\Quote\Address\Rate $_shippingRate
     * @param \Magento\Framework\Event\Manager $eventManager
     * @param \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender
     * @param \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory
     * @param \Collector\Base\Model\Session $_collectorSession
     * @param \Collector\Iframe\Model\State $orderState
     * @param \Magento\Framework\App\Response\Http $response
     * @param \Magento\Customer\Model\AddressFactory $addressFactory
     * @param \Magento\Customer\Model\Session $customerSession
	 * @param \Magento\Customer\Api\AddressRepositoryInterface $_addressRepository
     * @param \Magento\Checkout\Model\Cart $cart
	 * @param \Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface $transactionBuilder
     */
    public function __construct(
        \Collector\Base\Model\Config $collectorConfig,
        \Collector\Base\Model\ApiRequest $apiRequest,
        \Magento\Framework\App\ProductMetadataInterface $_productMetaData,
        \Collector\Iframe\Helper\Data $_helper,
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Store\Model\StoreManagerInterface $_storeManager,
        \Magento\Customer\Api\CustomerRepositoryInterface $_customerRepository,
        \Magento\Checkout\Model\Session $_checkoutSession,
        \Magento\Customer\Model\CustomerFactory $_customerFactory,
        \Magento\Quote\Model\QuoteManagement $quoteManagement,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Sales\Api\Data\OrderInterface $_orderInterface,
        \Collector\Base\Logger\Collector $logger,
        \Collector\Iframe\Model\ResourceModel\Fraud\Collection $fraudCollection,
        \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory $_quoteCollectionFactory,
        \Magento\Quote\Model\Quote\Address\Rate $_shippingRate,
        \Magento\Framework\Event\Manager $eventManager,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender,
        \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory,
        \Collector\Base\Model\Session $_collectorSession,
        \Collector\Iframe\Model\State $orderState,
        \Magento\Framework\App\Response\Http $response,
        \Magento\Customer\Model\AddressFactory $addressFactory,
        \Magento\Customer\Model\Session $customerSession,
		\Magento\Customer\Api\AddressRepositoryInterface $_addressRepository,
        \Magento\Checkout\Model\Cart $cart,
		\Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface $transactionBuilder
    ) {
        //ugly hack to remove compilation errors in Magento 2.1.x
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->messageManager = $objectManager->get('\Magento\Framework\Message\ManagerInterface');
        $this->redirect = $objectManager->get('\Magento\Framework\App\Response\RedirectInterface');
        //end of hack
		$this->transactionBuilder = $transactionBuilder;
        $this->cart = $cart;
		$this->addressRepository = $_addressRepository;
        $this->request = $request;
        $this->fraudCollection = $fraudCollection;
        $this->customerSession = $customerSession;
        $this->addressFactory = $addressFactory;
        $this->apiRequest = $apiRequest;
        $this->collectorConfig = $collectorConfig;
        $this->response = $response;
        $this->logger = $logger;
        $this->collectorSession = $_collectorSession;
        $this->orderSender = $orderSender;
        $this->subscriberFactory = $subscriberFactory;
        $this->orderState = $orderState;
        $this->quoteManagement = $quoteManagement;
        $this->helper = $_helper;
        $this->eventManager = $eventManager;
        $this->resultPageFactory = $resultPageFactory;
        $this->checkoutSession = $_checkoutSession;
        $this->orderInterface = $_orderInterface;
        $this->quoteCollectionFactory = $_quoteCollectionFactory;
        $this->storeManager = $_storeManager;
        $this->customerRepository = $_customerRepository;
        $this->customerFactory = $_customerFactory;
        $this->shippingRate = $_shippingRate;
        parent::__construct($context);
    }

    protected function getPaymentMethodByName($name)
    {
        return isset($this->paymentToMethod[$name]) ? $this->paymentToMethod[$name] : 'collector_base';
    }

    public function execute()
    {
        $order = $this->orderInterface->loadByIncrementId($this->request->getParam('OrderNo'));
        if ($order->getData('shown_success_page') == 1){
            return $this->redirect->redirect($this->response, '/');
        }
        $this->eventManager->dispatch(
            'checkout_onepage_controller_success_action',
            ['order_ids' => [$order->getId()]]
        );
        $this->checkoutSession->setLastOrderId($order->getId());
        $resultPage = $this->resultPageFactory->create();
        $order->setData('shown_success_page', 1);
        $order->save();
        return $resultPage;
    }
}
