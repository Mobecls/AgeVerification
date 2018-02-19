<?php

namespace Magenmagic\AgeVerification\Model;

use Magenmagic\AgeVerification\Helper\Data;
use Magenmagic\AgeVerification\Model\EVSRequest\RequestObject;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\HTTP\Adapter\CurlFactory;
use \Psr\Log\LoggerInterface;
use Zend\Http\PhpEnvironment\RemoteAddress;

/**
 * @category   Magenmagic
 * @package    Magenmagic_AgeVerification
 * @author     Alex Brynov
 */
class EVSRequest
{
    /**
     * Request timeout in sec
     *
     * @int
     */
    protected $requestTimeout = 5;

    /**
     * @var string
     */
    protected $serviceUrl = 'https://identiflo.everification.net/WebServices/Integrated/Main/V210/ConsumerPlus';

    /**
     * @var array
     */
    protected $allowedCountries = ['US'];

    /**
     * Only 1 and 9 states are legitimate according to the documentation
     *
     * @var array
     */
    protected $validCodes = [1, 9];

    /**
     * @var CurlFactory
     */
    protected $curlFactory;

    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * @var RemoteAddress
     */
    protected $remoteAddress;

    /**
     * @var RequestObject
     */
    protected $identity;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var RequestObject
     */
    private $requestObject;

    /**
     * EVSRequest constructor.
     *
     * @param CurlFactory     $curlFactory
     * @param Data            $dataHelper
     * @param RemoteAddress   $remoteAddress
     * @param LoggerInterface $logger
     * @param RequestObject   $requestObject
     */
    public function __construct(
        CurlFactory $curlFactory,
        Data $dataHelper,
        RemoteAddress $remoteAddress,
        LoggerInterface $logger,
        RequestObject $requestObject
    ) {
        $this->curlFactory   = $curlFactory;
        $this->dataHelper    = $dataHelper;
        $this->remoteAddress = $remoteAddress;
        $this->logger        = $logger;
        $this->requestObject = $requestObject;
    }

    /**
     * @param RequestObject $request
     *
     * @return bool
     */
    public function validate(RequestObject $request)
    {
        try {
            if (!in_array($request->getCountry(), $this->allowedCountries)) {
                return false;
            }

            $this->identity = $request;

            $http = $this->curlFactory->create();

            $http->setConfig(['timeout' => 60])
                ->write(\Zend_Http_Client::POST, $this->serviceUrl, '1.1', ['Content-Type: text/xml'], $this->getXml());

            $response = $http->read();

            $responseCode = \Zend_Http_Response::extractCode($response);

            if ($responseCode !== 200) {
                throw new \Exception(sprintf('Error code %d returned', $responseCode));
            }

            $response = preg_split('/^\r?$/m', $response, 2);
            $response = trim($response[1]);

            return in_array($this->getCode($response), $this->validCodes);
        } catch (\Exception $e) {
            $this->logger->alert(
                sprintf('Magenmagic_AgeVerification:%s:', $e->getMessage())
            );
        }

        return false;
    }

    /**
     * @param CustomerInterface $customer
     *
     * @return EVSRequest\RequestObject|null
     */
    protected function makeRequestObject(CustomerInterface $customer)
    {
        if (!($addresses = $customer->getAddresses())) {
            return null;
        }
        /** @var EVSRequest\RequestObject $object */
        $object = ObjectManager::getInstance()->get(EVSRequest\RequestObject::class);

        return $object->setEmail($customer->getEmail())
            ->setFirstName($customer->getFirstname())
            ->setLastName($customer->getLastname())
            ->setMiddleName($customer->getMiddlename())
            ->setCountry($addresses[0]->getCountryId())
            ->setCity($addresses[0]->getCity())
            ->setZipCode($addresses[0]->getPostcode())
            ->setState($addresses[0]->getRegion()->getRegion())
            ->setStreet(implode(' ', $addresses[0]->getStreet()))
            ->setDateOfBirth($customer->getDob());
    }

    /**
     * Full documentation available here
     * https://identiflo.everification.net/Help/GetFile?file=ApiDocumentPdf
     *
     * @return string
     */
    protected function getXml()
    {
        $requestXml = '<?xml version="1.0" encoding="UTF-8"?>' .
            '<PlatformRequest>' .
            '<Credentials>' .
            '<Username>' . $this->dataHelper->getApiUsername() . '</Username>' .
            '<Password>' . $this->dataHelper->getApiPassword() . '</Password>' .
            '</Credentials>' .
            '<CustomerReference>' . $this->getCustomerReference() . '</CustomerReference>' .
            '<Identity>' .
            '<FirstName>' . $this->identity->getFirstName() . '</FirstName>' .
            '<MiddleName>' . $this->identity->getMiddleName() . '</MiddleName>' .
            '<LastName>' . $this->identity->getLastName() . '</LastName>' .
            '<Street>' . $this->identity->getStreet() . '</Street>' .
            '<City>' . $this->identity->getCity() . '</City>' .
            '<State>' . $this->identity->getState() . '</State>' .
            '<ZipCode>' . $this->identity->getZipCode() . '</ZipCode>' .
            '<DateOfBirth>' . $this->identity->getDateOfBirth() . '</DateOfBirth>' .
            '<EmailAddress>' . $this->identity->getEmail() . '</EmailAddress>' .
            '<IpAddress>' . $this->remoteAddress->getIpAddress() . '</IpAddress>' .
            '</Identity>' .
            '</PlatformRequest>';

        return $requestXml;
    }

    /**
     * @param RequestObject $identity
     *
     * @return $this
     */
    protected function setIdentity(RequestObject $identity)
    {
        $this->identity = $identity;

        return $this;
    }

    /**
     * @return string
     */
    protected function getCustomerReference()
    {
        return $this->identity->getFirstName() . ' - ' . $this->identity->getDateOfBirth();
    }

    /**
     * @param $response
     *
     * @return int
     * @throws \Exception
     */
    protected function getCode($response)
    {
        if ($response) {
            $xml = simplexml_load_string($response);

            if (empty($xml->TransactionDetails->Errors)) {
                $code = $xml->Response->DateOfBirthResult->attributes();

                return $code['code'] ? $code['code'] : 0;
            } else {
                $error = $xml->TransactionDetails->Errors[0]->Error->attributes();

                throw new \Exception((int)$error['code'] . ':' . (string)$error['message']);
            }
        }

        return null;
    }
}