<?php
class Verifiable
{
	// Protect the secret constant from prying eyes.
	private const SECRET_KEY 		= "NwvprhfBkGuPJnjJp77UPJWJUpgC7mLz";

	protected static $verify_array=array();
	/*
		A Verifier array should look like this:
		array(
			"TransactionId" => 'getTransId',
			"UserId" => 'getUserId',
			"CurrencyAmount" => 'getCurrencyAmount'
		);

		This has the key as the Key to use in toArray and the Values are the accessors
	*/

	// Create a SHA1 Hashed string that represents this object as defined by the verify_array.
	public function toVerifierStr()
	{
		$toEncode = self::SECRET_KEY;
		foreach ($static::verify_array as $key => $val)
		{
			$toEncode = $toEncode . call_user_func($this,$val);
		}
		return sha1( $toEncode );
	}

	// Create an Array representation of this object as defined by the verify_array.
	public function toArray()
	{
		$retArr = array();
		foreach ($static::verify_array as $key => $val)
		{
			$retArr[$key] = call_user_func($this,$val);
		}
		return $retArr;
	}

}
?>