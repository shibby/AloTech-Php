<?php
/**
 * Alo-Tech Api Php
 * @author Güven Atbakan <guvenatbakan@gmail.com>
 */
namespace AloTech;

/**
 * Api Class
 */
class Api
{
    /**
     * ApiUrl, generated auto by tenantName
     * @var string
     */
    protected $apiUrl = '';

    /**
     * Your application token. Please go to "Admin > Applications" to generate your app token.
     * @var string
     */
    protected $appToken = '';
    /**
     * Your company's tenant name. Also known as domain prefix.
     * @var string
     */
    protected $tenantName;

    /**
     * Parameters to send alo-tech server
     * @var array
     */
    private $parameters = [];
    /**
     * Response status. success/error
     * @var string
     */
    private $status = '';
    /**
     * Message from returning server
     * @var string
     */
    private $message = '';
    /**
     * Api result without status and message
     * @var null|array
     */
    private $data = NULL;

    /**
     * Api constructor.
     * @param string $tenantName
     * @param string $appToken Mandatory. Application token supplied by Alotech.
     */
    public function __construct($tenantName, $appToken)
    {
        $this->tenantName = $tenantName;
        $this->apiUrl = 'http://' . $tenantName . '.alo-tech.com/api/';
        $this->appToken = $appToken;
        $this->parameters['app_token'] = $appToken;
    }

    /**
     * You can test your keys with this function
     * @return $this
     */
    public function ping()
    {
        $this->parameters['function'] = 'ping';
        return $this;
    }

    /**
     * Get user details with email
     * @param string $userId Mandatory. Username (Email) of the user.
     * @return $this
     */
    public function getUser($userId)
    {
        $this->parameters['function'] = 'getuser';
        $this->parameters['userid'] = $userId;
        return $this;
    }

    /**
     * Adds a new contact to Dialer Campaign.
     *
     * @param integer $campaign Mandatory. Unique ID of the contact on Alotech platform.
     * @param string $uniqueId Optional. A uniqueid value to match the contact on reports.
     * @param string $name Optional. Name of the contact.
     * @param string $surname Optional. Surname of the contact.
     * @param string $email Optional. Email of the contact.
     * @param string $homePhone Partly optional. Home phone number of the contact in e164 format.
     * @param string $businessPhone Partly optional. Business phone number of the contact in e164 format.
     * @param string $mobilePhone Partly optional. Mobile phone number of the contact in e164 format.
     * @param string $customerPhone Partly optional. Custom phone number of the contact in e164 format.
     * @param string $listName Optional. List name of the contact.
     * @param array $customFields Custom fields in array.
     * @return $this
     */
    public function addContactToCampaign(
        $campaign, $uniqueId = '', $name = '', $surname = '', $email = '',
        $homePhone = '', $businessPhone = '', $mobilePhone = '', $customerPhone = '',
        $listName = '', $customFields = []
    )
    {
        $this->parameters['function'] = 'addcontacttocampaign';

        $this->parameters['campaign'] = $campaign;
        $this->parameters['uniqueid'] = $uniqueId;
        $this->parameters['name'] = $name;
        $this->parameters['surname'] = $surname;
        $this->parameters['email'] = $email;
        $this->parameters['homePhone'] = $homePhone;
        $this->parameters['businessPhone'] = $businessPhone;
        $this->parameters['mobilePhone'] = $mobilePhone;
        $this->parameters['customerPhone'] = $customerPhone;
        $this->parameters['listName'] = $listName;
        if ($customFields) {
            $this->parameters['customFields'] = json_encode($customFields);
        }

        return $this;
    }

    /**
     * Send your request to alotech server
     * @throws \Exception
     */
    public function send()
    {
        $url = $this->apiUrl . "?" . http_build_query($this->parameters);

        $guzzle = new \GuzzleHttp\Client();
        $request = $guzzle->request('GET', $url);
        if ($request->getStatusCode() != "200") {
            throw new \Exception("Sunucuya istek gönderilemedi. Kahrolsun bağzı hatalar");
        }

        $result = (array)json_decode($request->getBody());


        if (isset($result['success']) && $result['success'] == 1) {
            $this->status = 'success';
        } else {
            $this->status = 'error';
        }
        if (isset($result['message'])) {
            $this->message = $result['message'];
        }
        unset($result['success']);
        unset($result['error']);
        unset($result['message']);

        if ($result) {
            $this->data = $result;
        }

        $this->parameters = [];
        $this->parameters['app_token'] = $this->appToken;


        return $this->formatResponse();
    }

    /**
     * Formats response.
     * @return array
     */
    public function formatResponse()
    {
        return [
            'status' => $this->status,
            'message' => $this->message,
            'data' => $this->data
        ];
    }


}