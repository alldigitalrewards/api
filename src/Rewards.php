<?php
/**
 * ADR Rewards Marketplace API Wrapper
 *
 * @file Rewards.php
 * @project alldigitalrewards/api
 * @author Zech Walden <zech@zewadesign.com>
 * @created 10/1/15 10:54 AM
 */

namespace ADR;

/**
 * Rewards API Wrapper
 * @package ADR\Rewards
 */
class Rewards
{
    /** @var string vOffice API Endpoint */
    private $apiUrl = '';

    /** @var string vOffice API key */
    protected $apiUser;

    /** @var string vOffice API key */
    protected $apiKey;

    /** @var string vOffice's medium image url, 600x400 */
    public $imageURL = '';

    /** @var string vOffice's full image url, 1440x? */
    private $type = 'get';

    /**
     * Class Construct
     *
     * @param string $endpoint
     * @param string $apiUser
     * @param string $apiKey
     *
     * @return Rewards
     */
    public function __construct($endpoint, $apiUser, $apiKey)
    {
        $this->apiUrl = $endpoint . '/api';
        $this->imageURL = $endpoint . '/resources/app/products/images/';
        $this->apiUser = $apiUser;
        $this->apiKey = $apiKey;
    }

    /**
     * Prepare query string for execution
     *
     * @param array $params Array to generate query string
     *
     * @access private
     * @return string
     */
    private function prepareQuery($params = null)
    {
        $request = "";

        if($params !== null && !empty($params)) {
            $request = http_build_query($params);
        }

        return $request;
    }

    /**
     * Make an API Request
     *
     * @param string $call
     * @param string $type set get, post, put, delete
     * @param array $params fields
     *
     * @access private
     * @return string|bool
     */
    private function call($call, $type = 'get', $params = [])
    {
        $this->type = $type;

        return $this->curl($call, $params);

    }

    /**
     * Format API response
     *
     * @param int $httpCode
     * @param string $response
     *
     * @access private
     * @return string|bool
     */
    private function formatResponse($httpCode, $response)
    {
        switch($httpCode) {
            case 200:
                //convert all members to object
                return $response;//json_decode($response);
                break;
            default:
                return $response;
                break;
        }
    }

    /**
     * Process curl API Request
     *
     * @param string $endpoint
     * @param array $params get or post fields
     *
     * @access private
     * @return string|bool
     */
    private function curl($endpoint, array $params)
    {
        $url = $this->apiUrl . '/' . $endpoint;

        if( $this->type === 'get' && ! empty ( $params ) ) {
            $url .= '?' . $this->prepareQuery($params);
        }

        $ch = curl_init($url);
        $this->prepareCURL($ch, $params);
        $data = curl_exec($ch);
        $status = curl_getinfo($ch);
        curl_close($ch);

        return $this->formatResponse($status['http_code'], $data);
    }

