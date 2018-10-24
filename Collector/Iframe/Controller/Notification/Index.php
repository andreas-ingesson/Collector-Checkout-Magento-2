<?php

namespace Collector\Iframe\Controller\Notification;

class Index extends \Magento\Framework\App\Action\Action
{

    protected $paymentToMethod = [
        'DirectInvoice' => 'collector_invoice',
        'PartPayment' => 'collector_partpay',
        'Account' => 'collector_account',
        'Card' => 'collector_card',
        'Bank' => 'collector_bank',
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
        file_put_contents("var/log/coldev.log", "test 1\n", FILE_APPEND);
        $quote = $this->quoteCollectionFactory->create()->addFieldToFilter(
            "reserved_order_id",
            $this->request->getParam('OrderNo')
        )->getFirstItem();
        file_put_contents("var/log/coldev.log", "test 2\n", FILE_APPEND);
        $order = $this->orderInterface->loadByIncrementId($this->request->getParam('OrderNo'));
        file_put_contents("var/log/coldev.log", "test 3\n", FILE_APPEND);
        $response = $this->getResp($quote->getData('collector_private_id'), $quote->getData('collector_btype'));
        file_put_contents("var/log/coldev.log", "test 4\n", FILE_APPEND);
        
        $this->setOrderStatusState($order, $response["data"]["purchase"]["result"]);
        file_put_contents("var/log/coldev.log", "test 5\n", FILE_APPEND);
        $order->getPayment()->setMethod($this->getPaymentMethodByName($response['data']['purchase']['paymentName']));
        file_put_contents("var/log/coldev.log", "test 6\n", FILE_APPEND);
        $order->getPayment()->save();
        file_put_contents("var/log/coldev.log", "test 7\n", FILE_APPEND);
        $order->setCollectorInvoiceId($response['data']['purchase']['purchaseIdentifier']);
        file_put_contents("var/log/coldev.log", "test 8\n", FILE_APPEND);
        
        if ($quote->getData('collector_btype') == \Collector\Base\Model\Session::B2B) {
        file_put_contents("var/log/coldev.log", "test 9\n", FILE_APPEND);
            $order->setCollectorSsn($response['data']['businessCustomer']['organizationNumber']);
        file_put_contents("var/log/coldev.log", "test 10\n", FILE_APPEND);
        }
        file_put_contents("var/log/coldev.log", "test 11\n", FILE_APPEND);
        
        $payment = $order->getPayment();
        file_put_contents("var/log/coldev.log", "test 12\n", FILE_APPEND);
        $payment->setLastTransId($response['data']['purchase']['purchaseIdentifier']);
        file_put_contents("var/log/coldev.log", "test 13\n", FILE_APPEND);
        $payment->setTransactionId($response['data']['purchase']['purchaseIdentifier']);
        file_put_contents("var/log/coldev.log", "test 14\n", FILE_APPEND);
        $payment->setAdditionalInformation(
            [\Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS => (array) $response['data']['purchase']]
        );
        file_put_contents("var/log/coldev.log", "test 15\n", FILE_APPEND);
        $formatedPrice = $order->getBaseCurrency()->formatTxt(
            $order->getGrandTotal()
        );
        file_put_contents("var/log/coldev.log", "test 16\n", FILE_APPEND);
        
        $message = __('The authorized amount is %1.', $formatedPrice);
        file_put_contents("var/log/coldev.log", "test 17\n", FILE_APPEND);
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
        $this->orderSender->send($order);
                    ob_start();
var_dump($quote->getData('collector_btype'));
var_dump($quote->getData('collector_public_token'));
file_put_contents("var/log/coldev.log", "dump 4 ".ob_get_clean()."\n", FILE_APPEND);
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
