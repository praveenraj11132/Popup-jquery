<?php

namespace Wheelpros\CustomerExtended\Block\RedirectOrder;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Wheelpros\CustomerExtended\Model\SalesforceApi;
use Magento\Checkout\Model\Session as CheckoutSession;

class OrderDetails extends Template
{
    /**
     * @var SalesforceApi
     */
    private SalesforceApi $salesforceApi;

    /**
     * @var CheckoutSession
     */
    private CheckoutSession $checkoutSession;


    /**
     * @param Context       $context
     * @param SalesforceApi $salesforceApi
     * @param array         $data
     */
    public function __construct(
        Template\Context $context,
        SalesforceApi    $salesforceApi,
        CheckoutSession $checkoutSession,
        array            $data = []
    ) {
        parent::__construct($context, $data);
        $this->salesforceApi = $salesforceApi;
        $this->checkoutSession = $checkoutSession;
    }

    public function getOrderDetails()
    {
        $ordersData = $this->salesforceApi->fetchOrders();
        if ($ordersData['done'] && $ordersData['totalSize'] > 0) {
            return $ordersData['records'];
        }
        return [];
    }
}
