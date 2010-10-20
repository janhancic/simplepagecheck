<?php

class SimplePageCheck
{
	protected $checks = Array ();
	protected $recipientEmail = null;

	public function __construct ( $RecipientEmail )
	{
		$this->recipientEmail = $RecipientEmail;
	}

	public function AddCheck ( $Url, $StringToCheck, $IsRegex = false )
	{
		if ( Preg_Match ( "@\b(https?://)(([0-9a-zA-Z_!~*'().&=+$%-]+:)?[0-9a-zA-Z_!~*'().&=+$%-]+\@)?(([0-9]{1,3}\.){3}[0-9]{1,3}|([0-9a-zA-Z_!~*'()-]+\.)*([0-9a-zA-Z][0-9a-zA-Z-]{0,61})?[0-9a-zA-Z]\.[a-zA-Z]{2,6})(:[0-9]{1,4})?((/[0-9a-zA-Z_!~*'().;?:\@&=+$,%#-]+)*/?)@", $Url ) == 0 )
			throw new InvalidArgumentException ( '$Url "' . $Url . '" is not a valid URL.' );

		if ( Is_Bool ( $IsRegex ) === false )
			throw new InvalidArgumentException ( '$IsRegex "' . $IsRegex . '" is not a boolean.' );

		$this->checks[] = Array (
			'url' => $Url,
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
			if ( $this->urlExists ( $check['url'] ) === false )
			{
				$this->sendError ( $check, 'URL not reachable' );

				continue;
			}

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
Check: " . $Check['check'] . "
Regex: " . ( $Check['is_regex'] === true ? 'yes' : 'no' ) . "

$ErrorMessage";

		Mail (
			$this->recipientEmail,
			'SimplePageCheck check error',
			$body
		);
	}

	protected function urlExists ( $Url )
	{
		if ( Function_Exists ( 'curl_init' ) === false )
			return true;
		else
		{
			// from PHP's manual: http://www.php.net/manual/en/function.file-exists.php#85246
			$handle = CUrl_Init ( $Url );
			if ( $handle === false )
				return false;

			CUrl_SetOpt ( $handle, CURLOPT_HEADER, false );
			CUrl_SetOpt ( $handle, CURLOPT_FAILONERROR, true );
			CUrl_SetOpt ( $handle, CURLOPT_HTTPHEADER, Array ( "User-Agent: Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.15) Gecko/20080623 Firefox/2.0.0.15" ) );
			CUrl_SetOpt ( $handle, CURLOPT_NOBODY, true );
			CUrl_SetOpt ( $handle, CURLOPT_RETURNTRANSFER, false );

			$connectable = CUrl_Exec ( $handle );
			CUrl_Close ( $handle );

			return $connectable;
		}
	}
}

?>