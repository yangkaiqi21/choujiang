<?php
namespace app\index\controller;

class CryptDes {

    public $key    = "";
    public $iv    = ""; //like java: private static byte[] iv = [50, 51, 52, 53, 54, 55, 56, 57];

    //加密
    public function encrypt($input)
    {

        $key = base64_decode($this->key);

        $td = mcrypt_module_open( MCRYPT_3DES, '', MCRYPT_MODE_CBC, '');

        $size = mcrypt_get_block_size(MCRYPT_3DES, MCRYPT_MODE_CBC);

        $input = $this->pkcs5_pad($input,$size);

        //使用MCRYPT_3DES算法,cbc模式
        mcrypt_generic_init($td, $key, $this->iv);

        $data = mcrypt_generic($td, $input);

        mcrypt_generic_deinit($td);

        mcrypt_module_close($td);

        $data = base64_encode($data);

        return $data;
    }

    //解密
    public function decrypt($encrypted)
    {
        $encrypted = base64_decode($encrypted);

        $key = base64_decode($this->key);

        //使用MCRYPT_3DES算法,cbc模式
        $td = mcrypt_module_open( MCRYPT_3DES,'',MCRYPT_MODE_CBC,'');

        mcrypt_generic_init($td, $key, $this->iv);

        $decrypted = mdecrypt_generic($td, $encrypted);

        mcrypt_generic_deinit($td);

        mcrypt_module_close($td);

        $decrypted = $this->pkcs5_unpad($decrypted);
        return $decrypted;
    }

    //去填充
    private function pkcs5_unpad($text)
    {
        $pad = ord($text{strlen($text)-1});
        if ($pad > strlen($text)) {
            return false;
        }
        if (strspn($text, chr($pad), strlen($text)-$pad) != $pad){
            return false;
        }
        return substr($text, 0, -1 * $pad);
    }

    //填充
    public function pkcs5_pad($text, $blocksize){
        $pad = $blocksize - (strlen($text) % $blocksize);
        return $text . str_repeat(chr($pad), $pad);
    }
}