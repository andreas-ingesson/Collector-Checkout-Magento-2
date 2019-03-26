<?php

namespace Collector\Iframe\Model\Config\Source;

class DefaultCustomerType implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Return array of options as value-label pairs, eg. value => label
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            \Collector\Iframe\Model\Config\Source\Customertype::PRIVATE_CUSTOMER => __('Private Customers'),
            \Collector\Iframe\Model\Config\Source\Customertype::BUSINESS_CUSTOMER => __('Business Customers')
        ];
    }
}
