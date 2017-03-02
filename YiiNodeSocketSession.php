<?php
namespace dejmon\yii2sockets;

use Yii;
use yii\redis\Session;

/**
 * Created by PhpStorm.
 * User: coder1
 * Date: 25.10.16
 * Time: 16:55
 */
class YiiNodeSocketSession extends Session {
    /**
     * Generates a unique key used for storing session data in cache.
     * @param string $id session variable name
     * @return string a safe cache key associated with the session variable name
     */
    protected function calculateKey($id)
    {
        return $this->keyPrefix . md5($id);
    }
}