<?php
namespace dejmon\yii2sockets;
use Yii;
use yii\base\Component;
use yii\base\Exception;
use yii\helpers\VarDumper;
/**
 *
 */

class YiiNodeSocket extends Component {

    /**
     * Cookie name
     * @var string
     */
    public $sessionVarName = 'SESSID';
    public $sessionKeyPrefix = '';
    public $serviceKey = 'qwerty';

    public $nodeJsHost = 'localhost';
    public $nodeJsHostClient = 'localhost';
    public $nodeJsPort = '3001';
    public $nodeJsScheme = 'http';
    public $nodeJsServerBase = '/server';

    /**
     * Ssl key path
     * @var string
     */
    public $sslKeyPath;

    /**
     * Ssl Cert path
     * @var string
     */
    public $sslCertPath;

    /**
     * SSL CA path
     * @var string
     */
    public $sslCaPath;

    public $channelsByPermissions = [];

    public $userSocketId;

    public function init()
    {
        parent::init();
        //Get current user socket id
        $headers = Yii::$app->request->hasMethod('getHeaders') ? Yii::$app->request->getHeaders() : [];
        if(!empty($headers) && !empty($headers['yii-node-socket-id'])) {
            $userSocketId = is_array($headers['yii-node-socket-id']) ? reset($headers['yii-node-socket-id']) : $headers['yii-node-socket-id'];
            $this->userSocketId = !empty($userSocketId) ? $userSocketId : null;
        }
    }

    /**
     * Check that user has connected socket (by request headers)
     * @return bool
     */
    public function hasSocketConnected() {
        return !empty($this->userSocketId);
    }

    /**
     * Get base url of Node.js server
     * @return string
     */
    protected function getNodeBaseUrl() {
        return $this->nodeJsScheme . '://' . $this->nodeJsHost . ':' . $this->nodeJsPort;
    }

    /**
     * Add auto connect channel while session start
     * @param $channel
     */
    public function addUserSessionChannel($channel) {
        $_SESSION['nodejs'] = isset($_SESSION['nodejs']) ? $_SESSION['nodejs'] : [];
        $_SESSION['nodejs']['channels'] = isset($_SESSION['nodejs']['channels']) ? $_SESSION['nodejs']['channels'] : [];
        $_SESSION['nodejs']['channels'][$channel] = $channel;
    }

    /**
     * Remove auto connect channel from user
     * @param $channel
     */
    public function removeUserSessionChannel($channel) {
        if(isset($_SESSION['nodejs']['channels'][$channel])) {
            unset($_SESSION['nodejs']['channels'][$channel]);
            $this->removeSessionFromChannel(session_id(), $channel);
        }
    }

    public function reloadUserChannels($sid = null) {
        $url = $this->getNodeBaseUrl() . $this->nodeJsServerBase . '/reload_user_channels';
        $data = [
            'sid' => $sid,
        ];
        $this->sendDataToNodeJS($data, $url);
    }

    /**
     * Send message to user ID
     * @param mixed $message
     * @param integer $uid
     * @param string $callback
     * @return bool|mixed
     */
    public function sendMessageToUser($message, $uid = 0, $callback = '') {
        $data = [
            'body' => $message,
            'userId' => $uid,
            'callback' => $callback,
        ];
        return $this->sendMessage($data);
    }

    /**
     * Send message to session ID
     * @param mixed $message
     * @param string $sid
     * @param string $callback
     * @return bool|mixed
     */
    public function sendMessageToSession($message, $sid = '', $callback = '') {
        if(!$sid) return false;
        $data = [
            'body' => $message,
            'sessionId' => $sid,
            'callback' => $callback,
        ];
        return $this->sendMessage($data);
    }

    /**
     * Send message to session ID
     * @param mixed $message
     * @param string $socketId
     * @param string $callback
     * @return bool|mixed
     */
    public function sendMessageToSocket($message, $socketId = '', $callback = '') {
        if(!$socketId) return false;
        $data = [
            'body' => $message,
            'socketId' => $socketId,
            'callback' => $callback,
        ];
        return $this->sendMessage($data);
    }

    /**
     * Send message to channel
     * @param mixed $message
     * @param string $channel
     * @param string $callback
     * @return mixed
     */
    public function sendMessageToChannel($message, $channel = 'notify', $callback = '') {
        $data = [
            'body' => $message,
            'channel' => $channel,
            'callback' => $callback,
        ];
        return $this->sendMessage($data);
    }

