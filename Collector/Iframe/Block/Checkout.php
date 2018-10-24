<?php

namespace Collector\Iframe\Block;

class Checkout extends \Magento\Checkout\Block\Onepage
{
    /**
     * @var \Collector\Iframe\Helper\Data
     */
    protected $helper;
    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $cart;

    protected $languageArray = [
        "NO" => "nb-NO",
        "SE" => "sv",
        "FI" => "fi-FI",
        "DK" => "en-DK",
        "DE" => "en-DE"
    ];
    /**
     * @var \Collector\Base\Model\Session
     */
    protected $collectorSession;


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
     * Checkout constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Data\Form\FormKey $formKey
     * @param \Magento\Checkout\Model\CompositeConfigProvider $configProvider
     * @param \Collector\Iframe\Helper\Data $_helper
     * @param \Magento\Checkout\Model\Cart $_cart
     * @param \Collector\Base\Model\Config $collectorConfig
     * @param \Collector\Base\Model\Session $_collectorSession
     * @param \Collector\Base\Model\ApiRequest $apiRequest
     * @param \Magento\Customer\Model\Session $customerSession
     * @param array $layoutProcessors
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Magento\Checkout\Model\CompositeConfigProvider $configProvider,
        \Collector\Iframe\Helper\Data $_helper,
        \Magento\Checkout\Model\Cart $_cart,
        \Collector\Base\Model\Config $collectorConfig,
        \Collector\Base\Model\Session $_collectorSession,
        \Collector\Base\Model\ApiRequest $apiRequest,
        \Magento\Customer\Model\Session $customerSession,
        array $layoutProcessors = [],
        array $data = []
    ) {
        parent::__construct($context, $formKey, $configProvider, $layoutProcessors, $data);
        $this->apiRequest = $apiRequest;
        $this->collectorConfig = $collectorConfig;
        $this->customerSession = $customerSession;
        $this->collectorSession = $_collectorSession;
        $this->helper = $_helper;
        $this->cart = $_cart;
    }

    protected function _toHtml()
    {
        return parent::_toHtml();
    }

    public function getCheckoutUrl()
    {
        if ($this->collectorConfig->getTestMode()) {
            $this->collectorSession->setCollectorUrl("https://checkout-uat.collector.se/collector-checkout-loader.js");
        } else {
            $this->collectorSession->setCollectorUrl("https://checkout.collector.se/collector-checkout-loader.js");
        }
        return $this->collectorSession->getCollectorUrl('');
    }

    public function getLanguage()
    {
        $lang = $this->collectorConfig->getCountryCode();
        if (!empty($this->languageArray[$lang])) {
            $this->collectorSession->setCollectorLanguage($this->languageArray[$lang]);
            return $this->languageArray[$lang];
        }
        return null;
    }

    public function getDataVariant()
    {
        $dataVariant = ' async';
        if ($this->collectorSession->getBtype('') == \Collector\Base\Model\Session::B2B
            || empty($this->collectorSession->getBtype(''))
            && ($this->collectorConfig->getCustomerType()
                == \Collector\Iframe\Model\Config\Source\Customertype::BUSINESS_CUSTOMER)) {
            $dataVariant = ' data-variant="b2b" async';
        }
        $this->collectorSession->setCollectorDataVariant($dataVariant);
        $this->cart->getQuote()->setData('collector_btype', $dataVariant);
        $this->cart->getQuote()->save();
        return $dataVariant;
    }

    public function getPublicToken()
    {
        $this->customerSession->setCollectorIncrementId($this->cart->getQuote()->getReservedOrderId());
        if (!empty($this->cart->getQuote()->getData('collector_private_id'))) {
            $this->helper->updateCart();
            $this->helper->updateFees();
            return $this->collectorSession->getCollectorPublicToken();
        }
        if (empty($this->cart->getQuote()->getReservedOrderId())) {
            $this->cart->getQuote()->reserveOrderId()->save();
        }
        $result = $this->apiRequest->getTokenRequest([
            'storeId' => $this->collectorConfig->getB2BrB2CStore(),
            'countryCode' => $this->collectorConfig->getCountryCode(),
            'reference' => $this->cart->getQuote()->getReservedOrderId(),
            'redirectPageUri' => $this->helper->getSuccessPageUrl(),
            'merchantTermsUri' => $this->collectorConfig->getTermsUrl(),
            'notificationUri' => $this->helper->getNotificationUrl(),
            'validationUri' => $this->helper->getValidationUrl(),
            "cart" => ['items' => $this->helper->getProducts()],
            "fees" => $this->helper->getFees()
        ]);
		if ($result['error'] !== NULL){
			return array('error'=>true,'message'=>$result['error']['errors'][0]['message']);
		}
        $this->collectorSession->setCollectorPublicToken($result["data"]["publicToken"]);
        $this->collectorSession->setCollectorPrivateId($result['data']['privateId']);
        $this->cart->getQuote()->setData('collector_private_id', $result['data']['privateId']);
        $this->cart->getQuote()->setData('collector_public_token', $result["data"]["publicToken"]);
        $this->cart->getQuote()->setData('collector_btype', $this->collectorSession->getBtype());
        $this->cart->getQuote()->save();
        return $publicToken = $result["data"]["publicToken"];
    }
}
