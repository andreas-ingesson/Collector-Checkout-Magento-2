<?php
/**
 * A Magento 2 module named Collector/Iframe
 * Copyright (C) 2017 Collector
 *
 * This file is part of Collector/Iframe.
 *
 * Collector/Iframe is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

namespace Collector\Iframe\Controller\CollectorInvoiceStatus;

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
     * Index constructor.
     * @param \Magento\Framework\App\Action\Context $context
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
	 * @param \Collector\Base\Model\Config $_config
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Collector\Iframe\Helper\Data $_helper,
        \Magento\Framework\Api\FilterBuilder $_filterBuilder,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Sales\Model\Order $order,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender,
        \Magento\Quote\Api\CartRepositoryInterface $_cartRepositoryInterface,
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
		\Collector\Base\Model\Config $_config
    ) {
        $this->checkerFactory = $checkerFactory;
		$this->config = $_config;
        $this->fraudFactory = $fraudFactory;
        $this->collectorLogger = $logger;
        $this->apiRequest = $apiRequest;
        $this->request = $request;
        $this->collectorSession = $_collectorSession;
        $this->order = $order;
        $this->orderSender = $orderSender;
        $this->resultPageFactory = $resultPageFactory;
        $this->helper = $_helper;
        $this->filterBuilder = $_filterBuilder;
        $this->eventManager = $eventManager;
        $this->checkoutSession = $_checkoutSession;
        $this->orderInterface = $_orderInterface;
        $this->quoteCollectionFactory = $_quoteCollectionFactory;
        $this->storeManager = $_storeManager;
        $this->customerRepository = $_customerRepository;
        $this->customerFactory = $_customerFactory;
        $this->shippingRate = $_shippingRate;
        $this->cartRepositoryInterface = $_cartRepositoryInterface;
        $this->cartManagementInterface = $_cartManagementInterface;
        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        if (!empty($this->request->getParam('OrderNo'))) {
            if (!empty($this->request->getParam('InvoiceStatus'))) {
                $order = $this->orderInterface->loadByIncrementId($this->request->getParam('OrderNo'));
                if ($order->getId()) {
                    if ($this->request->getParam('InvoiceStatus') == "0") {
                        $status = $this->config->getHoldStatus();
                        $order->setState($status)->setStatus($status);
                        $order->save();
                    } else {
                        if ($this->request->getParam('InvoiceStatus') == "1") {
                            $status = $this->config->getAcceptStatus();
                            $order->setState($status)->setStatus($status);
                            $order->save();
                        } else {
                            $status = $this->config->getDeniedStatus();
                            $order->setState($status)->setStatus($status);
                            $order->save();
                        }
                    }
                }
                $fraud = $this->fraudFactory->create();
                $fraud->setIncrementId($this->request->getParam('OrderNo'));
                $fraud->setStatus($this->request->getParam('InvoiceStatus'));
                $fraud->setIsAntiFraud(1);
                $fraud->save();

            }
            $checker = $this->checkerFactory->create();
            $checker->setData('increment_id', $this->request->getParam('OrderNo'));
            $checker->save();
        }
        return $this->resultPageFactory->create();
    }
}
