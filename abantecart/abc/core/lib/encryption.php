<?php
/*------------------------------------------------------------------------------
  $Id$

  AbanteCart, Ideal OpenSource Ecommerce Solution
  http://www.AbanteCart.com

  Copyright © 2011-2017 Belavier Commerce LLC

  This source file is subject to Open Software License (OSL 3.0)
  License details is bundled with this package in the file LICENSE.txt.
  It is also available at this URL:
  <http://www.opensource.org/licenses/OSL-3.0>

 UPGRADE NOTE:
   Do not edit or add to this file if you wish to upgrade AbanteCart to newer
   versions in the future. If you wish to customize AbanteCart for your
   needs please refer to http://www.AbanteCart.com for more information.
------------------------------------------------------------------------------*/

namespace abc\core\lib;

use abc\core\ABC;
use abc\core\helper\AHelperUtils;
use abc\core\engine\Registry;

if (!class_exists('abc\core\ABC')) {
    header('Location: static_pages/?forbidden='.basename(__FILE__));
}

final class AEncryption
{
    private $key;

    function __construct($key)
    {
        $this->key = $key;
    }

    /**
     * Encode function
     *
     * @param string $str
     *
     * @return string
     */
    function encrypt($str)
    {
        if (!$this->key) {
            return $str;
        }

        $enc_str = '';
        if (!$this->checkOpenssl()) {
            //non openssl basic encryption
            for ($i = 0; $i < strlen($str); $i++) {
                $char = substr($str, $i, 1);
                $keychar = substr($this->key, ($i % strlen($this->key)) - 1, 1);
                $char = chr(ord($char) + ord($keychar));
                $enc_str .= $char;
            }
            $enc_str = base64_encode($enc_str);
        } else {
            $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
            $enc_str = base64_encode(openssl_encrypt($str, 'aes-256-cbc', $this->key, 0, $iv).'::'.$iv);
        }
        return str_replace('==', '', strtr($enc_str, '+/', '-_'));
    }

    /**
     * Decode function
     *
     * @param string $enc_str
     *
     * @return string
     */
    function decrypt($enc_str)
    {
        if (!$this->key) {
            return $enc_str;
        }

        $str = '';
        $enc_str = base64_decode(strtr($enc_str, '-_', '+/').'==');
        if (!$this->checkOpenssl()) {
            //non openssl basic decryption
            for ($i = 0; $i < strlen($enc_str); $i++) {
                $char = substr($enc_str, $i, 1);
                $keychar = substr($this->key, ($i % strlen($this->key)) - 1, 1);
                $char = chr(ord($char) - ord($keychar));
                $str .= $char;
            }
        } else {
            list($encrypted_data, $iv) = explode('::', $enc_str, 2);
            $str = openssl_decrypt($encrypted_data, 'aes-256-cbc', $this->key, 0, $iv);
        }
        return trim($str);
    }

    private function checkOpenssl()
    {
        if (!function_exists('openssl_encrypt')) {
            $error_text = 'openssl php-library did not load. It is recommended to '
                .'enable PHP openssl for system to function properly.';
            $registry = Registry::getInstance();
            $log = $registry->get('log');
            if (!is_object($log) || !method_exists($log, 'write')) {
                $log = ABC::getObjectByAlias('ALog');
                $registry->set('log', $log);
            }
            $log->write($error_text);
            return false;
        }
        return true;
    }

    /*
    * Deprecated!!! Do not use as this function will be removed in v1.3!!!!!
    * MD5 based encoding used for passwords. , only used for older passwords in upgraded/migrated stores
    */
    static function getHash($keyword)
    {
        if (!defined('SALT')) {
            //backwards compatibility for extensions prior to 1.2.8
            return md5($keyword.'SALT');
        } else {
            return md5($keyword.SALT);
        }
    }
}

