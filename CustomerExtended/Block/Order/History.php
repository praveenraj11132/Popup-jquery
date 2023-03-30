<?php

namespace Wheelpros\CustomerExtended\Block\Order;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Wheelpros\CustomerExtended\Model\SalesforceApi;

class History extends Template
{
    /**
     * @var SalesforceApi
     */
    private SalesforceApi $salesforceApi;

    /**
     * @param Context       $context
     * @param SalesforceApi $salesforceApi
     * @param array         $data
     */
    public function __construct(
        Template\Context $context,
        SalesforceApi    $salesforceApi,
        array            $data = []
    ) {
        parent::__construct($context, $data);
        $this->salesforceApi = $salesforceApi;
    }

    /**
     * Returns customer orders
     *
     * @return array
     */
    public function getCustomerOrders(): array
    {
        $ordersData = $this->salesforceApi->fetchOrders();

        if ($ordersData['done'] && $ordersData['totalSize'] > 0) {
            return $ordersData['records'];
        }

        return [];
    }

    /**
     * Get reorder URL
     *
     * @param  string $salesforceOrderId
     * @return string
     */
    public function getReorderUrl($salesforceOrderId): string
    {
        return $this->getUrl('request/order/reorder', ['order_id' => $salesforceOrderId]);
    }
    public function getRedirectOrder(){
        return $this->getUrl('request/redirectorder/orderdetails');
    }
}
