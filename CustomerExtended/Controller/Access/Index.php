<?php

namespace Wheelpros\CustomerExtended\Controller\Access;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class Index implements HttpPostActionInterface
{

    const XML_PATH_FOR_RECIPIENT_EMAIL_REQUEST_ACCESS="wheelpros_customer/request_access_form/recipient_email";

    const XML_PATH_FOR_FROM_EMAIL_CODE_REQUEST_ACCESS="wheelpros_customer/request_access_form/send_email_code";

    private JsonFactory $jsonFactory;

    private TransportBuilder $transportBuilder;

    private StoreManagerInterface $storeManager;

    private RequestInterface $request;

    private ScopeConfigInterface $scopeConfig;
    private ManagerInterface $manager;

    /**
     * @param JsonFactory $jsonFactory
     * @param TransportBuilder $transportBuilder
     * @param StoreManagerInterface $storeManager
     * @param RequestInterface $request
     * @param ScopeConfigInterface $scopeConfig
     * @param ManagerInterface $manager
     */
    public function __construct(
        JsonFactory           $jsonFactory,
        TransportBuilder      $transportBuilder,
        StoreManagerInterface $storeManager,
        RequestInterface      $request,
        ScopeConfigInterface   $scopeConfig,
        ManagerInterface      $manager
    ) {
        $this->jsonFactory = $jsonFactory;
        $this->transportBuilder = $transportBuilder;
        $this->storeManager = $storeManager;
        $this->request = $request;
        $this->scopeConfig = $scopeConfig;
        $this->manager = $manager;
    }

    /**
     * Execute action based on request and return result
     *
     * @return Json
     */
    public function execute()
    {
        $result = $this->jsonFactory->create();
        try {
            $templateOptions = ['area' => Area::AREA_FRONTEND, 'store' => $this->storeManager->getStore()->getId()];
            $params = $this->request->getParams();
            $toEmail = $this->scopeConfig->getValue(
                self::XML_PATH_FOR_RECIPIENT_EMAIL_REQUEST_ACCESS,
                ScopeInterface::SCOPE_STORE
            );
            $toEmail = explode(',', $toEmail);
            $templateVars = [
                'store' => $this->storeManager->getStore(),
                'firstname' => $params['request']['firstname'],
                'lastname' => $params['request']['lastname'],
                'email' => $params['request']['email'],
                'business_name' => $params['request']['businessname'],
                'account_number' => $params['request']['accountnumber'],
            ];
            $fromEmailCode = $this->scopeConfig->getValue(
                self::XML_PATH_FOR_FROM_EMAIL_CODE_REQUEST_ACCESS,
                ScopeInterface::SCOPE_STORE
            );
            $fromEmail = $this->scopeConfig->getValue(
                'trans_email/ident_' . $fromEmailCode . '/email',
                ScopeInterface::SCOPE_STORE
            );
            $fromName = $this->scopeConfig->getValue(
                'trans_email/ident_' . $fromEmailCode . '/name',
                ScopeInterface::SCOPE_STORE
            );
            $from = ['email' => $fromEmail, 'name' => $fromName];
            $transport = $this->transportBuilder->setTemplateIdentifier('request_access')
                ->setTemplateOptions($templateOptions)
                ->setTemplateVars($templateVars)
                ->setFromByScope($from)
                ->addTo($toEmail)
                ->getTransport();
            $transport->sendMessage();
            $this->manager->addSuccessMessage("Form submitted successfully");
            $result->setData(
                [
                    'success' => true,
                    'message' => "Form submitted successfully",
                ]
            );
        } catch (\Exception $e) {
            $this->manager->addErrorMessage($e->getMessage());
            $result->setData(
                [
                    'success' => false,
                    'message' => $e->getMessage(),
                ]
            );
        }
        return $result;
    }
}
