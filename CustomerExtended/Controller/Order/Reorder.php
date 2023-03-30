<?php

namespace Wheelpros\CustomerExtended\Controller\Order;

use Magento\Catalog\Model\ProductRepository;
use Magento\Customer\Model\Session;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\Manager;
use Psr\Log\LoggerInterface;
use Wheelpros\CustomerExtended\Model\SalesforceApi;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\Quote;
use Magento\Quote\Api\CartRepositoryInterface;

class Reorder implements ActionInterface
{
    /**
     * @var RequestInterface
     */
    private RequestInterface $request;
    /**
     * @var SalesforceApi
     */
    private SalesforceApi $salesforceApi;
    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;
    /**
     * @var Session
     */
    private Session $session;
    /**
     * @var Manager
     */
    private Manager $messageManager;
    /**
     * @var ResultFactory
     */
    private ResultFactory $resultFactory;
    /**
     * @var QuoteFactory
     */
    private QuoteFactory $quoteFactory;
    /**
     * @var CartRepositoryInterface
     */
    private CartRepositoryInterface $cartRepository;
    /**
     * @var ProductRepository
     */
    private ProductRepository $productRepository;

    /**
     * @param RequestInterface        $request
     * @param SalesforceApi           $salesforceApi
     * @param LoggerInterface         $logger
     * @param Session                 $session
     * @param Manager                 $messageManager
     * @param ResultFactory           $resultFactory
     * @param QuoteFactory            $quoteFactory
     * @param CartRepositoryInterface $cartRepository
     * @param ProductRepository       $productRepository
     */
    public function __construct(
        RequestInterface        $request,
        SalesforceApi           $salesforceApi,
        LoggerInterface         $logger,
        Session                 $session,
        Manager                 $messageManager,
        ResultFactory           $resultFactory,
        QuoteFactory            $quoteFactory,
        CartRepositoryInterface $cartRepository,
        ProductRepository       $productRepository
    ) {
        $this->request = $request;
        $this->salesforceApi = $salesforceApi;
        $this->logger = $logger;
        $this->session = $session;
        $this->messageManager = $messageManager;
        $this->resultFactory = $resultFactory;
        $this->quoteFactory = $quoteFactory;
        $this->cartRepository = $cartRepository;
        $this->productRepository = $productRepository;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $salesforceOrderId = $this->request->getParam('order_id');
        $orderItemsData = $this->salesforceApi->fetchOrderItems($salesforceOrderId);
        $customerId = $this->session->getCustomerId();

        if ($orderItemsData['done'] && $orderItemsData['totalSize'] > 0) {
            foreach ($orderItemsData['records'] as $item) {
                try {
                    $product = $this->productRepository->get($item['ccrz__ExtSKU__c']);
                    $qty = $item['ccrz__Quantity__c'] ?? 1;
                    $quote = $this->cartRepository->getActiveForCustomer($customerId);
                    /** @var Quote $cart */
                    $cart = $this->quoteFactory->create()->loadActive($quote->getId());
                    $cart->addProduct($product, $qty);
                    $this->cartRepository->save($cart);
                } catch (NoSuchEntityException|LocalizedException $e) {
                    return $this->handleError($e);
                } catch (\Exception $e) {
                    return $this->handleError($e);
                }
            }
        } else {
            $this->logger->info('Couldn\'t fetch skus from salesforce order items api');
            $this->messageManager->addErrorMessage('Couldn\'t reorder items');
            return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setUrl('/request/recent/order');
        }

        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setUrl('/checkout/cart');
    }

    /**
     * Handle error
     *
     * @param \Exception $e
     * @return Redirect
     */
    private function handleError($e)
    {
        $this->logger->info('Error while re-ordering items. Error: ' . $e->getMessage());
        $this->messageManager->addErrorMessage('Couldn\'t reorder items');
        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setUrl('/request/recent/order');
    }
}
