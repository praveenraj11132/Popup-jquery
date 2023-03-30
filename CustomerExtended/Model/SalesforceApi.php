<?php

namespace Wheelpros\CustomerExtended\Model;

use Magento\Customer\Model\Session;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Serialize\Serializer\Json;
use Wheelpros\CustomerExtended\ConfigurationService\Config;

class SalesforceApi
{
    private const GRANT_TYPE = 'password';

    private const ERROR_CODE = 'INVALID_SESSION_ID';

    /**
     * @var Config
     */
    private Config $config;
    /**
     * @var Curl
     */
    private Curl $curl;
    /**
     * @var Json
     */
    private Json $json;
    /**
     * @var Session
     */
    private Session $session;

    /**
     * @param Config  $config
     * @param Curl    $curl
     * @param Json    $json
     * @param Session $session
     */
    public function __construct(
        Config  $config,
        Curl    $curl,
        Json    $json,
        Session $session
    ) {
        $this->config = $config;
        $this->curl = $curl;
        $this->json = $json;
        $this->session = $session;
    }

    /**
     * Get authorization token
     *
     * @return string
     */
    public function getSalesforceAuthToken(): string
    {
        if (!empty($this->session->getAuthToken())) {
            return $this->session->getAuthToken();
        }

        $authApiEndpoint = $this->config->getAuthApiEndpoint();
        $clientID        = $this->config->getClientId();
        $clientSecret    = $this->config->getClientSecret();
        $username        = $this->config->getUsername();
        $password        = $this->config->getPassword();

        // Prepare Query params
        $params = "grant_type=" . self::GRANT_TYPE ."&client_id=$clientID&client_secret=$clientSecret&username=$username&password=$password";

        // make api request
        $this->doPostRequest($authApiEndpoint, $params);

        $response = $this->json->unserialize($this->curl->getBody());

        if (isset($response['access_token'])) {
            $this->session->setAuthToken($response['access_token']);
            return $response['access_token'];
        }

        return "";
    }

    /**
     * Fetch customer orders
     *
     * @return array
     */
    public function fetchOrders()
    {
        $orderApiEndpoint = $this->config->getSalesforceOrderApiEndpoint();
        $authToken = $this->getSalesforceAuthToken();
        $params = "?q=SELECT+FIELDS(ALL)+from+ccrz__E_Order__c+Where+ccrz__Account__c='0010S00000QQJMxQAP'+ORDER+BY+ccrz__OrderDate__c+DESC+LIMIT+200";
        $uri = $orderApiEndpoint . $params;

        // make api request
        $this->doGetRequest($uri, $authToken);

        // handle response
        $responseData = $this->json->unserialize($this->curl->getBody());
        if (isset($responseData[0]['errorCode']) && $responseData[0]['errorCode'] == self::ERROR_CODE) {
            $authToken = $this->getSalesforceAuthToken();
            $this->doGetRequest($uri, $authToken);
            return $this->json->unserialize($this->curl->getBody());
        }

        return $responseData;
    }

    /**
     * Fetch order items
     *
     * @param  string $salesforceOrderId
     * @return array
     */
    public function fetchOrderItems($salesforceOrderId)
    {
        $orderApiEndpoint = $this->config->getSalesforceOrderApiEndpoint();
        $authToken = $this->getSalesforceAuthToken();

        $params = "?q=SELECT+FIELDS(ALL)+from+ccrz__E_OrderItem__c+Where+ccrz__Order__c='$salesforceOrderId'+LIMIT+200";
        $uri = $orderApiEndpoint . $params;

        // make api request
        $this->doGetRequest($uri, $authToken);

        // handle response
        $responseData = $this->json->unserialize($this->curl->getBody());
        if (isset($responseData[0]['errorCode']) && $responseData[0]['errorCode'] == self::ERROR_CODE) {
            $authToken = $this->getSalesforceAuthToken();
            $this->doGetRequest($uri, $authToken);
            return $this->json->unserialize($this->curl->getBody());
        }

        return $responseData;
    }

    /**
     * Do Curl POST request
     *
     * @param  string $uri
     * @param  string $params
     * @return void
     */
    private function doPostRequest(string $uri, string $params)
    {
        $this->curl->post($uri, $params);
    }

    /**
     * Do Curl POST request
     *
     * @param  string $uri
     * @param  string $authToken
     * @return void
     */
    private function doGetRequest(string $uri, string $authToken)
    {
        $this->curl->addHeader('Authorization', "Bearer $authToken");
        $this->curl->get($uri);
    }
}