    /**
     * Send message
     * @param $message
     * @return mixed
     */
    public function sendMessage($message) {
        $url = $this->getNodeBaseUrl() . $this->nodeJsServerBase . '/publish_message';
        return $this->sendDataToNodeJS($message, $url);
    }

    /**
     * Add session to channel
     * @param string $sid
     * @param string $channel
     * @return mixed
     */
    public function addSessionToChannel($sid, $channel) {
        $url = $this->getNodeBaseUrl() . $this->nodeJsServerBase . '/add_session_to_channel';
        $data = [
            'sid' => $sid,
            'channel' => $channel,
        ];
        return $this->sendDataToNodeJS($data, $url);
    }

    /**
     * Remove session from channel
     * @param string $sid
     * @param string $channel
     * @return mixed
     */
    public function removeSessionFromChannel($sid, $channel) {
        $url = $this->getNodeBaseUrl() . $this->nodeJsServerBase . '/remove_session_from_channel';
        $data = [
            'sid' => $sid,
            'channel' => $channel,
        ];
        return $this->sendDataToNodeJS($data, $url);
    }

    /**
     * Add user to channel
     * @param integer $uid
     * @param string $channel
     * @return mixed
     */
    public function addUserToChannel($uid, $channel) {
        $url = $this->getNodeBaseUrl() . $this->nodeJsServerBase . '/add_user_to_channel';
        $data = [
            'uid' => $uid,
            'channel' => $channel,
        ];
        return $this->sendDataToNodeJS($data, $url);
    }

    /**
     * Remove user from channel
     * @param integer $uid
     * @param string $channel
     * @return mixed
     */
    public function removeUserFromChannel($uid, $channel) {
        $url = $this->getNodeBaseUrl() . $this->nodeJsServerBase . '/remove_user_from_channel';
        $data = [
            'uid' => $uid,
            'channel' => $channel,
        ];
        return $this->sendDataToNodeJS($data, $url);
    }

    /**
     * Adds new channel
     * @param string $channel
     * @return mixed
     */
    public function addChannel($channel)
    {
        $url = $this->getNodeBaseUrl() . $this->nodeJsServerBase . '/add_channel';
        $data = [
            'channel' => $channel,
        ];

        return $this->sendDataToNodeJS($data, $url);
    }

    /**
     * Adds user to channels
     * @param integer $uid
     * @param array $channels
     * @return mixed
     */
    public function addUserToChannels($uid, $channels)
    {
        $url = $this->getNodeBaseUrl() . $this->nodeJsServerBase . '/add_user_to_channels';
        $data = [
            'uid' => $uid,
            'channels' => $channels,
        ];
        return $this->sendDataToNodeJS($data, $url);
    }

    /**
     * Adds new channel
     * @param string $channels
     * @return mixed
     */
    public function addChannels($channels)
    {
        $url = $this->getNodeBaseUrl() . $this->nodeJsServerBase . '/add_channels';
        $data = [
            'channels' => $channels,
        ];

        return $this->sendDataToNodeJS($data, $url);
    }

    /**
     * Send any data to Node.js server
     * @param mixed $data
     * @param string $url
     * @return mixed
     * @throws Exception
     */
    public function sendDataToNodeJS($data, $url) {
        $result = false;
        $curl = curl_init($url);
        curl_setopt_array($curl, [
            CURLOPT_CAINFO => $this->sslCertPath,
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => http_build_query($data),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [ 'NodejsServiceKey: ' . $this->serviceKey ],
        ]);
        try {
            $nodeOut = curl_exec($curl);
            if (!$nodeOut) {
                throw new Exception(curl_error($curl));
            }
            //try to decode JSON data
            $nodeOutJSON = @json_decode($nodeOut, true);
            curl_close ($curl);
            $result = $nodeOutJSON ? $nodeOutJSON : $nodeOut;
        } catch (Exception $e) {
            Yii::error(VarDumper::dumpAsString($e->getMessage(), 3));
        }
        return $result;
    }

    /**
     * @return YiiNodeSocketFrameBasic
     */
    public function newMessage() {
        return new YiiNodeSocketFrameBasic();
    }

    /**
     * @return YiiNodeSocketFrameJQuery
     */
    public function newJQuery() {
        return new YiiNodeSocketFrameJQuery();
    }

    /**
     * @return YiiNodeSocketFrameGrowl
     */
    public function newNotify() {
        return new YiiNodeSocketFrameGrowl();
    }
}