<?php
/**
 * Author: Lucus
 */
namespace services;

class AuthorizeService {
    private $_auth = null;
    public function __construct() {
        $this->_auth = \AuthorizeModel::getInstance();
    }

    public function findOrCreate($userId, $uuid) {
       $device = DeviceService::findOrCreateDevice($uuid);
       $deviceId = $device['id'];
       $authorize = $this->_auth->findAuthByDeviceId($userId, $deviceId);
       if ($authorize) {
           return $authorize;
       }
       $accessToken = $this->generateAccessToken();
       $authorize = $this->_auth->createAuthorize($userId, $accessToken, $deviceId);
       return $authorize;
    }

    protected function generateAccessToken() {
        $maxLen = 31;
        if (function_exists('openssl_random_pseudo_bytes')) {
            $randomData = openssl_random_pseudo_bytes($maxLen);
            if ($randomData !== false && strlen($randomData) === $maxLen) {
                return bin2hex($randomData);
            }
        } else if (function_exists('mcrypt_create_iv')) {
            $randomData = @mcrypt_create_iv($maxLen, MCRYPT_DEV_URANDOM);
            if ($randomData !== false && strlen($randomData) === $maxLen) {
                return bin2hex($randomData);
            }
        } else if (@file_exists('/dev/urandom')) {
            $randomData = file_get_contents('/dev/urandom', false, null, 0, $maxLen);
            if ($randomData !== false && strlen($randomData) === $maxLen) {
                return bin2hex($randomData);
            }
        }
        $randomData = mt_rand() . mt_rand() . mt_rand() . mt_rand() . microtime(true) . uniqid(mt_rand(), true);
        return substr(hash('sha512', $randomData), 0, 63);
    }

    public function isLogin($accessToken) {
        $ret = $this->_auth->findAuthByToken($accessToken);
        return $ret;
    }
}
