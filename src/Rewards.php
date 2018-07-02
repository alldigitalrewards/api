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
    /** @var string API Endpoint */
    private $apiUrl = '';

    /** @var string API key */
    protected $apiUser;

    /** @var string API key */
    protected $apiKey;

    /** @var string medium image url, 600x400 */
    public $imageURL = '';

    /** @var string full image url, 1440x? */
    private $type = 'get';

    /** @var string full image url, 1440x? */
    private $lang = 'en';

    /** @var string Country Code to filter requests by */
    private $country_code = 'USA';

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
        $this->apiUrl = $endpoint . 'api';
        $this->imageURL = $endpoint . '/resources/app/products/images/';
        $this->apiUser = $apiUser;
        $this->apiKey = $apiKey;
    }

    /**
     * Set the language for return data
     *
     * @param string $lang
     *
     * @return Rewards
     */
    public function setLanguage($lang = 'en')
    {
        $this->lang = $lang;
    }

    /**
     * Get the set language
     *
     * @return Rewards
     */
    public function getLanguage()
    {
        return $this->lang;
    }

    /**
     * @param string $country_code
     */
    public function setCountryCode($country_code)
    {
        $this->country_code = $country_code;
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

        if (empty($params['country_code'])) {
            $params['country_code'] = $this->country_code;
        }

        if( $this->type === 'get' && ! empty ( $params ) ) {
            $url .= '?' . $this->prepareQuery($params) . '&lang=' . $this->lang;
        } else {
            $url .= '?lang=' . $this->lang;
        }

        $url .= '&ip_address=' . $_SERVER['REMOTE_ADDR'];
        try {
            $ch = curl_init($url);
            $this->prepareCURL($ch, $params);
            $data = curl_exec($ch);
            if(!curl_error($ch)) {

                $status = curl_getinfo($ch);
                curl_close($ch);

                return $this->formatResponse($status['http_code'], $data);

            }

            throw new \Exception("Unable to make API Connection");
        } catch (\Exception $e) {
            echo $e->getMessage();
            exit;
        }
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
            $params = $this->prepareQuery($params);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
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
     * @param int $redemptionId
     *
     * @access public
     * @return string
     */
    public function getRedemptionCampaign($redemptionId)
    {
        return $this->call('redemption/' . $redemptionId, 'get');
    }

    /**
     * Fetches pin eligibility
     *
     * This call will request ask the resource if the pin is available
     *
     * @param int $pin
     *
     * @access public
     * @return string
     */
    public function isPinEligible($pin)
    {
        return $this->call('redemption/pin', 'post', ['pin' => $pin]);
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
        return $this->call('redemption/' . $campaignId, 'get');
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
     * Fetches reward groups
     *
     * This call will request the reward groups at the endpoint
     *
     * @access public
     * @return string
     */
    public function getRewardGroups($params = [])
    {
        return $this->call('reward/group', 'get', $params);
    }

    /**
     * Fetches a reward group
     *
     * This call will request the reward group at the endpoint
     *
     * @param int $groupId
     *
     * @access public
     * @return string
     */
    public function getRewardGroup($groupId)
    {
        return $this->call('reward/group/' . $groupId, 'get');
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
    public function getRewardCategories($params = [])
    {
        return $this->call('reward/category', 'get', $params);
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


    /* Gamification */
    public function fetchGameInstance($uniqueId, $gameId, $meta = [])
    {
        return $this->call('gamification/' . $uniqueId, 'post', ['game_id' => $gameId, 'meta' => $meta]);
    }

    /**
     * Fetches a game by it's id
     **
     * @param int $gameId
     *
     * @access public
     * @return string
     */
    public function getGame($gameId)
    {
        return $this->call('gamification/' . $gameId, 'get');
    }

    /**
     * Fetches games
     *
     * This call will request the endpoints active games available for gameplay
     *
     * @param int $page
     * @param int $offset
     *
     * @access public
     * @return string
     */
    public function getGames($page = 1, $offset = 30)
    {
        $params = [
            'page' => $page,
            'offset' => $offset
        ];

        return $this->call('gamification', 'get', $params);
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
     * @param int|string $uniqueId|$email
     *
     * @access public
     * @return string
     */
    public function getUser($userIdentifier)
    {
        if(is_int($userIdentifier)) {
            return $this->call('user/' . $userIdentifier, 'get');
        } else {
            return $this->call('user/' . urlencode($userIdentifier), 'get');
        }
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
    public function getUserTransactions($uniqueId, $page = 1, $offset = 30)
    {
        $params = [
            'page' => $page,
            'offset' => $offset
        ];
        return $this->call('user/' . $uniqueId . '/transaction', 'get', $params);
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
    public function createUserSSOToken($uniqueId)
    {
        return $this->call('user/' . $uniqueId . '/sso', 'post');
    }

    /**
     * Authenticates a single user SSO token
     *
     * This call will authenticate the $uniqueId's resource availability of provided SSO token at the endpoint
     *
     * @param int $uniqueId
     *
     * @access public
     * @return string
     */
    public function getUserSSOToken($token)
    {
        return $this->call('user/sso/' . $token, 'get');
    }

    /**
     * Authenticates a user resource
     *
     * This call will request authentication of $uniqueId's email and password, returning their user
     *
     * @param int $uniqueId
     *
     * @access public
     * @return string
     */
    public function authenticateUser($email, $password)
    {
        $credentials = [
            'email_address' => urlencode($email),
            'password' => sha1($password)
        ];

        return $this->call('user/authenticate/', 'get', $credentials);
    }

    /**
     * Password recovery for user resource
     *
     * This call will request a password recovery token of user resource
     *
     * @param int $uniqueId
     *
     * @access public
     * @return string
     */
    public function fetchPasswordToken($email, $senderEmail, $senderName, $url)
    {
        return $this->call('user/passwordRecovery/', 'get', ['email_address' => $email, 'url' => $url, 'sender_email' => $senderEmail, 'sender_name' => $senderName]);
    }

    /**
     * Password recovery for user resource
     *
     * This call will request a password recovery token of user resource
     *
     * @param int $uniqueId
     *
     * @access public
     * @return string
     */
    public function processPasswordToken($password, $token)
    {
        return $this->call('user/passwordRecovery/', 'post', ['password' => $password, 'token' => $token]);
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
     * If an object is supplied instead of an integer for uniqueId, a user will be generated with the object provided,
     * and the transaction assigned to them. This will enable "Guest Checkouts" . You may optionally supply
     * a unique ID for continuity which will then be used for future exchanges.
     *
     * If you do not provide a unique ID, one will be generated.
     *
     * To create a redemption, please ensure you provide the proper campaign pin & id.
     * If you have any physical products in your reward list, you must provide the shipping
     * address, if not already stored for the resource
     *
     * @param int|object $userIdentifier|user object
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
    public function createUserTransaction($oUser, array $transaction)
    {
        $user = json_decode($this->getUser($oUser->unique_id));
        if($user->success === false) {
            $user = json_decode($this->createUser((array)$oUser));
        }
        //return error messages
        if($user->success === false) {
            return json_encode($user);
        }
        $user = $user->user;

        return $this->call('user/' . $user->unique_id . '/transaction', 'post', $transaction);
    }

    public function creditPoints($uniqueId, $points)
    {
        return $this->call(
            'user/' . $uniqueId . '/point/credit',
            'post',
            ['amount' => $points]
        );
    }

    public function deletePendingRedemption($oUser, $code)
    {
        return $this->call('user/' . $oUser->unique_id . '/pendingRedemption', 'delete', ['pin' => $code]);
    }

    public function getPendingRedemption($oUser, $code)
    {
        $call = $this->call('user/' . $oUser->unique_id . '/pendingRedemption', 'get', ['pin' => $code]);
        return $call;
    }

    public function createPendingRedemption($oUser, array $transaction)
    {
        $user = json_decode($this->getUser($oUser->unique_id));
        if($user->success === false) {
            $user = json_decode($this->createUser((array)$oUser));
        }
        //return error messages
        if($user->success === false) {
            return json_encode($user);
        }
        $user = $user->user;

        return $this->call('user/' . $user->unique_id . '/pendingRedemption', 'post', $transaction);
    }
}
