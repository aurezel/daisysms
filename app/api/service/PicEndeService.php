<?php
/*
 module:		会员列表
 create_time:	2020-05-10 13:00:18
 author:
 contact:
*/

namespace app\api\service;
use xhadmin\CommonService;
class PicEndeService extends CommonService {

    const ENCRYPT_KEY = 'xAFC6RR8Nt3q1Ixx';
    const ENCRYPT_SECRET = 'RsR6NbLh4H2zEUSU';

    /** @inheritdoc */
    public function encrypt($text) {
        return openssl_encrypt($text, 'AES-128-CBC', self::ENCRYPT_KEY, 0, self::ENCRYPT_SECRET);
    }

    /** @inheritdoc */
    public function decrypt($cipherText) {
        return openssl_decrypt($cipherText, 'AES-128-CBC', self::ENCRYPT_KEY, 0, self::ENCRYPT_SECRET);
    }
}

