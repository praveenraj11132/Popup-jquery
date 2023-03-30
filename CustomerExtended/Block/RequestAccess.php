<?php

namespace Wheelpros\CustomerExtended\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Wheelpros\Core\Model\Config;

class RequestAccess extends Template
{
    protected const XML_PATH_FOR_EXISTING_CUSTOMER_CONTENT = 'wheelpros_customer/customer_login_form/existing_customer_content';

    private Config $config;

    /**
     * @param Context $context
     * @param Config $config
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Config            $config,
        array            $data = []
    ) {
        $this->config = $config;
        parent::__construct($context, $data);
    }

    public function getExistingCustomerContent()
    {
        return $this->config->getConfig(self::XML_PATH_FOR_EXISTING_CUSTOMER_CONTENT);
    }
}
