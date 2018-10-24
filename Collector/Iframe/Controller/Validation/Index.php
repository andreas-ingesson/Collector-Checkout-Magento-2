<?php

namespace Collector\Iframe\Controller\Validation;

class Index extends \Magento\Framework\App\Action\Action
{
    
    protected $paymentToMethod = [
        'DirectInvoice' => 'collector_invoice',
        'PartPayment' => 'collector_partpay',
        'Account' => 'collector_account',
        'Card' => 'collector_card',
        'Bank' => 'collector_bank',
    ];

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;
    /**
     * @var \Collector\Iframe\Helper\Data
     */
    protected $helper;
    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    protected $filterBuilder;
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
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $cartRepositoryInterface;
    /**
     * @var \Magento\Framework\Event\Manager
     */
    protected $eventManager;
    /**
     * @var \Magento\Quote\Api\CartManagementInterface
     */
    protected $cartManagementInterface;
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;
    /**
     * @var \Collector\Base\Model\Session
     */
    protected $collectorSession;
    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $order;
    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\OrderSender
     */
    protected $orderSender;

    /**
     * @var \Collector\Base\Model\ApiRequest
     */
    protected $apiRequest;

    /**
     * @var \Collector\Base\Logger\Collector
     */
    protected $collectorLogger;

    /**
     * @var \Collector\Iframe\Model\FraudFactory
     */
    protected $fraudFactory;
    /**
     * @var \Collector\Iframe\Model\CheckerFactory
     */
    protected $checkerFactory;
	/**
     * @var \Collector\Base\Logger\Collector
     */
    protected $config;
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;
    /**
     * @var \Collector\Base\Model\Config
     */
    protected $collectorConfig;
    /**
     * @var \Magento\Customer\Model\AddressFactory
     */
    protected $addressFactory;
    /**
     * @var \Magento\Quote\Model\QuoteManagement
     */
    protected $quoteManagement;
    /**
     * @var \Collector\Iframe\Model\State
     */
    protected $orderState;
    
