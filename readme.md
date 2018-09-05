# Collector Checkout by Ecomatic

## General Information

The Invoice Fee will not be shown in the cart section on checkout page

Known to work in versions: 2.2.0 and 2.1.8

#### Google Tag Manager
We recommend using WeltPixels plugin called Magento 2 Google Analytics Enhanced Ecommerce UA GTM Tracking.
Their GTM plugin can be found here: https://www.weltpixel.com/google-analytics-enhanced-ecommerce-tag-manager-magento-2.html
We have built a compatability plugin for WeltPixels plugin mentioned above.
The compatability plugin can be found here: https://github.com/Ecomatic/Collector-Checkout-Weltpixel-GTM-compatibility

## Firewall
If you are using a firewall some urls need to be opened to be able to use this plugin, those are:
* ecommercetest.collector.se
* ecommerce.collector.se
* checkout-api-uat.collector.se
* checkout-api.collector.se
## Settings

#### Required settings:
All of these are in Stores -> Configuration
* Collector -> Checkout -> General -> Username
* Collector -> Checkout -> General -> iframe Password
* Collector -> Checkout -> General -> Store ID
* Collector -> Checkout -> General -> Terms and conditions URL, full url incluing protocol (e.g https://example.com/terms)
* Sales -> Tax -> Shopping Carty Display Settings -> Display Subtotal -> Including Tax or Including and Excluding Tax
* General -> General -> Store Information -> Country -> Sweden or Norway

#### Recommended Settings:
* Sales -> Tax -> Shopping Carty Display Settings -> Display Prices -> Including Tax
* Sales -> Tax -> Shopping Carty Display Settings -> Display Shipping Amount -> Including Tax
* Sales -> Tax -> Default Tax Destination Calculation -> Default Country -> Same country as store information in Requirered Settings
* Sales -> Shipping Settings -> Origin -> Country -> Same country as store information in Requirered Settings


## Known Issues
