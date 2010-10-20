<?php

class SimplePageCheck
{
	protected $checks = Array ();
	protected $recipientEmail = null;

	public function __construct ( $RecipientEmail )
	{
		$this->recipientEmail = $RecipientEmail;
	}

	public function AddCheck ( $Url, $PreCheckUrl, $StringToCheck, $IsRegex = false )
	{
		if ( Preg_Match ( "@\b(https?://)(([0-9a-zA-Z_!~*'().&=+$%-]+:)?[0-9a-zA-Z_!~*'().&=+$%-]+\@)?(([0-9]{1,3}\.){3}[0-9]{1,3}|([0-9a-zA-Z_!~*'()-]+\.)*([0-9a-zA-Z][0-9a-zA-Z-]{0,61})?[0-9a-zA-Z]\.[a-zA-Z]{2,6})(:[0-9]{1,4})?((/[0-9a-zA-Z_!~*'().;?:\@&=+$,%#-]+)*/?)@", $Url ) == 0 )
			throw new InvalidArgumentException ( '$Url "' . $Url . '" is not a valid URL.' );

		if ( $PreCheckUrl !== null && Preg_Match ( "@\b(https?://)(([0-9a-zA-Z_!~*'().&=+$%-]+:)?[0-9a-zA-Z_!~*'().&=+$%-]+\@)?(([0-9]{1,3}\.){3}[0-9]{1,3}|([0-9a-zA-Z_!~*'()-]+\.)*([0-9a-zA-Z][0-9a-zA-Z-]{0,61})?[0-9a-zA-Z]\.[a-zA-Z]{2,6})(:[0-9]{1,4})?((/[0-9a-zA-Z_!~*'().;?:\@&=+$,%#-]+)*/?)@", $PreCheckUrl ) == 0 )
			throw new InvalidArgumentException ( '$PreCheckUrl "' . $PreCheckUrl . '" is not a valid URL.' );

		if ( Is_Bool ( $IsRegex ) === false )
			throw new InvalidArgumentException ( '$IsRegex "' . $IsRegex . '" is not a boolean.' );

		$this->checks[] = Array (
			'url' => $Url,
			'pre_url' => $PreCheckUrl,
			'check' => $StringToCheck,
			'is_regex' => $IsRegex
		);
	}

	public function RunChecks ()
	{
		if ( Count ( $this->checks ) === 0 )
			throw new BadMethodCallException ( 'No checks defined. Add at least one check using "AddCheck" method' );

		foreach ( $this->checks as $key => $check )
		{
			if ( $check['pre_url'] !== null )
				$response = File_Get_Contents ( $check['pre_url'] );

			$response = File_Get_Contents ( $check['url'] );

			if ( $response === false )
			{
				$this->sendError ( $check, 'URL not reachable' );
				continue;
			}

			$checkFound = false;
			if ( $check['is_regex'] === false )
				$checkFound = ( StrPos ( $response, $check['check'] ) !== false );

			if ( $checkFound === false )
				$this->sendError ( $check, 'Check string not found' );
		}
	}

	protected function sendError ( $Check, $ErrorMessage )
	{
$body = "URL: " . $Check['url'] . "
Pre check URL: " . ( $Check['pre_url'] === null ? 'n/a' : $Check['pre_url'] ) . "
Check: " . $Check['check'] . "
Regex: " . ( $Check['is_regex'] === true ? 'yes' : 'no' ) . "

$ErrorMessage";

		Mail (
			$this->recipientEmail,
			'SimplePageCheck check error',
			$body
		);
	}
}

?>