// SSL Based encryption class PHP 5.3 >
// Manual Configuration is required
/*
Requirement: PHP => 5.3 and openSSL enabled

NOTE: Do not confuse SSL data encryption with signed SSL certificates (HTTPS) used for browser access to sites

Configuration:
Add key storage location path.
Add below lines to /abc/config/config.php file. Change path to your specific path on your server
'ENCRYPTION_KEYS_DIR' => '/path/to/keys/'

NOTES:
1. Keep Key in secure location with restricted file permissions for root and apache (webserver)
2. There is no key expiration management.
These needs to be accounted for in key management procedures


Examples:

1. Generate Keys

$password = "123456";
$conf = array (
	'digest_alg'       => 'sha512',
	'private_key_type' => OPENSSL_KEYTYPE_RSA,
	'private_key_bits' => 2048,
	'encrypt_key'      => true
);
$enc = new ASSLEncryption ();
$keys = $enc->generate_ssl_key_pair($conf, $password);
$enc->save_ssl_key_pair($keys, 'key_with_pass');
AHelperUtils::echoArray($keys);

2. Encrypt

$enc = new ASSLEncryption ('key_with_pass');
$enc_str = $enc->encrypt('test text');
echo $enc_str;

3. Decrypt

$enc = new ASSLEncryption ('', 'key_with_pass', $password);
echo $enc->decrypt($enc_str);


Need help configuring, supporting or extending functionality,
contact www.abantecart.com for forum or paid support
*/

final class ASSLEncryption
{
    private $public_key;
    private $private_key;
    private $key_path;
    private $failed_str = "*****";
    public $active = false;
    private $registry;
    private $message;
    private $log;

    //To generate new keys, class can be initiated with no data passed
    function __construct($public_key_name = '', $private_key_name = '', $pass_phrase = null)
    {
        $this->registry = Registry::getInstance();
        $this->log = $this->registry->get('log');
        $this->message = $this->registry->get('messages');

        //Validate if SSL PHP support is installed
        if (!function_exists('openssl_pkey_get_public')) {
            $error =
                "Error: PHP OpenSSL is not available on your server! Check if OpenSSL installed for PHP and enabled";
            $this->log->write($error);
            $this->message->saveError('OpenSSL Error', $error);
            return null;
        }

        //construct key storage path
        //NOTE: ENCRYPTION_KEYS_DIR needs to be added into configuration file
        //Suggested:  Directory to be secured for read a write ONLY for users root and apache (web server).
        if (ABC::env('ENCRYPTION_KEYS_DIR')) {
            $this->key_path = ABC::env('ENCRYPTION_KEYS_DIR');
        } else {
            $this->key_path = ABC::env('DIR_SYSTEM').'keys/';
        }

        if ($public_key_name) {
            $this->public_key = $this->getPublicKey($public_key_name.'.pub');
        }
        if ($private_key_name) {
            $this->loadPrivateKey($private_key_name.'.prv', $pass_phrase);
        }
        $this->active = true;
    }

    /**
     * Generate new Key Private/Public keys pair.
     *
     * @param array         $config      - array with standard openssl_csr_new config-args
     * @param null | string $pass_phrase - is set if want to have a pass-phrase to access private key
     *
     * @return array
     */
    public function generate_ssl_key_pair($config = array(), $pass_phrase = null)
    {
        $default_length = 2048;

        if (!isset($config['private_key_bits'])) {
            $config['private_key_bits'] = $default_length;
        }
        //Set key bits limits
        if ($config['private_key_bits'] < 256) {
            $config['private_key_bits'] = 256;
        } else {
            if ($config['private_key_bits'] > 8192) {
                $config['private_key_bits'] = 8192;
            }
        }

        $config['private_key_type'] = (int)$config['private_key_type'];

        $res = openssl_pkey_new($config);

        //# Do we need to use pass-phrase for the key?
        $private_key = '';
        if ((isset($config['encrypt_key'])) && ($config['encrypt_key'] == true)) {
            openssl_pkey_export($res, $private_key, $pass_phrase);
        } else {
            openssl_pkey_export($res, $private_key);
        }

        $public_key = openssl_pkey_get_details($res);
        $public_key = $public_key["key"];

        return array('public' => $public_key, 'private' => $private_key);
    }

