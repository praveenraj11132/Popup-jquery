<?php

namespace Wheelpros\CustomerExtended\ViewModel;

use Magento\Customer\Model\Address\Mapper;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class Address implements ArgumentInterface
{
    /**
     * @var \Magento\Customer\Model\Address\Config
     */
    protected $_addressConfig;

    /**
     * @var Mapper
     */
    protected $addressMapper;

    /**
     * @param \Magento\Customer\Model\Address\Config $addressConfig
     * @param Mapper $addressMapper
     */
    public function __construct(
        \Magento\Customer\Model\Address\Config $addressConfig,
        Mapper $addressMapper,
    ) {
        $this->_addressConfig = $addressConfig;
        $this->addressMapper = $addressMapper;
    }

    /**
     * HTML for Address
     * @param AddressInterface $address
     * @return \Magento\Framework\Phrase|string
     */
    public function getFormattedAddressHtml($address)
    {
        if ($address) {
            return $this->_getAddressHtml($address);
        } else {
            return $this->escapeHtml(__('You have not set a default billing address.'));
        }
    }

    /**
     * Render an address as HTML and return the result
     *
     * @param AddressInterface $address
     * @return string
     */
    protected function _getAddressHtml($address)
    {
        /** @var \Magento\Customer\Block\Address\Renderer\RendererInterface $renderer */
        $renderer = $this->_addressConfig->getFormatByCode('html')->getRenderer();
        return $renderer->renderArray($this->addressMapper->toFlatArray($address));
    }
}