    /**
     * Prepare curl API Request
     *
     * @param object $ch
     * @param array $params
     *
     * @access private
     * @return void
     */
    private function prepareCURL($ch, $params = [])
    {
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($ch, CURLOPT_USERPWD, "$this->apiUser:$this->apiKey");

        if( $this->type !== 'get' ) {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($this->type));

            if( !empty ( $params ) ) {
                $params = $this->prepareQuery($params);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/x-www-form-urlencoded',
            ]);
        }
    }

    /* Redemptions */

    /**
     * Fetches redemptions
     *
     * This call will request the endpoints active redemption campaigns
     *
     * @param int $page
     * @param int $offset
     *
     * @access public
     * @return string
     */
    public function getRedemptionCampaigns($page = 1, $offset = 30)
    {
        $params = [
            'page' => $page,
            'offset' => $offset
        ];

        return $this->call('redemption', 'get', $params);
    }

    /**
     * Fetches redemption
     *
     * This call will request the resource $campaignId at the endpoint
     *
     * @param int $campaignId
     *
     * @access public
     * @return string
     */
    public function getRedemptionCampaign($campaignId)
    {
        return $this->call('redemption/' . $campaignId, 'get');
    }

    /**
     * Fetches redemption pins
     *
     * This call will request the resource $campaignId's available pins at the endpoint
     *
     * @param int $campaignId
     *
     * @access public
     * @return string
     */
    public function getRedemptionCampaignPins($campaignId)
    {
        return $this->call('redemption/' . $campaignId . '/pin', 'get');
    }

    /* Rewards */

    /**
     * Fetches rewards
     *
     * This call will request the endpoints active rewards
     *
     * @param int $page
     * @param int $offset
     * @param array $filters
     * @param bool $filters['hotpick']
     * @param bool $filters['featured']
     * @param string $filters['title']
     * @param int $filters['priceMin']
     * @param int $filters['priceMax']
     * @param int $filters['categoryId']
     * @param array $filters['categoryIds']
     *
     * @access public
     * @return string
     */
    public function getRewards($page = 1, $offset = 30, $filters = [])
    {
        $params = [
            'page' => $page,
            'offset' => $offset
        ];

        if( ! empty ( $filters ) ) {

            $params = array_merge($params, $filters);

        }

        return $this->call('reward', 'get', $params);
    }

    /**
     * Fetches a single reward
     *
     * This call will request the resource $rewardId at the endpoint
     *
     * @param int $rewardId
     *
     * @access public
     * @return string
     */
    public function getReward($rewardId)
    {
        return $this->call('reward/' . $rewardId, 'get');
    }

    /**
     * Fetches reward categories
     *
     * This call will request the reward categories at the endpoint
     *
     * @access public
     * @return string
     */
    public function getRewardCategories()
    {
        return $this->call('reward/category', 'get');
    }

    /**
     * Fetches a reward category
     *
     * This call will request the reward categories at the endpoint
     *
     * @param int $categoryId
     *
     * @access public
     * @return string
     */
    public function getRewardCategory($categoryId)
    {
        return $this->call('reward/category/' . $categoryId, 'get');
    }

    /* Users */

    /**
     * Fetches users
     *
     * This call will request the endpoints active users
     *
     * @param int $page
     * @param int $offset
     *
     * @access public
     * @return string
     */
    public function getUsers($page = 1, $offset = 30)
    {
        $params = [
            'page' => $page,
            'offset' => $offset
        ];

        return $this->call('user', 'get', $params);
    }

    /**
     * Fetches a single user
     *
     * This call will request the resource $uniqueId at the endpoint
     *
     * @param int $uniqueId
     *
     * @access public
     * @return string
     */
    public function getUser($uniqueId)
    {
        return $this->call('user/' . $uniqueId, 'get');
    }

    /**
     * Fetches a single user's transactions
     *
     * This call will request the resource $uniqueId's transactions at the endpoint
     *
     * @param int $uniqueId
     *
     * @access public
     * @return string
     */
    public function getUserTransactions($uniqueId)
    {
        return $this->call('user/' . $uniqueId . '/transaction', 'get');
    }

    /**
     * Fetches a single user's transaction
     *
     * This call will request the resource $uniqueId's transaction by the resource $transactionId at the endpoint
     *
     * @param int $uniqueId
     * @param int $transactionId
     *
     * @access public
     * @return string
     */
    public function getUserTransaction($uniqueId, $transactionId)
    {
        return $this->call('user/' . $uniqueId . '/transaction/' . $transactionId, 'get');
    }

    /**
     * Creates a single user's cart
     *
     * This call will create the resource $uniqueId's cart at the endpoint
     *
     * @param int $uniqueId
     * @param array $rewards
     *
     * @access public
     * @return string
     */
    public function createUserCart($uniqueId, array $rewards)
    {
        return $this->call('user/' . $uniqueId . '/cart', 'post', ['rewards' => $rewards]);
    }

    /**
     * Creates a single user's cart
     *
     * This call will create the resource $uniqueId's cart at the endpoint
     *
     * @param int $uniqueId
     * @param array $rewards
     *
     * @access public
     * @return string
     */
    public function updateUserCart($uniqueId, $cartId, array $rewards)
    {
        return $this->call('user/' . $uniqueId . '/cart/' . $cartId, 'put', ['rewards' => $rewards]);
    }

    /**
     * Fetches a single user's cart
     *
     * This call will request the resource $uniqueId's cart by the resource $cartId at the endpoint
     *
     * @param int $uniqueId
     * @param int $cartId
     *
     * @access public
     * @return string
     */
    public function getUserCart($uniqueId, $cartId)
    {
        return $this->call('user/' . $uniqueId . '/cart/' . $cartId, 'get');
    }

    /**
     * Deletes a single user's cart
     *
     * This call will delete the resource $uniqueId's cart by the resource $cartId at the endpoint
     *
     * @param int $uniqueId
     * @param int $cartId
     *
     * @access public
     * @return string
     */
    public function deleteUserCart($uniqueId, $cartId)
    {
        return $this->call('user/' . $uniqueId . '/cart/' . $cartId, 'delete');
    }

    /**
     * Fetches a single user SSO token
     *
     * This call will request a return of resource $uniqueId's SSO token at the endpoint
     *
     * @param int $uniqueId
     *
     * @access public
     * @return string
     */
    public function getUserSSOToken($uniqueId)
    {
        return $this->call('user/' . $uniqueId . '/sso', 'get');
    }

    /**
     * Create a user
     *
     * This call will create a user at the endpoint
     *
     * @param array $user
     * @param string $user['firstname']
     * @param string $user['lastname']
     * @param string $user['email_address']
     * @param string $user['password']
     * @param int $user['credit']
     * @param int $user['unique_id']
     *
     * @access public
     * @return string
     */
    public function createUser(array $user)
    {
        return $this->call('user', 'post', $user);
    }

    /**
     * Create a user
     *
     * This call will create a user at the endpoint
     *
     * @param int $uniqueId
     * @param array $user
     * @param string $user['firstname']
     * @param string $user['lastname']
     * @param string $user['email_address']
     * @param string $user['password']
     * @param int $user['credit']
     * @param int $user['unique_id']
     *
     * @access public
     * @return string
     */
    public function updateUser($uniqueId, array $user)
    {
        return $this->call('user/' . $uniqueId, 'put', $user);
    }

    /**
     * Create a user transaction
     *
     * This call will create a user transaction with $uniqueId resource at the endpoint
     *
     * To create a redemption, please ensure you provide the proper campaign pin & id.
     * If you have any physical products in your reward list, you must provide the shipping
     * address, if not already stored for the resource
     *
     * @param int $uniqueId
     * @param array $transaction
     * @param array $transaction['rewards']
     * @param string $transaction['campaign_pin'] (optional)
     * @param int $transaction['campaign_id'] (optional)
     * @param array $transaction['shipping_address'] (required)
     * @param string $transaction['shipping_address']['firstname'] (required)
     * @param string $transaction['shipping_address']['lastname'] (required)
     * @param string $transaction['shipping_address']['address'] (optional)
     * @param string $transaction['shipping_address']['secondary_address'] (optional)
     * @param string $transaction['shipping_address']['city'] (optional)
     * @param string $transaction['shipping_address']['state'] (optional)
     * @param string $transaction['shipping_address']['zip'] (optional)
     *
     * @access public
     * @return string
     */
    public function createUserTransaction($uniqueId, array $transaction)
    {
        return $this->call('user/' . $uniqueId . '/transaction', 'post', $transaction);
    }
}