    /**
     * Save Private/Public keys pair to set key_path location
     * Input: Private/Public keys pair array
     *
     * @param array  $keys
     * @param string $key_name
     *
     * @return string
     */
    public function save_ssl_key_pair($keys = array(), $key_name)
    {
        if (!file_exists($this->key_path)) {
            $result = mkdir($this->key_path, 0700, true); // create dir with nested folders
        } else {
            $result = true;
        }
        if (!$result) {
            $error = "Error: Can't create directory ".$this->key_path." for saving SSL keys!";
            $this->log->write($error);
            $this->message->saveError('Create SSL Key Error', $error);
            return $error;
        }
        if (empty($key_name)) {
            $key_name = 'default_key';
        }

        foreach ($keys as $type => $key) {
            $ext = '';
            if ($type == 'private') {
                $ext = '.prv';
            } else {
                if ($type == 'public') {
                    $ext = '.pub';
                }
            }
            $file = $this->key_path.'/'.$key_name.$ext;
            if (file_exists($file)) {
                $error = "Error: Can't create key ".$key_name."! Already Exists";
                $this->log->write($error);
                $this->message->saveError('Create SSL Key Error', $error);
                return $error;
            }
            $handle = fopen($file, 'w');
            fwrite($handle, $key."\n");
            fclose($handle);
        }
        return '';
    }

    /**
     * Get public key based on key name provided. It is loaded if not yet loaded
     * Key's are stored in the path based on the configuration
     *
     * @param string $key_name
     *
     * @return string
     */
    public function getPublicKey($key_name)
    {
        if (empty($this->public_key)) {
            $this->public_key = openssl_pkey_get_public("file://".$this->key_path.$key_name);
        }
        return $this->public_key;
    }

    /**
     * Load private key based on key name provided.
     * Input : Key name and pass-phrase (if used)
     * Key's are stored in the path based on the configuration
     * NOTE: Private key value never returned back
     *
     * @param string $key_name
     * @param string $pass_phrase
     *
     * @return bool
     */
    public function loadPrivateKey($key_name, $pass_phrase = '')
    {
        $this->private_key = openssl_pkey_get_private("file://".$this->key_path.$key_name, $pass_phrase);
        if ($this->private_key) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Decrypt value based on private key ONLY
     *
     * @param string $crypttext
     *
     * @return string
     */
    public function decrypt($crypttext)
    {
        if (empty($crypttext)) {
            return '';
        }
        //check if encryption is off or this is not encrypted string
        if (!$this->active || !base64_decode($crypttext, true)) {
            return $crypttext;
        }

        $cleartext = '';
        if (empty($this->private_key)) {
            $error = "Error: SSL Decryption failed! Missing private key";
            $this->log->write($error);
            return $this->failed_str;
        }

        if ((openssl_private_decrypt(base64_decode($crypttext), $cleartext, $this->private_key)) === true) {
            return $cleartext;
        } else {
            $error = "Error: SSL Decryption based on private key has failed! '
                      .'Possibly corrupted encrypted data or wrong key!";
            $this->log->write($error);
            return $this->failed_str;
        }
    }

    /**
     * Encrypt value based on public key ONLY
     *
     * @param string $cleartext
     *
     * @return string
     */
    public function encrypt($cleartext)
    {
        if (empty($cleartext)) {
            return '';
        }
        //check if encryption is off or this is not encrypted string
        if (!$this->active) {
            return $cleartext;
        }

        $crypttext = '';
        if (empty($this->public_key)) {
            $error = "Error: SSL Encryption failed! Missing public key";
            $this->log->write($error);
            return '';
        }

        if ((openssl_public_encrypt($cleartext, $crypttext, $this->public_key)) === true) {
            return base64_encode($crypttext);
        } else {
            $error = "Error: SSL Encryption based on public key has failed! Possibly encryption error or wrong key!";
            $this->log->write($error);
            return '';
        }
    }

    public function getKeyPath()
    {
        return $this->key_path;
    }

}

// SSL Based data encryption class based on ASSLEncryption class
// Manual Configuration is required

/**
 * This class is managing encryption/description of data in AbanteCart database tables
 * configured in $this->enc_data array
 * These tables need to have specific postfix in the name like '_enc'
 *
 * Configuration:
 * Add below configs to abc/config/config.php file.
 *  'DATA_ENCRYPTION_ENABLED' => true);
 *  'ENCRYPTED_POSTFIX' => '_enc');
 * 'DATA_ENCRYPTION_KEYPAIR' => 'data_enc_key');
 *
 * NOTE: DATA_ENCRYPTION_KEYPAIR is a default key and it needs to be a files' name
 *          portion for public and private keys stored in ENCRYPTION_KEYS_DIR
 * Keys can be generated by ASSLEncryption class (see ASSLEncryption class) or by any other openSSL script
 * Example of keys: data_enc_key for data_enc_key.pub and data_enc_key.prv
 * This is also generated in encryption_data_manager extension
 *
 * Tables SQL:
 * New tables needs to be created with provided SQL.
 * Encryption Data Manager extension runs SQL on install
 *
 * Limitation: pass-phrase is not supported to data encryption.
 **/
final class ADataEncryption
{
    private $key_name;
    private $keys;
    private $pass_phrase;
    private $enc_data;
    private $postfix = '';
    public $active = false;
    private $registry;
    private $message;
    private $log;

