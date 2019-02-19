<?php

namespace Collector\Gateways\Model\Payment;

/**
 * Invoice payment method model
 */


class InvoiceInvoice extends \Collector\Gateways\Model\Payment\Invoice
{
    protected $_code = 'collector_invoiceinvoice';
    protected $_isGateway = true;
    protected $_canCapture = true;
    protected $_canCapturePartial = true;
    protected $_canVoid = false;
    protected $_canRefund = true;
    protected $_canRefundInvoicePartial = true;
    protected $_isOffline = false;
    protected $_canAuthorize = false;

    public function getTitle()
    {
        return "Collector Invoice";
    }
}
