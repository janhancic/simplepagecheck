<?php

class SimplePageCheck
{
	protected $sites = Array ();

	public function AddCheck ( $Url, $StringToCheck, $IsRegex = false )
	{
		if ( Preg_Match ( "@\b(https?://)(([0-9a-zA-Z_!~*'().&=+$%-]+:)?[0-9a-zA-Z_!~*'().&=+$%-]+\@)?(([0-9]{1,3}\.){3}[0-9]{1,3}|([0-9a-zA-Z_!~*'()-]+\.)*([0-9a-zA-Z][0-9a-zA-Z-]{0,61})?[0-9a-zA-Z]\.[a-zA-Z]{2,6})(:[0-9]{1,4})?((/[0-9a-zA-Z_!~*'().;?:\@&=+$,%#-]+)*/?)@", $Url ) == 0 )
			throw new InvalidArgumentException ( '$Url "' . $Url . '" is not a valid URL.' );

		if ( Is_Bool ( $IsRegex ) === false )
			throw new InvalidArgumentException ( '$IsRegex "' . $IsRegex . '" is not a boolean.' );

		$this->sites[] = Array (
			'url' => $Url,
			'check' => $StringToCheck,
			'is_regex' => $IsRegex
		);
	}
}

?>