    function __construct($key_name = null, $pass_phrase = null)
    {
        //if not enabled exit
        if (!ABC::env('DATA_ENCRYPTION_ENABLED')) {
            return null;
        }
        $this->registry = Registry::getInstance();
        $this->log = $this->registry->get('log');
        $this->message = $this->registry->get('messages');

        //load default key
        if ($key_name) {
            $this->key_name = $key_name;
        } else {
            /** @noinspection PhpUndefinedConstantInspection */
            $this->key_name = ABC::env('DATA_ENCRYPTION_KEYPAIR');
        }
        if ($pass_phrase) {
            $this->pass_phrase = $pass_phrase;
        }

        //load keys from database
        $this->loadKeys();

        //set tables/fields encrypted
        $this->enc_data['orders'] = array(
            'id'     => 'order_id',
            'fields' => array(
                'telephone',
                'fax',
                'email',
                'shipping_company',
                'shipping_address_1',
                'shipping_address_2',
                'shipping_city',
                'shipping_postcode',
                'shipping_country',
                'payment_company',
                'payment_address_1',
                'payment_address_2',
                'payment_city',
                'payment_postcode',
                'payment_country',
            ),
        );
        $this->enc_data['customers'] = array(
            'id'     => 'customer_id',
            'fields' => array(
                'telephone',
                'fax',
                'email',
            ),
        );
        $this->enc_data['addresses'] = array(
            'id'     => 'address_id',
            'fields' => array(
                'company',
                'address_1',
                'address_2',
                'postcode',
                'city',
            ),
        );

        if (ABC::env('ENCRYPTED_POSTFIX')) {
            $this->postfix = ABC::env('ENCRYPTED_POSTFIX');
        } else {
            $this->postfix = '_enc';
        }

        $this->active = true;
    }

    /**
     * Get postfix used to extend tables storing encrypted data
     * This is only for tables that require encryption
     * This is set in ENCRYPTED_POSTFIX configuration
     *
     * @param string $table
     *
     * @return string
     */
    public function postfix($table)
    {
        //check if table requires encryption and there is a postfix
        if ($this->getEncryptedTableID($table)) {
            return $this->postfix;
        } else {
            return '';
        }
    }

    /**
     * Get list of tables containing encrypted data
     *
     * @return array
     */
    public function getEncryptedTables()
    {
        if (AHelperUtils::has_value($this->enc_data)) {
            return array_keys($this->enc_data);
        }
        return array();
    }

