<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Crypt\Key\Derivation;

/**
 * Salted S2K key generation (OpenPGP document, RFC 2440)
 */
class SaltedS2k
{
    protected static $supportedMhashAlgos = array (
        'md2'        => MHASH_MD2,
        'md4'        => MHASH_MD4,
        'md5'        => MHASH_MD5,
        'sha1'       => MHASH_SHA1,
        'sha224'     => MHASH_SHA224,
        'sha256'     => MHASH_SHA256,
        'sha384'     => MHASH_SHA384,
        'sha512'     => MHASH_SHA512,
        'ripemd128'  => MHASH_RIPEMD128,
        'ripemd256'  => MHASH_RIPEMD256,
        'ripemd320'  => MHASH_RIPEMD320,
        'haval128,3' => MHASH_HAVAL128,
        'haval160,3' => MHASH_HAVAL160,
        'haval192,3' => MHASH_HAVAL192,
        'haval224,3' => MHASH_HAVAL224,
        'haval256,3' => MHASH_HAVAL256,
        'tiger128,3' => MHASH_TIGER128,
        'riger160,3' => MHASH_TIGER160,
        'whirpool'   => MHASH_WHIRLPOOL,
        'snefru256'  => MHASH_SNEFRU256,
        'gost'       => MHASH_GOST,
        'crc32'      => MHASH_CRC32,
        'crc32b'     => MHASH_CRC32B
    );

    /**
     * Generate the new key
     *
     * @param  string  $hash       The hash algorithm to be used by HMAC
     * @param  string  $password   The source password/key
     * @param  int $bytes      The output size in bytes
     * @param  string  $salt       The salt of the algorithm
     * @throws Exception\InvalidArgumentException
     * @return string
     */
    public static function calc($hash, $password, $salt, $bytes)
    {
        if (!in_array($hash, array_keys(static::$supportedMhashAlgos))) {
            throw new Exception\InvalidArgumentException("The hash algorihtm $hash is not supported by " . __CLASS__);
        }
        if (strlen($salt)<8) {
            throw new Exception\InvalidArgumentException('The salt size must be at least of 8 bytes');
        }
        return mhash_keygen_s2k(static::$supportedMhashAlgos[$hash], $password, $salt, $bytes);
    }
}
