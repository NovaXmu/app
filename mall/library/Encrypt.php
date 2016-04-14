<?php
/**
*
*	@copyright  Copyright (c) 2015 Nili
*	All rights reserved
*
*	file:			Encrypt.php
*	description:	对称加密
*
*	@author Nili
*	@license Apache v2 License
*	
**/

/**
* 
*/
class Library_Encrypt
{
	/**
	* 
	*/
	public static function encrypt($key , $variable)
	{
		//基于当前时间和$variable变量产生密文
		$array = array('variable' => $variable, 'time' => date("Y-m-d H:i:s"));
		$key = pack("H*", md5($key));
		
		$plaintext = json_encode($array);
		# 为 CBC 模式创建随机的初始向量
		$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
		$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
		

		# 创建和 AES 兼容的密文（Rijndael 分组大小 = 128）
		# 仅适用于编码后的输入不是以 00h 结尾的
		# （因为默认是使用 0 来补齐数据）
		$ciphertext = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key,
		                             $plaintext, MCRYPT_MODE_CBC, $iv);

		# 将初始向量附加在密文之后，以供解密时使用
		$ciphertext = $iv . $ciphertext;
		
		# 对密文进行 base64 编码
		$ciphertext_base64 = base64_encode($ciphertext);

		# 密文并未进行完整性和可信度保护，
		# 所以可能遭受 Padding Oracle 攻击。<----来自手册，待解决

		return $ciphertext_base64;

	}

	public static function decrypt($key, $ciphertext_base64)
	{
		$key = pack("H*", md5($key));
		$ciphertext_dec = base64_decode($ciphertext_base64);
		
		# 初始向量大小，可以通过 mcrypt_get_iv_size() 来获得
		$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
		$iv_dec = substr($ciphertext_dec, 0, $iv_size);
		
		# 获取除初始向量外的密文
		$ciphertext_dec = substr($ciphertext_dec, $iv_size);

		# 可能需要从明文末尾移除 0
		$plaintext_dec = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key,
		                                $ciphertext_dec, MCRYPT_MODE_CBC, $iv_dec);

		$plaintext_dec = rtrim($plaintext_dec,"\0");
		return $plaintext_dec;//返回
	}
}
?>