    /**
     * Get ID field name for table containing encrypted data
     *
     * @param string
     *
     * @return string
     */
    public function getEncryptedTableID($table)
    {
        if (AHelperUtils::has_value($this->enc_data)) {
            return $this->enc_data[$table]['id'];
        }
        return '';
    }

    /**
     * Get list of encrypted fields in table containing encrypted data
     *
     * @param string
     *
     * @return array
     */
    public function getEncryptedFields($table)
    {
        if (AHelperUtils::has_value($this->enc_data)) {
            return (array)$this->enc_data[$table]['fields'];
        }
        return array();
    }

    /**
     * Add to the list of encrypted tables/fields containing encrypted data
     *
     * @param array $table_data
     *
     * @return null
     */
    public function addEncryptedTables($table_data)
    {
        foreach ($table_data as $table => $data) {
            if (in_array($table, $this->getEncryptedTables())) {
                $error = "ADataEncryption Error: Can't add existing table ".$table."! Table already Exists";
                $this->log->write($error);
            } else {
                $this->enc_data[$table] = $data;
            }
        }
        return null;
    }

    /**
     * Add to the list of fields to existing tables containing encrypted data
     *
     * @since 1.2.7
     *
     * @param string $table
     * @param array  $fields
     *
     * @return null
     */
    public function addEncryptedFields($table, $fields)
    {
        if (empty($table)) {
            return null;
        }

        foreach ($fields as $field) {
            if (!in_array($field, $this->getEncryptedFields($table))) {
                $this->enc_data[$table][] = $field;
            }
        }
        return null;
    }

    /**
     * Decrypt 1 row of data in table for fields that are encrypted
     *
     * @param array       $crypt_data_arr
     * @param string      $table
     * @param null|string $pass
     *
     * @return array
     * @throws AException
     */
    public function decrypt_data($crypt_data_arr, $table, $pass = null)
    {
        if (empty($pass)) {
            $pass = $this->pass_phrase;
        }
        if (empty($table)) {
            return array();
        }
        //if encryption off return pure data
        if (!$this->active) {
            return $crypt_data_arr;
        }

        //detect key to use
        $key_name = $this->detectDecryptKey($crypt_data_arr['key_id']);

        $open_data_arr = $crypt_data_arr;
        $enc = new ASSLEncryption('', $key_name, $pass);
        $fields = $this->getEncryptedFields($table);
        foreach ($crypt_data_arr as $key => $data) {
            if (in_array($key, $fields)) {
                $open_data_arr[$key] = $enc->decrypt($data);
            }
        }
        return $open_data_arr;
    }

    /**
     * Encrypt 1 row of data in table for fields that are encrypted
     *
     * @param array  $open_data_arr
     * @param string $table
     *
     * @return array
     * @throws AException
     */
    public function encrypt_data($open_data_arr, $table)
    {
        if (empty($table)) {
            return array();
        }
        //if encryption off return pure data
        if (!$this->active) {
            return $open_data_arr;
        }

        $key_name = $this->detectEncryptKey($open_data_arr['key_id']);
        $open_data_arr['key_id'] = $this->getKeyIdByName($key_name);

        $crypt_data_arr = $open_data_arr;
        $enc = new ASSLEncryption($key_name);
        $fields = $this->getEncryptedFields($table);
        foreach ($open_data_arr as $key => $data) {
            if (in_array($key, $fields)) {
                $crypt_data_arr[$key] = $enc->encrypt($data);
            }
        }
        return $crypt_data_arr;
    }

    /**
     * Encrypt 1 field of data
     *
     * @param string $open_data
     * @param int    $key_id
     *
     * @return string
     * @throws AException
     */
    public function encrypt_field($open_data, $key_id = 0)
    {
        //if encryption off return pure data
        if (!$this->active) {
            return $open_data;
        }

        //detect key to use
        $key_name = $this->detectEncryptKey($key_id);
        $enc = new ASSLEncryption($key_name);
        return $enc->encrypt($open_data);
    }

