<?php

namespace Collector\Iframe\Controller\Notification;

class Index extends \Magento\Framework\App\Action\Action
{

    protected $paymentToMethod = [
        'DirectInvoice' => 'collector_invoice',
        'PartPayment' => 'collector_partpay',
        'Account' => 'collector_account',
        'Card' => 'collector_card',
        'BankTransfer' => 'collector_bank',
        'Campaign' => 'collector_campaign',
        'Trustly' => 'collector_trustly'
    ];

    protected $resultPageFactory;
    protected $jsonHelper;
    protected $layoutFactory;
    protected $resultJsonFactory;
    protected $helper;
    protected $formKey;
    protected $orderInterface;
    protected $quoteCollectionFactory;
    protected $storeManager;
    protected $customerFactory;
    protected $customerRepository;
    protected $shippingRate;
    protected $cartRepositoryInterface;
    protected $cartManagementInterface;
    protected $apiRequest;
    protected $collectorConfig;
    protected $orderState;
    protected $transactionBuilder;
    protected $request;
    protected $orderSender;

    /**
     * Index constructor.
     * @param \Magento\Framework\View\Result\LayoutFactory $_layoutFactory
     * @param \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Collector\Iframe\Helper\Data $_helper
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Quote\Api\CartRepositoryInterface $_cartRepositoryInterface
     * @param \Magento\Quote\Api\CartManagementInterface $_cartManagementInterface
     * @param \Magento\Framework\Data\Form\FormKey $formKey
     * @param \Magento\Store\Model\StoreManagerInterface $_storeManager
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $_customerRepository
     * @param \Magento\Checkout\Model\Session $_checkoutSession
     * @param \Magento\Customer\Model\CustomerFactory $_customerFactory
     * @param \Magento\Sales\Api\Data\OrderInterface $_orderInterface
     * @param \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory $_quoteCollectionFactory
     * @param \Magento\Quote\Model\Quote\Address\Rate $_shippingRate
     * @param \Collector\Base\Model\ApiRequest $apiRequest
     * @param \Collector\Base\Model\Config $collectorConfig
     * @param \Collector\Iframe\Model\State $orderState
	 * @param \Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface $transactionBuilder
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     */
    public function __construct(
        \Magento\Framework\View\Result\LayoutFactory $_layoutFactory,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender,
        \Magento\Framework\App\Request\Http $request,
        \Collector\Iframe\Helper\Data $_helper,
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Quote\Api\CartRepositoryInterface $_cartRepositoryInterface,
        \Magento\Quote\Api\CartManagementInterface $_cartManagementInterface,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Magento\Store\Model\StoreManagerInterface $_storeManager,
        \Magento\Customer\Api\CustomerRepositoryInterface $_customerRepository,
        \Magento\Checkout\Model\Session $_checkoutSession,
        \Magento\Customer\Model\CustomerFactory $_customerFactory,
        \Magento\Sales\Api\Data\OrderInterface $_orderInterface,
        \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory $_quoteCollectionFactory,
        \Magento\Quote\Model\Quote\Address\Rate $_shippingRate,
        \Collector\Base\Model\ApiRequest $apiRequest,
        \Collector\Base\Model\Config $collectorConfig,
        \Collector\Iframe\Model\State $orderState,
        \Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface $transactionBuilder,
        \Magento\Framework\Json\Helper\Data $jsonHelper
    ) {
        $this->formKey = $formKey;
        $this->orderSender = $orderSender;
        $this->request = $request;
        $this->helper = $_helper;
        $this->layoutFactory = $_layoutFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->transactionBuilder = $transactionBuilder;
        $this->apiRequest = $apiRequest;
        $this->checkoutSession = $_checkoutSession;
        $this->orderInterface = $_orderInterface;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->quoteCollectionFactory = $_quoteCollectionFactory;
        $this->storeManager = $_storeManager;
        $this->customerRepository = $_customerRepository;
        $this->customerFactory = $_customerFactory;
        $this->shippingRate = $_shippingRate;
        $this->cartRepositoryInterface = $_cartRepositoryInterface;
        $this->cartManagementInterface = $_cartManagementInterface;
        $this->collectorConfig = $collectorConfig;
        $this->orderState = $orderState;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $quote = $this->quoteCollectionFactory->create()->addFieldToFilter(
            "reserved_order_id",
            $this->request->getParam('OrderNo')
        )->getFirstItem();
        $order = $this->orderInterface->loadByIncrementId($this->request->getParam('OrderNo'));
        
        $response = $this->getResp($quote->getData('collector_private_id'), $quote->getData('collector_btype'));
        $quote->setPaymentMethod($this->getPaymentMethodByName($response['data']['purchase']['paymentName'])); //payment method
        $quote->getPayment()->importData(['method' => $this->getPaymentMethodByName($response['data']['purchase']['paymentName'])]);
        $fee = 0;
        if ($this->getPaymentMethodByName($response['data']['purchase']['paymentName']) == 'collector_invoice'){
            if ($response['data']['customerType'] == "PrivateCustomer"){
                $fee = $this->apiRequest->convert($this->collectorConfig->getInvoiceB2CFee(), null, 'SEK');
            }
            else {
                $fee = $this->apiRequest->convert($this->collectorConfig->getInvoiceB2BFee(), null, 'SEK');
            }
        }
        $quote->setFeeAmount($fee);
        $quote->setBaseFeeAmount($fee);
        $quote->save();

        $order->setFeeAmount($fee);
        $order->setBaseFeeAmount($fee);
        $order->setGrandTotal($order->getGrandTotal() + $fee);
        $order->setBaseGrandTotal($order->getBaseGrandTotal() + $fee);
        
        $this->setOrderStatusState($order, $response["data"]["purchase"]["result"]);
        $order->getPayment()->setMethod($this->getPaymentMethodByName($response['data']['purchase']['paymentName']));
        $order->getPayment()->save();
        $order->setCollectorInvoiceId($response['data']['purchase']['purchaseIdentifier']);

        if ($quote->getData('collector_btype') == \Collector\Base\Model\Session::B2B) {
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
        $payment->save();
        $quote->setIsActive(0);
        $order->save();
        
        
        $this->orderSender->send($order);
        return $resultPage;
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
    
    private function setOrderStatusState(&$order, $result = '')
    {
        try {
            switch ($result) {
                case "OnHold":
                    $activeStatus = $this->collectorConfig->getAcceptStatus();
                    $activeState = $this->orderState->load($activeStatus)->getState();
                    $order->setHoldBeforeState($activeState)->setHoldBeforeStatus($activeStatus);
                    $status = $this->collectorConfig->getHoldStatus();
                    $state = $this->orderState->load($status)->getState();
                    break;
                case "Preliminary":
                    $status = $this->collectorConfig->getAcceptStatus();
                    $state = $this->orderState->load($status)->getState();
                    break;
                default:
                    $status = $this->collectorConfig->getDeniedStatus();
                    $state = $this->orderState->load($status)->getState();
                    break;
            }
            $order->setState($state)->setStatus($status);
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }
}
