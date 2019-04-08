<?php

namespace Collector\Iframe\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;
    /**
     * @var \Magento\Quote\Model\Quote\Address\Rate
     */
    protected $shippingRate;
    /**
     * @var \Magento\SalesRule\Model\Coupon
     */
    protected $coupon;
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;
    /**
     * @var \Magento\Catalog\Helper\Product\Configuration
     */
    protected $productConfigHelper;
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;
    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $imageHelper;
    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $pricingHelper;
    /**
     * @var \Collector\Base\Model\Session
     */
    protected $collectorSession;
    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $cart;
    /**
     * @var \Collector\Base\Logger\Collector
     */
    protected $logger;
    /**
     * @var \Magento\Tax\Model\Calculation
     */
    protected $taxCalculation;

    /**
     * @var \Collector\Base\Model\Config
     */
    protected $collectorConfig;

    /**
     * @var \Collector\Base\Model\ApiRequest
     */
    protected $apiRequest;

    /**
     * @var \Magento\Checkout\Helper\Data
     */
    protected $checkoutHelper;

    /**
     * @var \Collector\Base\Helper\Prices
     */
    protected $collectorPriceHelper;

    protected $shippingCollected = false;
    public $allowedCountries = [
        'NO',
        'SE',
        'FI',
        'DE'
    ];
    /**
     * @var 
     */
    protected $quoteRepository;
    
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
    
    /**
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;
    
    /**
     * @var \Magento\SalesRule\Model\CouponFactory
     */
    protected $couponFactory;
    
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;
    
    /**
     * @var \Magento\GroupedProduct\Model\Product\Type\Grouped
     */
    protected $groupedProductClass;

    /**
     * Data constructor.
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Store\Model\StoreManagerInterface $_storeManager
     * @param \Magento\Tax\Model\Calculation $taxCalculation
     * @param \Magento\Framework\Pricing\Helper\Data $_pricingHelper
     * @param \Magento\Checkout\Model\Cart $cart
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Magento\Checkout\Model\Session $_checkoutSession
     * @param \Magento\Quote\Model\Quote\Address\Rate $_shippingRate
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\SalesRule\Model\Coupon $_coupon
     * @param \Magento\Catalog\Helper\Product\Configuration $_productConfigHelper
     * @param \Collector\Base\Model\Session $_collectorSession
     * @param \Collector\Base\Logger\Collector $logger
     * @param \Collector\Base\Model\ApiRequest $apiRequest
     * @param \Magento\Checkout\Helper\Data $checkoutHelper
     * @param \Magento\Framework\Message\ManagerInterface $_messageManager
     * @param \Collector\Base\Model\Config $collectorConfig
     * @param \Magento\Quote\Api\CartRepositoryInterface $_quoteRepository
     * @param \Magento\Framework\Escaper $_escaper
     * @param \Magento\SalesRule\Model\CouponFactory $_couponFactory
     * @param \Magento\Catalog\Model\ProductFactory $_productFactory
     * @param \Magento\GroupedProduct\Model\Product\Type\Grouped $_groupedProductClass
     * @param \Collector\Base\Helper\Prices $collectorPriceHelper
     */
    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Store\Model\StoreManagerInterface $_storeManager,
        \Magento\Tax\Model\Calculation $taxCalculation,
        \Magento\Framework\Pricing\Helper\Data $_pricingHelper,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Checkout\Model\Session $_checkoutSession,
        \Magento\Quote\Model\Quote\Address\Rate $_shippingRate,
        \Magento\Framework\App\Helper\Context $context,
        \Magento\SalesRule\Model\Coupon $_coupon,
        \Magento\Catalog\Helper\Product\Configuration $_productConfigHelper,
        \Collector\Base\Model\Session $_collectorSession,
        \Collector\Base\Logger\Collector $logger,
        \Collector\Base\Model\ApiRequest $apiRequest,
        \Magento\Checkout\Helper\Data $checkoutHelper,
        \Magento\Framework\Message\ManagerInterface $_messageManager,
        \Collector\Base\Model\Config $collectorConfig,
        \Magento\Quote\Api\CartRepositoryInterface $_quoteRepository,
        \Magento\Framework\Escaper $_escaper,
        \Magento\SalesRule\Model\CouponFactory $_couponFactory,
        \Magento\Catalog\Model\ProductFactory $_productFactory,
        \Magento\GroupedProduct\Model\Product\Type\Grouped $_groupedProductClass,
        \Collector\Base\Helper\Prices $collectorPriceHelper
    ) {
        //ugly hack to remove compilation errors in Magento 2.1.x
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->scopeConfig = $objectManager->get('\Magento\Framework\App\Config\ScopeConfigInterface');
        //end of hack
        
        $this->groupedProductClass = $_groupedProductClass;
        $this->productFactory = $_productFactory;
        $this->collectorPriceHelper = $collectorPriceHelper;
        $this->checkoutHelper = $checkoutHelper;
        $this->apiRequest = $apiRequest;
        $this->collectorConfig = $collectorConfig;
        $this->logger = $logger;
        $this->collectorSession = $_collectorSession;
        $this->productRepository = $productRepository;
        $this->imageHelper = $imageHelper;
        $this->pricingHelper = $_pricingHelper;
        $this->cart = $cart;
        $this->taxCalculation = $taxCalculation;
        $this->shippingRate = $_shippingRate;
        $this->checkoutSession = $_checkoutSession;
        $this->productConfigHelper = $_productConfigHelper;
        $this->messageManager = $_messageManager;
        $this->storeManager = $_storeManager;
        $this->coupon = $_coupon;
        $this->couponFactory = $_couponFactory;
        $this->escaper = $_escaper;
        $this->quoteRepository = $_quoteRepository;
        return parent::__construct($context);
    }


    public function getSuccessPageUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl() . "collectorcheckout/success?OrderNo=" .
            $this->cart->getQuote()->getReservedOrderId();
    }

    public function getNotificationUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl() .
            "collectorcheckout/notification?OrderNo=" .
            $this->cart->getQuote()->getReservedOrderId();
    }
    
    public function getValidationUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl() .
            "collectorcheckout/validation?OrderNo=" .
            $this->cart->getQuote()->getReservedOrderId();
    }

    public function getDiscount()
    {
        return $this->collectorPriceHelper->getQuoteDiscount($this->cart->getQuote(), true);
    }

    public function hasDiscount()
    {
        return $this->collectorPriceHelper->hasQuoteDiscount($this->cart->getQuote());
    }

    public function getTax()
    {
        return $this->collectorPriceHelper->getQuoteTaxValue($this->cart->getQuote(), true);
    }

    public function getGrandTotal()
    {
        return $this->collectorPriceHelper->getQuoteGrandTotal($this->cart->getQuote(), true);
    }

    public function getShippingMethods()
    {
        $this->cart->getQuote()->collectTotals();
        $currentStoreId = $this->storeManager->getStore()->getId();
        $request = $this->taxCalculation->getRateRequest(null, null, null, $currentStoreId);
        $shippingAddress = $this->cart->getQuote()->getShippingAddress();
        if (!$this->shippingCollected) {
            $this->shippingCollected = true;
            $shippingAddress->setCollectShippingRates(true)->collectShippingRates();
        }
        $shippingTaxClass = $this->collectorConfig->getShippingTaxClass();
        $shippingTax = $this->taxCalculation->getRate($request->setProductClassId($shippingTaxClass));
        $shippingMethods = [];
        $first = true;

        $methods = $shippingAddress->getGroupedAllShippingRates();

        $selectedIsActive = false;
        if (!empty($shippingAddress->getShippingMethod())) {
            foreach ($methods as $method) {
                foreach ($method as $rate) {
                    if ($rate->getCode() == $shippingAddress->getShippingMethod()) {
                        $selectedIsActive = true;
                    }
                }
            }
        }

        foreach ($methods as $method) {
            foreach ($method as $rate) {
                $price = $rate->getPrice();
                if ($this->scopeConfig->getValue('tax/calculation/shipping_includes_tax') == 0 && $this->scopeConfig->getValue('tax/cart_display/shipping') == 2){
                    $price += $price*($shippingTax/100);
                }
                else if ($this->scopeConfig->getValue('tax/calculation/shipping_includes_tax') == 1 && $this->scopeConfig->getValue('tax/cart_display/shipping') == 1){
                    $price = $price/($shippingTax/100+1);
                }
                $shipMethod = [
                    'first' => !$selectedIsActive && $first
                        || $selectedIsActive && $rate->getCode() == $shippingAddress->getShippingMethod(),
                    'code' => $rate->getCode(),
                    'content' => ''
                ];
                if (!$selectedIsActive && $first
                    || $selectedIsActive && $rate->getCode() == $shippingAddress->getShippingMethod()
                ) {
                    $first = false;
                    $this->setShippingMethod($rate->getCode());
                }
                $shipMethod['content'] = $rate->getMethodTitle() . ": "
                    . $this->pricingHelper->currency(
                        $price,
                        true,
                        false
                    );

                array_push($shippingMethods, $shipMethod);
            }
        }
        return $shippingMethods;
    }

    public function setDiscountCode($code)
    {
        $couponCode = $code;

        $cartQuote = $this->cart->getQuote();
        $codeLength = strlen($couponCode);
        try {
            $isCodeLengthValid = $codeLength && $codeLength <= \Magento\Checkout\Helper\Cart::COUPON_CODE_MAX_LENGTH;

            $itemsCount = $cartQuote->getItemsCount();
            if ($itemsCount) {
                $cartQuote->getShippingAddress()->setCollectShippingRates(true);
                $cartQuote->setCouponCode($isCodeLengthValid ? $couponCode : '')->collectTotals();
                $this->quoteRepository->save($cartQuote);
            }

            if ($codeLength) {
                $escaper = $this->escaper;
                $coupon = $this->couponFactory->create();
                $coupon->load($couponCode, 'code');
                if (!$itemsCount) {
                    if ($isCodeLengthValid && $coupon->getId()) {
                        $this->checkoutSession->getQuote()->setCouponCode($code)->collectTotals()->save();
                        $this->collectorSession->setCollectorAppliedDiscountCode($code);
                        $this->cart->getQuote()->setData('collector_applied_discount_code', $code);
                        $this->cart->getQuote()->save();
                        return array('message'=>__('You used coupon code "%1".', $escaper->escapeHtml($couponCode)), 'error'=>false);
                    } else {
                        return array('message'=>__('The coupon code "%1" is not valid.', $escaper->escapeHtml($couponCode)), 'error'=>false);
                    }
                } else {
                    if ($isCodeLengthValid && $coupon->getId() && $couponCode == $cartQuote->getCouponCode()) {
                        return array('message'=>__('You used coupon code "%1".', $escaper->escapeHtml($couponCode)), 'error'=>false);
                    } else {
                        return array('message'=>__('The coupon code "%1" is not valid.', $escaper->escapeHtml($couponCode)), 'error'=>false);
                    }
                }
            } else {
                $this->messageManager->addSuccess(__('You canceled the coupon code.'));
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            return array('message'=>$e->getMessage(), 'error'=>true);
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            return array('message'=>$e->getMessage(), 'error'=>true);
            return array('message'=>__('We cannot apply the coupon code.'), 'error'=>true);
        }
    }
	
	public function setMessage($message, $error){
		if ($error){
			$this->messageManager->addError($message);
		}
		else {
			$this->messageManager->addSuccess($message);
		}
	}

    public function unsetDiscountCode()
    {
        $this->collectorSession->setCollectorAppliedDiscountCode('');
        $this->cart->getQuote()->setData('collector_applied_discount_code', null);
        $this->cart->getQuote()->save();
        $this->checkoutSession->getQuote()->setCouponCode()->collectTotals()->save();
        return array('message'=>__('You canceled the coupon code.'), 'error'=>false);
    }

    public function getShippingMethod()
    {
        return $this->cart->getQuote()->getShippingAddress()
            ->getShippingMethod();
    }

    public function setShippingMethod($methodInput = '')
    {
        $shippingAddress = $this->cart->getQuote()->getShippingAddress();
        $methods = $shippingAddress->getGroupedAllShippingRates();
        foreach ($methods as $method) {
            foreach ($method as $rate) {
                if ($rate->getCode() == $methodInput || empty($methodInput)) {
                    $this->cart->getQuote()->getShippingAddress()->setShippingMethod($rate->getCode());
                    $this->shippingRate->setCode($rate->getCode());
                    try {
                        $this->cart->getQuote()->getShippingAddress()->addShippingRate($this->shippingRate);
                    } catch (\Exception $e) {
                    }
                    $this->cart->getQuote()->setTotalsCollectedFlag(false);
                    $this->cart->getQuote()->collectTotals();
                    $this->cart->getQuote()->save();
                    break;
                }
            }
        }

        return $this->collectorPriceHelper->getQuoteShippingPrice($this->cart->getQuote(), true);
    }

    public function getShippingPrice($inclFormatting = true)
    {
        if (empty($this->cart->getQuote()->getShippingAddress()->getShippingMethod())) {
            $this->getShippingMethod();
        }
        return $this->collectorPriceHelper->getQuoteShippingPrice($this->cart->getQuote(), $inclFormatting);
    }

    public function getBlockProducts()
    {
        $request = $this->taxCalculation->getRateRequest(null, null, null, $this->storeManager->getStore()->getId());
        $items = [];

        foreach ($this->cart->getQuote()->getAllVisibleItems() as $cartItem) {
            $product = $cartItem->getProduct();
            $taxClassId = $product->getTaxClassId();
            //$percent = $this->taxCalculation->getRate($request->setProductClassId($taxClassId));
            $options = [];
            $op = $cartItem->getProduct()->getTypeInstance(true)->getOrderOptions($cartItem->getProduct());
            if ($cartItem->getProductType() == 'configurable') {
                foreach ($op['attributes_info'] as $option) {
                    $options[] = $option['label'] . ": " . $option['value'];
                }
                if ($this->collectorConfig->getUseConfigurableParentImage() == \Magento\Catalog\Model\Config\Source\Product\Thumbnail::OPTION_USE_PARENT_IMAGE){
                    $image = $this->imageHelper->init(
                        $product,
                        'product_page_image_small'
                    )->setImageFile($product->getFile())->resize(80, 80)->getUrl();
                }
                else {
                    $image = $this->imageHelper->init(
                        $cartItem->getChildren()[0]->getProduct(),
                        'product_page_image_small'
                    )->setImageFile($cartItem->getChildren()[0]->getProduct()->getFile())->resize(80, 80)->getUrl();
                }
            } else {
                if ($cartItem->getProductType() == 'bundle') {
                    foreach ($op['bundle_options'] as $option) {
                        $options[] = $option['value'][0]['title'];
                    }
                    $image = $this->imageHelper->init(
                        $product,
                        'product_page_image_small'
                    )->setImageFile($product->getFile())->resize(80, 80)->getUrl();
                }
                else if ($cartItem->getProductType() == 'grouped'){
                    if ($this->collectorConfig->getUseBundleParentImage() == \Magento\Catalog\Model\Config\Source\Product\Thumbnail::OPTION_USE_PARENT_IMAGE){
                        $groupedProductId = $this->groupedProductClass->getParentIdsByChild($product->getId())[0];
                        $groupedProduct = $this->productFactory->create()->load($groupedProductId);
                        $image = $this->imageHelper->init(
                            $groupedProduct,
                            'product_page_image_small'
                        )->setImageFile($groupedProduct->getFile())->resize(80, 80)->getUrl();
                    }
                    else {
                        $image = $this->imageHelper->init(
                            $product,
                            'product_page_image_small'
                        )->setImageFile($product->getFile())->resize(80, 80)->getUrl();
                    }
                }
                else {
                    $image = $this->imageHelper->init(
                        $product,
                        'product_page_image_small'
                    )->setImageFile($product->getFile())->resize(80, 80)->getUrl();
                }
            }
            
            $item = array(
                'name' => $cartItem->getName(),
                'options' => $options,
                'id' => $cartItem->getId(),

                'unitPrice' =>
                    $this->checkoutHelper->formatPrice(
                        $this->scopeConfig->getValue('tax/cart_display/price') == 1 ?
                            $cartItem->getPrice() :
                            $cartItem->getPriceInclTax()
                    ),
                'qty' => $cartItem->getQty(),
                'sum' => $this->checkoutHelper->formatPrice(
                    $this->scopeConfig->getValue('tax/cart_display/price') == 1 ?
                        $cartItem->getRowTotal() :
                        $cartItem->getRowTotalInclTax()
                ),
                'img' => $image
            );
            if ($this->scopeConfig->getValue('tax/cart_display/price') == 3){
                $item['sum'] .= "<br><span style=\"font-size: 10px;\">" .
                $this->checkoutHelper->formatPrice($cartItem->getRowTotal(), true, false) .
                "&nbsp" .
                __('Excl. Tax') .
                "</span>";
            }
            array_push($items, $item);
        }
        return $items;
    }

    public function getProducts()
    {
        $quoteTotals = $this->checkoutSession->getQuote()->getShippingAddress()->getData();
        $request = $this->taxCalculation->getRateRequest(null, null, null, $this->storeManager->getStore()->getId());
        $cartTotals = $this->collectorPriceHelper->getQuoteTotalsArray($this->cart->getQuote(), false);
        $items = [];
        $bundlesWithFixedPrice = [];
        $sum = 0;
        foreach ($this->cart->getQuote()->getAllItems() as $cartItem) {
            if ($cartItem->getProductType() == 'configurable') {
                continue;
            } elseif (in_array($cartItem->getParentItemId(), $bundlesWithFixedPrice)) {
                continue;
            } elseif ($cartItem->getProductType() == 'bundle') {
                $product = $cartItem->getProduct();
                if ($product->getPriceType() == \Magento\Bundle\Model\Product\Price::PRICE_TYPE_FIXED) {
                    $bundlesWithFixedPrice[] = $cartItem->getItemId();
                } elseif ($product->getPriceType() == \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC) {
                    continue;
                }
            }
            $product = $this->productRepository->get($cartItem->getSku());
            $taxClassId = $product->getTaxClassId();
            $percent = $this->taxCalculation->getRate($request->setProductClassId($taxClassId));
            if ($cartItem->getParentItem() && $cartItem->getQty() == 0) {
                $qty = $cartItem->getParentItem()->getQty();
            } else if ($cartItem->getParentItem() !== null) {
                if ($cartItem->getParentItem()->getProductType() == 'bundle') {
                    $qty = $cartItem->getQty() * $cartItem->getParentItem()->getQty();
                } else if ($cartItem->getParentItem()->getProductType() == 'configurable'){
                    $qty = $cartItem->getParentItem()->getQty();
                } else {
                    $qty = $cartItem->getQty();
                }
            } else {
                $qty = $cartItem->getQty();
            }
            $price = $cartItem->getPriceInclTax();
            if ($cartItem->getPriceInclTax() == 0) {
                $price = $cartItem->getParentItem()->getPriceInclTax();
            }
            $sum += ($price * $qty);
            array_push($items, array(
                'id' => $cartItem->getSku(),
                'description' => $cartItem->getName(),
                'unitPrice' => round($price,  2),
                'quantity' => $qty,
                'vat' => $percent
            ));
        }
        $totals =
            (!empty($quoteTotals['subtotal']) ? $quoteTotals['subtotal'] : 0)
            + (!empty($cartTotals['fee']['value']) ? $cartTotals['fee']['value'] : 0)
            + (!empty($quoteTotals['shipping_amount']) ? $quoteTotals['shipping_amount'] - $quoteTotals['shipping_tax_amount'] : 0)
            + (!empty($cartTotals['tax']['value']) ? $cartTotals['tax']['value'] : 0);
        if ($this->cart->getQuote()->getGrandTotal() < $totals) {
            $coupon = "no_code";
            if ($this->cart->getQuote()->getCouponCode() != null) {
                $coupon = $this->cart->getQuote()->getCouponCode();
            }
            $code = array(
                'id' => 'discount',
                'description' => $coupon,
                'quantity' => 1,
                'unitPrice' => sprintf(
                    "%01.2f",
                    $this->cart->getQuote()->getGrandTotal() - $totals
                ),
                'vat' => '0',
            );
            array_push($items, $code);
        }
        if ($this->cart->getQuote()->getGrandTotal() < ($sum + ($this->cart->getQuote()->getGrandTotal() - $totals))){
            $rounding = array(
                'id' => 'rounding',
                'description' => 'rounding',
                'quantity' => 1,
                'unitPrice' => sprintf(
                    "%01.2f",
                    $this->cart->getQuote()->getGrandTotal() - ($sum + ($this->cart->getQuote()->getGrandTotal() - $totals))
                ),
                'vat' => '0',
            );
            array_push($items, $rounding);
        }
        return $items;
    }

    public function getFees()
    {
        $request = $this->taxCalculation->getRateRequest(null, null, null, $this->storeManager->getStore()->getId());
        $btype = $this->collectorSession->getBtype('');
        if ($btype == \Collector\Base\Model\Session::B2B ||
            empty($btype) && $this->getCustomerType() ==
            \Collector\Iframe\Model\Config\Source\Customertype::BUSINESS_CUSTOMER
        ) {
            $fee = $this->collectorConfig->getInvoiceB2BFee();
            $feeTaxClass = $this->collectorConfig->getB2BInvoiceFeeTaxClass();
            $feeTax = $this->taxCalculation->getRate($request->setProductClassId($feeTaxClass));
        }
        else {
            $fee = $this->collectorConfig->getInvoiceB2CFee();
            $feeTaxClass = $this->collectorConfig->getB2CInvoiceFeeTaxClass();
            $feeTax = $this->taxCalculation->getRate($request->setProductClassId($feeTaxClass));
        }
        $this->cart->getQuote()->collectTotals();
        $shippingAddress = $this->cart->getQuote()->getShippingAddress();
        $shippingTaxClass = $this->scopeConfig->getValue('tax/classes/shipping_tax_class');
        $request->setProductTaxClassId($shippingTaxClass);
        $request->setProductClassId($shippingTaxClass);
        $shippingTax = $this->taxCalculation->getRate($request);
        $ret = [];
        if ($fee > 0) {
            $ret['directinvoicenotification'] = [
                'id' => 'invoice_fee',
                'description' => 'Invoice Fee',
                'unitPrice' => $fee,
                'vat' => $feeTax
            ];
        }
        if (!empty($shippingAddress->getShippingMethod())) {
            $ret ['shipping'] = [
                'id' => "shipping",
                'description' => $shippingAddress->getShippingMethod(),
                'unitPrice' => $shippingAddress->getShippingInclTax(),
                'vat' => $shippingTax
            ];
        } else {
            $ret['shipping'] = [
                'id' => 'shipping',
                'description' => 'freeshipping_freeshipping',
                'unitPrice' => 0,
                'vat' => $shippingTax
            ];
        }
        return $ret;
    }

    public function updateFees()
    {
        $result = $this->apiRequest->callCheckoutsFees($this->getFees(), $this->cart);
		if ($result->error !== NULL){
			return array('error'=>true,'message'=>$result->error->errors[0]->message);
		}
		return true;
    }

    public function updateCart()
    {
        $result = $this->apiRequest->callCheckoutsCart([
            'countryCode' => $this->collectorConfig->getCountryCode(),
            'items' => $this->getProducts()
        ], $this->cart);
		if ($result->error !== NULL){
			return array('error'=>true,'message'=>$result->error->errors[0]->message);
		}
		return true;
    }

    public function getOrderResponse()
    {
        $result = [];
        $data = $this->apiRequest->callCheckouts($this->cart);
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
}