    /**
     * Index constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Collector\Base\Model\Config $collectorConfig
     * @param \Collector\Iframe\Helper\Data $_helper
     * @param \Magento\Framework\Api\FilterBuilder $_filterBuilder
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Sales\Model\Order $order
     * @param \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender
     * @param \Magento\Quote\Api\CartRepositoryInterface $_cartRepositoryInterface
     * @param \Magento\Quote\Api\CartManagementInterface $_cartManagementInterface
     * @param \Magento\Store\Model\StoreManagerInterface $_storeManager
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $_customerRepository
     * @param \Magento\Checkout\Model\Session $_checkoutSession
     * @param \Magento\Customer\Model\CustomerFactory $_customerFactory
     * @param \Magento\Sales\Api\Data\OrderInterface $_orderInterface
     * @param \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory $_quoteCollectionFactory
     * @param \Magento\Quote\Model\Quote\Address\Rate $_shippingRate
     * @param \Magento\Framework\Event\Manager $eventManager
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Collector\Base\Model\Session $_collectorSession
     * @param \Collector\Base\Model\ApiRequest $apiRequest
     * @param \Collector\Base\Logger\Collector $logger
     * @param \Collector\Iframe\Model\FraudFactory $fraudFactory
     * @param \Collector\Iframe\Model\CheckerFactory $checkerFactory
     * @param \Magento\Quote\Model\QuoteManagement $quoteManagement
     * @param \Magento\Customer\Model\AddressFactory $addressFactory
     * @param \Collector\Iframe\Model\State $orderState
	 * @param \Collector\Base\Model\Config $_config
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Collector\Base\Model\Config $collectorConfig,
        \Collector\Iframe\Helper\Data $_helper,
        \Magento\Framework\Api\FilterBuilder $_filterBuilder,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Sales\Model\Order $order,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender,
        \Magento\Quote\Api\CartRepositoryInterface $_cartRepositoryInterface,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Quote\Api\CartManagementInterface $_cartManagementInterface,
        \Magento\Store\Model\StoreManagerInterface $_storeManager,
        \Magento\Customer\Api\CustomerRepositoryInterface $_customerRepository,
        \Magento\Checkout\Model\Session $_checkoutSession,
        \Magento\Customer\Model\CustomerFactory $_customerFactory,
        \Magento\Sales\Api\Data\OrderInterface $_orderInterface,
        \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory $_quoteCollectionFactory,
        \Magento\Quote\Model\Quote\Address\Rate $_shippingRate,
        \Magento\Framework\Event\Manager $eventManager,
        \Magento\Framework\App\Request\Http $request,
        \Collector\Base\Model\Session $_collectorSession,
        \Collector\Base\Model\ApiRequest $apiRequest,
        \Collector\Base\Logger\Collector $logger,
        \Collector\Iframe\Model\FraudFactory $fraudFactory,
        \Collector\Iframe\Model\CheckerFactory $checkerFactory,
        \Magento\Quote\Model\QuoteManagement $quoteManagement,
        \Magento\Customer\Model\AddressFactory $addressFactory,
        \Collector\Iframe\Model\State $orderState,
		\Collector\Base\Model\Config $_config
    ) {
        $this->checkerFactory = $checkerFactory;
		$this->config = $_config;
        $this->fraudFactory = $fraudFactory;
        $this->collectorLogger = $logger;
        $this->apiRequest = $apiRequest;
        $this->orderState = $orderState;
        $this->request = $request;
        $this->collectorSession = $_collectorSession;
        $this->order = $order;
        $this->orderSender = $orderSender;
        $this->quoteManagement = $quoteManagement;
        $this->resultPageFactory = $resultPageFactory;
        $this->helper = $_helper;
        $this->collectorConfig = $collectorConfig;
        $this->filterBuilder = $_filterBuilder;
        $this->eventManager = $eventManager;
        $this->checkoutSession = $_checkoutSession;
        $this->orderInterface = $_orderInterface;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->quoteCollectionFactory = $_quoteCollectionFactory;
        $this->storeManager = $_storeManager;
        $this->customerRepository = $_customerRepository;
        $this->customerFactory = $_customerFactory;
        $this->addressFactory = $addressFactory;
        $this->shippingRate = $_shippingRate;
        $this->cartRepositoryInterface = $_cartRepositoryInterface;
        $this->cartManagementInterface = $_cartManagementInterface;
        parent::__construct($context);
    }

    public function execute()
    {
        $order = $this->orderInterface->loadByIncrementId($this->request->getParam('OrderNo'));
        $resultPage = $this->resultJsonFactory->create();
        
        if ($order->getId()) {
            $order->delete();
        }
        $quote = $this->quoteCollectionFactory->create()->addFieldToFilter(
            "reserved_order_id",
            $this->request->getParam('OrderNo')
        )->getFirstItem();
        
        
        try {
            $order = null;
            if (empty($quote->getData('collector_public_token'))) {
                $this->collectorLogger->error('Error while public_token loading');
                $return = array(
                    'title' => "Session Has Expired",
                    'message' => "Please reload the page"
                );
                $result = $this->resultJsonFactory->create();
                $result->setHttpResponseCode(500);
                return $result->setData($return);
            }
            $response = $this->getResp($quote->getData('collector_private_id'), $quote->getData('collector_btype'));
            $createAccount = $this->collectorConfig->createAccount();
            if ($response["code"] == 0) {
                $this->collectorLogger->error($response['error']);
                $return = array(
                    'title' => "Could not place Order",
                    'message' => $response['error']
                );
                $result = $this->resultJsonFactory->create();
                $result->setHttpResponseCode(500);
                return $result->setData($return);
            }
            $actual_quote = $this->quoteCollectionFactory->create()->addFieldToFilter(
                "reserved_order_id",
                $response['data']['reference']
            )->getFirstItem();
            //set payment method
            $paymentMethod = $this->getPaymentMethodByName($response['data']['purchase']['paymentName']);
            $shippingCountryId = $this->getCountryCodeByName(
                $response['data']['customer']['deliveryAddress']['country'],
                $response['data']['countryCode']
            );
            $billingCountryId = $this->getCountryCodeByName(
                $response['data']['customer']['billingAddress']['country'],
                $response['data']['countryCode']
            );

            //check countries
            if (!$this->collectorConfig->isShippingAddressEnabled() && empty($shippingCountryId)
                || empty($billingCountryId)) {
                $return = array(
                    'title' => "Could not place Order",
                    'message' => "Missing country information"
                );
                $result = $this->resultJsonFactory->create();
                $result->setHttpResponseCode(500);
                return $result->setData($return);
            }
            if (empty($actual_quote)) {
                $return = array(
                    'title' => "Session Has Expired",
                    'message' => "Please reload the page"
                );
                $result = $this->resultJsonFactory->create();
                $result->setHttpResponseCode(500);
                return $result->setData($return);
            }

            //init the store id and website id
            $store = $this->storeManager->getStore();
            $websiteId = $store->getWebsiteId();

            //init the customer
            $customer = $this->customerFactory->create();
            $customer->setWebsiteId($websiteId);
            if (empty($response["data"]["customerType"])) {
                $return = array(
                    'title' => "Could not place Order",
                    'message' => "Incorrect user data"
                );
                $result = $this->resultJsonFactory->create();
                $result->setHttpResponseCode(500);
                return $result->setData($return);
            }
            switch ($response["data"]["customerType"]) {
                case "PrivateCustomer":
                    $email = $response['data']['customer']['email'];
                    $firstname = $response['data']['customer']['billingAddress']['firstName'];
                    $lastname = $response['data']['customer']['billingAddress']['lastName'];
                    break;
                case "BusinessCustomer":
                    $email = $response['data']['businessCustomer']['email'];
                    $firstname = $response['data']['businessCustomer']['firstName'];
                    $lastname = $response['data']['businessCustomer']['lastName'];
                    break;
                default:
                    $return = array(
                        'title' => "Could not place Order",
                        'message' => "Incorrect user data"
                    );
                    $result = $this->resultJsonFactory->create();
                    $result->setHttpResponseCode(500);
                    return $result->setData($return);
                    break;
            }
            if (!$this->collectorConfig->isShippingAddressEnabled()) {
                if (isset($response['data']['businessCustomer']['invoiceAddress'])) {
                    $shippingAddressArr = [
                        'company' => $response['data']['businessCustomer']['deliveryAddress']['companyName'],
                        'firstname' => $response['data']['businessCustomer']['firstName'],
                        'lastname' => $response['data']['businessCustomer']['lastName'],
                        'street' => $response['data']['businessCustomer']['deliveryAddress']['address'],
                        'city' => $response['data']['businessCustomer']['deliveryAddress']['city'],
                        'postcode' => $response['data']['businessCustomer']['deliveryAddress']['postalCode'],
                        'telephone' => $response['data']['businessCustomer']['mobilePhoneNumber'],
                        'country_id' => $response['data']['countryCode'],
                        'same_as_billing' => 0
                    ];
                } else {
                    $shippingAddressArr = [
                        'company' => '',
                        'firstname' => $response['data']['customer']['deliveryAddress']['firstName'],
                        'lastname' => $response['data']['customer']['deliveryAddress']['lastName'],
                        'street' => $response['data']['customer']['deliveryAddress']['address'],
                        'city' => $response['data']['customer']['deliveryAddress']['city'],
                        'postcode' => $response['data']['customer']['deliveryAddress']['postalCode'],
                        'telephone' => $response['data']['customer']['mobilePhoneNumber'],
                        'country_id' => $response['data']['countryCode'],
                        'same_as_billing' => 0
                    ];
                }
                $actual_quote->getShippingAddress()->addData($shippingAddressArr);

                // Collect Rates and Set Shipping & Payment Method
                $this->shippingRate->setCode($actual_quote->getShippingAddress()->getShippingMethod())->getPrice();
                $shippingAddress = $actual_quote->getShippingAddress();

                $actual_quote->getShippingAddress()->addShippingRate($this->shippingRate);
                $actual_quote->getShippingAddress()->save();
            }
            if (isset($response['data']['businessCustomer']['invoiceAddress'])) {
                $billingAddress = array(
                    'company' => $response['data']['businessCustomer']['invoiceAddress']['companyName'],
                    'firstname' => $response['data']['businessCustomer']['firstName'],
                    'lastname' => $response['data']['businessCustomer']['lastName'],
                    'street' => isset($response['data']['businessCustomer']['invoiceAddress']['address']) ?
                        $response['data']['businessCustomer']['invoiceAddress']['address']
                        : $response['data']['businessCustomer']['invoiceAddress']['postalCode'],
                    'city' => $response['data']['businessCustomer']['invoiceAddress']['city'],
                    'country_id' => $response['data']['countryCode'],
                    'postcode' => $response['data']['businessCustomer']['invoiceAddress']['postalCode'],
                    'telephone' => $response['data']['businessCustomer']['mobilePhoneNumber']
                );
            } else {
                $billingAddress = array(
                    'company' => '',
                    'firstname' => $response['data']['customer']['billingAddress']['firstName'],
                    'lastname' => $response['data']['customer']['billingAddress']['lastName'],
                    'street' => $response['data']['customer']['billingAddress']['address'],
                    'city' => $response['data']['customer']['billingAddress']['city'],
                    'country_id' => $response['data']['countryCode'],
                    'postcode' => $response['data']['customer']['billingAddress']['postalCode'],
                    'telephone' => $response['data']['customer']['mobilePhoneNumber']
                );
            }
            //load customer by email address
            $customer->loadByEmail($email);
            
            if ($this->collectorConfig->getUpdateDbCustomer() && $customer->getEntityId() !== null){
                $shippingAddressExists = false;
                $billingAddressExists = false;
                foreach ($customer->getAddresses() as $address){
                    $addArr = $address->toArray();
                    if (isset($shippingAddressArr)){
                        if ($shippingAddressArr['street'] == $addArr['street'] && 
                        $shippingAddressArr['postcode'] == $addArr['postcode'] && 
                        $shippingAddressArr['firstname'] == $addArr['firstname'] && 
                        $shippingAddressArr['lastname'] == $addArr['lastname'] && 
                        $shippingAddressArr['city'] == $addArr['city']){
                            $shippingAddressExists = true;
                        }
                    }
                    if ($billingAddress['street'] == $addArr['street'] && 
                    $billingAddress['postcode'] == $addArr['postcode'] && 
                    $billingAddress['firstname'] == $addArr['firstname'] && 
                    $billingAddress['lastname'] == $addArr['lastname'] && 
                    $billingAddress['city'] == $addArr['city']){
                        $billingAddressExists = true;
                    }
                }
                if (!$shippingAddressExists){
                    if (isset($shippingAddressArr)) {
                        $cShippingAddress = $this->addressFactory->create();
                        $cShippingAddress->setCustomerId($customer->getId());
                        $cShippingAddress->setFirstname($firstname);
                        $cShippingAddress->setLastname($lastname);
                        $cShippingAddress->setCountryId($response['data']['countryCode']);
                        $cShippingAddress->setPostcode($shippingAddressArr['postcode']);
                        $cShippingAddress->setCity($shippingAddressArr['city']);
                        $cShippingAddress->setTelephone($shippingAddressArr['telephone']);
                        if ($shippingAddressArr['company'] != '') {
                            $cShippingAddress->setCompany($shippingAddressArr['company']);
                        }
                        $cShippingAddress->setStreet($shippingAddressArr['street']);
                        $cShippingAddress->setIsDefaultShipping('1');
                        $cShippingAddress->setSaveInAddressBook('1');
                        $cShippingAddress->save();
                        $customer->setDefaultShipping($cShippingAddress->getId());
                        $customer->save();
                    }
                }
                if (!$billingAddressExists){
                    $cBillingAddress = $this->addressFactory->create();
                    $cBillingAddress->setCustomerId($customer->getId());
                    $cBillingAddress->setFirstname($firstname);
                    $cBillingAddress->setLastname($lastname);
                    $cBillingAddress->setCountryId($response['data']['countryCode']);
                    $cBillingAddress->setPostcode($billingAddress['postcode']);
                    $cBillingAddress->setCity($billingAddress['city']);
                    $cBillingAddress->setTelephone($billingAddress['telephone']);
                    if ($billingAddress['company'] != '') {
                        $cBillingAddress->setCompany($billingAddress['company']);
                    }
                    $cBillingAddress->setStreet($billingAddress['street']);
                    $cBillingAddress->setIsDefaultBilling('1');
                    $cBillingAddress->setSaveInAddressBook('1');
                    $cBillingAddress->save();
                    $customer->setDefaultBilling($cBillingAddress->getId());
                    $customer->save();
                }
            }
            
            
            //check the customer
            if (!$customer->getEntityId() && $createAccount) {
                //If not avilable then create this customer
                $customer->setWebsiteId($websiteId)
                    ->setStore($store)
                    ->setFirstname($firstname)
                    ->setLastname($lastname)
                    ->setEmail($email)
                    ->setPassword($email);
                $customer->save();
                
                if ($this->collectorConfig->getUpdateDbCustomer()){
                    if (isset($shippingAddressArr)) {
                        $cShippingAddress = $this->addressFactory->create();
                        $cShippingAddress->setCustomerId($customer->getId());
                        $cShippingAddress->setFirstname($firstname);
                        $cShippingAddress->setLastname($lastname);
                        $cShippingAddress->setCountryId($response['data']['countryCode']);
                        $cShippingAddress->setPostcode($shippingAddressArr['postcode']);
                        $cShippingAddress->setCity($shippingAddressArr['city']);
                        $cShippingAddress->setTelephone($shippingAddressArr['telephone']);
                        if ($shippingAddressArr['company'] != '') {
                            $cShippingAddress->setCompany($shippingAddressArr['company']);
                        }
                        $cShippingAddress->setStreet($shippingAddressArr['street']);
                        $cShippingAddress->setIsDefaultShipping('1');
                        $cShippingAddress->setSaveInAddressBook('1');
                        $cShippingAddress->save();
                        $customer->setDefaultShipping($cShippingAddress->getId());
                        $customer->save();
                    }
                    $cBillingAddress = $this->addressFactory->create();
                    $cBillingAddress->setCustomerId($customer->getId());
                    $cBillingAddress->setFirstname($firstname);
                    $cBillingAddress->setLastname($lastname);
                    $cBillingAddress->setCountryId($response['data']['countryCode']);
                    $cBillingAddress->setPostcode($billingAddress['postcode']);
                    $cBillingAddress->setCity($billingAddress['city']);
                    $cBillingAddress->setTelephone($billingAddress['telephone']);
                    if ($billingAddress['company'] != '') {
                        $cBillingAddress->setCompany($billingAddress['company']);
                    }
                    $cBillingAddress->setStreet($billingAddress['street']);
                    $cBillingAddress->setIsDefaultBilling('1');
                    $cBillingAddress->setSaveInAddressBook('1');
                    $cBillingAddress->save();
                    $customer->setDefaultBilling($cBillingAddress->getId());
                    $customer->save();
                }
            }
            file_put_contents("var/log/coldev.log", "test 1\n", FILE_APPEND);
            if ($actual_quote->getData('newsletter_signup') == 1) {
                $this->subscriberFactory->create()->subscribe($response['data']['customer']['email']);
            }
            if ($createAccount) {
                $customer->setEmail($email);
                $customer->save();
                $customer = $this->customerRepository->getById($customer->getEntityId());
                $actual_quote->assignCustomer($customer);
            }

            //Set Address to quote @todo add section in order data for seperate billing and handle it

            $actual_quote->getBillingAddress()->addData($billingAddress);
            $actual_quote->setPaymentMethod($paymentMethod); //payment method
            $actual_quote->getPayment()->importData(['method' => $paymentMethod]);
            $actual_quote->setReservedOrderId($response['data']['reference']);
            if ($createAccount) {
                $actual_quote->getBillingAddress()->setCustomerId($customer->getId());
                $actual_quote->getShippingAddress()->setCustomerId($customer->getId());
            }

            $fee = 0;
            if ($this->getPaymentMethodByName($response['data']['purchase']['paymentName']) == 'collector_invoice'){
                if ($response['data']['customerType'] == "PrivateCustomer"){
                    $fee = $this->apiRequest->convert($this->collectorConfig->getInvoiceB2CFee(), null, 'SEK');
                }
                else {
                    $fee = $this->apiRequest->convert($this->collectorConfig->getInvoiceB2BFee(), null, 'SEK');
                }
            }

            $actual_quote->setFeeAmount($fee);
            $actual_quote->setBaseFeeAmount($fee);

            if (!$createAccount) {
                $actual_quote->setCustomerId(null);
                $actual_quote->setCustomerEmail($email);
                $actual_quote->setCustomerIsGuest(true);
                $actual_quote->setCustomerGroupId(\Magento\Customer\Api\Data\GroupInterface::NOT_LOGGED_IN_ID);
                $actual_quote->setCheckoutMethod(\Magento\Quote\Api\CartManagementInterface::METHOD_GUEST);
            }
ob_start();
var_dump($actual_quote->getData('collector_btype'));
var_dump($actual_quote->getData('collector_public_token'));
file_put_contents("var/log/coldev.log", "dump 1 ".ob_get_clean()."\n", FILE_APPEND);
            
            $actual_quote->save();
            $order = $this->quoteManagement->submit($actual_quote);
            $order->setData('is_iframe', 1);
            
ob_start();
var_dump($actual_quote->getData('collector_btype'));
var_dump($actual_quote->getData('collector_public_token'));
file_put_contents("var/log/coldev.log", "dump 2 ".ob_get_clean()."\n", FILE_APPEND);

            
            $order->setFeeAmount($fee);
            $order->setBaseFeeAmount($fee);
            $order->setGrandTotal($order->getGrandTotal() + $fee);
            $order->setBaseGrandTotal($order->getBaseGrandTotal() + $fee);
            
            
            
            $pendingStatus = $this->collectorConfig->getPendingStatus();
            $pendingState = $this->orderState->load($pendingStatus)->getState();
            $order->setState($pendingState)->setStatus($pendingStatus);
            $order->save();
ob_start();
var_dump($actual_quote->getData('collector_btype'));
var_dump($actual_quote->getData('collector_public_token'));
file_put_contents("var/log/coldev.log", "dump 3 ".ob_get_clean()."\n", FILE_APPEND);
            //REMOVE THIS AND PLACE SOME IN NOTIFICATION CALLBACK AND SOME IN SUCCESS/INDEX
            /*
            $this->orderSender->send($order);
            $order->setCollectorInvoiceId($response['data']['purchase']['purchaseIdentifier']);

            if ($this->collectorSession->getBtype('') == \Collector\Base\Model\Session::B2B) {
                $order->setCollectorSsn($response['data']['businessCustomer']['organizationNumber']);
            }

			$payment = $order->getPayment();
            $payment->setLastTransId($response['data']['purchase']['purchaseIdentifier']);
            $payment->setTransactionId($response['data']['purchase']['purchaseIdentifier']);
            $payment->setAdditionalInformation(
                [\Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS => (array) $response['data']['purchase']]
            );
            $formatedPrice = $order->getBaseCurrency()->formatTxt(
                $order->getGrandTotal()
            );
 
            $message = __('The authorized amount is %1.', $formatedPrice);
            //get the object of builder class
            $trans = $this->transactionBuilder;
            $transaction = $trans->setPayment($payment)
            ->setOrder($order)
            ->setTransactionId($response['data']['purchase']['purchaseIdentifier'])
            ->setAdditionalInformation(
                [\Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS => (array) $response['data']['purchase']]
            )
            ->setFailSafe(true)
            //build method creates the transaction and returns the object
            ->build(\Magento\Sales\Model\Order\Payment\Transaction::TYPE_AUTH);
 
            $payment->addTransactionCommentsToOrder(
                $transaction,
                $message
            );
            $payment->setParentTransactionId(null);
            $payment->save();
            $order->save();
			
            $order->setFeeAmount($fee);
            $order->setBaseFeeAmount($fee);

            $order->setGrandTotal($order->getGrandTotal() + $fee);
            $order->setBaseGrandTotal($order->getBaseGrandTotal() + $fee);
            if (!$this->setOrderStatusState($order, $response["data"]["purchase"]["result"])) {
                $return = array(
                    'title' => "Could not place Order",
                    'message' => "unknown orderstatus"
                );
                $result = $this->resultJsonFactory->create();
                $result->setHttpResponseCode(500);
                return $result->setData($return);
            }
            $order->save();

            $this->eventManager->dispatch(
                'checkout_onepage_controller_success_action',
                ['order_ids' => [$order->getId()]]
            );
            $this->checkoutSession->setLastOrderId($order->getId());

            
            $fraud = $this->fraudCollection->addFieldToFilter('increment_id', $response['data']['reference'])
                ->getFirstItem();
            if ($fraud->getId()) {
                if ($fraud->getStatus() == 1) {
                    $this->setOrderStatusState($order, 'Preliminary');
                } elseif ($fraud->getStatus() == 2) {
                    $this->setOrderStatusState($order, 'OnHold');
                } else {
                    $this->setOrderStatusState($order, '');
                }
            }
            $order->save();
            */
            $resp = array(
                'orderReference' => $order->getIncrementId()
            );
            return $resultPage->setData($resp);
        } catch (\Exception $e) {
            file_put_contents("var/log/coldev.log", $e->getMessage() . "\n" . $e->getTraceAsString() . "\n", FILE_APPEND);
            $this->collectorLogger->error($e->getMessage());
            $return = array(
                'title' => "Could not place Order",
                'message' => $e->getMessage()
            );
            $result = $this->resultJsonFactory->create();
            $result->setHttpResponseCode(500);
            return $result->setData($return);
        }
        $resp = array(
            'orderReference' => $order->getIncrementId()
        );
        return $resultPage->setData($resp);
    }
    
    public function getResp($privId, $btype)
    {
        if ($privId) {
            $data = $this->apiRequest->callCheckouts(null, $privId, $btype);
            if ($data["data"]) {
                $result['code'] = 1;
                $result['id'] = $data["id"];
                $result['data'] = $data["data"];
            } else {
                $result['code'] = 0;
                $result['error'] = $data["error"];
            }
            return $result;
        }
        return [];
    }
    
    protected function getPaymentMethodByName($name)
    {
        return isset($this->paymentToMethod[$name]) ? $this->paymentToMethod[$name] : 'collector_base';
    }
    
    private function getCountryCodeByName($name, $default)
    {
        $id = $default;
        switch ($name) {
            case 'Sverige':
                $id = 'SE';
                break;
            case 'Norge':
                $id = 'NO';
                break;
            case 'Suomi':
                $id = 'FI';
                break;
            case 'Deutschland':
                $id = 'DE';
                break;
        }
        return $id;
    }
}
