<?php

namespace Wheelpros\CustomerExtended\Block;

use Magento\Checkout\Helper\Data as CheckoutData;
use Magento\Customer\Block\Form\Login\Info;
use Magento\Customer\Model\Registration;
use Magento\Customer\Model\Url;
use Magento\Framework\Url\Helper\Data;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Wheelpros\Core\Model\Config;

class CustomerCreate extends Info
{

    protected const XML_PATH_FOR_CREDIT_URL = 'wheelpros_customer/customer_login_form/credit_app_url';

    protected const XML_PATH_FOR_NEW_CUSTOMER_CONTENT = 'wheelpros_customer/customer_login_form/new_customer_content';

    private Config $config;

    /**
     * @param Context $context
     * @param Registration $registration
     * @param Url $customerUrl
     * @param CheckoutData $checkoutData
     * @param Data $coreUrl
     * @param Config $config
     * @param array $data
     */
    public function __construct(
        Context      $context,
        Registration $registration,
        Url          $customerUrl,
        CheckoutData $checkoutData,
        Data         $coreUrl,
        Config        $config,
        array        $data = []
    ) {
        parent::__construct($context, $registration, $customerUrl, $checkoutData, $coreUrl, $data);
        $this->config = $config;
    }

    public function getCreditAppUrl()
    {
        return $this->config->getConfig(self::XML_PATH_FOR_CREDIT_URL);
    }

    public function getNewCustomerContent()
    {
        return $this->config->getConfig(self::XML_PATH_FOR_NEW_CUSTOMER_CONTENT);
    }
}
