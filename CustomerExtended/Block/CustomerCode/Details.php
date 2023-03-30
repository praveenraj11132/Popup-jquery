<?php

namespace Wheelpros\CustomerExtended\Block\CustomerCode;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class Details extends Template
{

    /**
     * @param Context       $context
     * @param array         $data
     */
    public function __construct(
        Template\Context $context,
        array            $data = []
    ) {
        parent::__construct($context, $data);
    }

    public function getCustomerCode(){
        return "";
    }

}