    /**
     * Decrypt 1 field of data
     *
     * @param string      $crypt_data
     * @param int         $key_id
     * @param null|string $pass
     *
     * @return string
     * @throws AException
     */
    public function decrypt_field($crypt_data, $key_id = 0, $pass = null)
    {
        if (empty($pass)) {
            $pass = $this->pass_phrase;
        }
        //if encryption off return pure data
        if (!$this->active) {
            return $crypt_data;
        }

        //detect key to use
        $key_name = $this->detectDecryptKey($key_id);

        $enc = new ASSLEncryption('', $key_name, $pass);
        return $enc->decrypt($crypt_data);
    }

    private function loadKeys()
    {
        $config = $this->registry->get('config');
        $cache = $this->registry->get('cache');

        $this->keys = array();
        $cache_key = 'encryption.keys.store_'.(int)$config->get('config_store_id');
        $this->keys = $cache->pull($cache_key);
        if (empty($this->keys)) {
            $db = $this->registry->get('db');
            $query = $db->query("SELECT * FROM ".$db->table_name('encryption_keys')." WHERE status = 1");
            if (!$query->num_rows) {
                return null;
            }
            foreach ($query->rows as $row) {
                $this->keys[$row['key_id']] = $row['key_name'];
            }
            $cache->push($cache_key, $this->keys);
        }
    }

    /**
     * @param int $key_id
     *
     * @return null|string
     * @throws AException
     */
    private function detectEncryptKey($key_id)
    {
        //detect key to use (set default first)
        $key_name = $this->key_name;
        $key_id = (int)$key_id;
        if ($key_id > 0) {
            //we have specific key set for record
            if ($this->keys[$key_id]) {
                $key_name = $this->keys[$key_id];
            } else {
                //something happened. we do nto have a key. Report incident.
                $error = "Error: Can not locate key ID: ".$key_id
                    ." in the encryption_keys table. Attempt to locate default keys! ";
                $this->log->write($error);
                $this->message->saveError('Data decryption error', $error);
                throw new AException (AC_ERR_LOAD, $error);
            }
        }

        /** @noinspection PhpUndefinedConstantInspection */
        if ($key_name == ABC::env('DATA_ENCRYPTION_KEYPAIR') && !ABC::env('DATA_ENCRYPTION_KEYPAIR')) {
            $error =
                "Error: Can not locate default key in configuration file. Refer to data encryption configuration help!";
            $this->log->write($error);
            $this->message->saveError('Data encryption error', $error);
            throw new AException (AC_ERR_LOAD, $error);
        }

        return $key_name;
    }

    /**
     * @param int $key_id
     *
     * @return null|string
     * @throws AException
     */
    private function detectDecryptKey($key_id)
    {
        $key_name = $this->key_name;
        $key_id = (int)$key_id;
        if ($key_id > 0) {
            //we have key set for record
            if ($this->keys[$key_id]) {
                $key_name = $this->keys[$key_id];
            } else {
                //something happened. we do nto have a key. Report incident.
                $error = "Error: Can not locate key ID: ".$key_id
                    ." in the encryption_keys table. Record data might not be decrypted! ";
                $this->log->write($error);
                $this->message->saveError('Data decryption error', $error);
                throw new AException (AC_ERR_LOAD, $error);
            }
        }

        /** @noinspection PhpUndefinedConstantInspection */
        if ($key_name == ABC::env('DATA_ENCRYPTION_KEYPAIR') && !ABC::env('DATA_ENCRYPTION_KEYPAIR')) {
            $error =
                "Error: Can not locate default key in configuration file. Refer to data encryption configuration help!";
            $this->log->write($error);
            $this->message->saveError('Data decryption error', $error);
        }

        return $key_name;
    }

    private function getKeyIdByName($key_name)
    {
        if (!count($this->keys)) {
            return 0;
        }
        foreach ($this->keys as $id => $name) {
            if ($key_name == $name) {
                return $id;
            }
        }
        return 0;
    }
}
