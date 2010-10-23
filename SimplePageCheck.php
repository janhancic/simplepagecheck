<?php

/*
	Author: Jan Hančič
	A class to check if a web page(s) contain a certain string or not. The string can be detected with a regular expression.
	Provides two ways of reporting errors. Via email or to the standard output.
	You have to call either 'ReportToEmail()' or 'ReportToOutput()' (or both) before you call 'RunChecks()' method. If you don't an exception will be thrown.
*/
class SimplePageCheck
{
	protected $checks = Array ();
	protected $reportToOutput = false;
	protected $recipientEmail = null;
	protected $oneEmailPerError = null;
	protected $inCli = false;

	public function __construct ()
	{
		if ( Php_Sapi_Name () === 'cli' )
			$this->inCli = true;
	}

	/*
		Sets if errors should be reported to the email or not.
		Params:
			(string)$RecipientEmail - errors will be sent to this email address
			(bool)$OneEmailPerError - if true, only one email will be sent with all errors, otherwise every error will be sent in it's own email
	*/
	public function ReportToEmail ( $RecipientEmail, $OneEmailPerError = true )
	{
		if ( Is_Bool ( $OneEmailPerError ) === false )
			throw new InvalidArgumentException ( '$OneEmailPerError "' . $OneEmailPerError . '" is not a boolean.' );

		$this->recipientEmail = $RecipientEmail;
		$this->oneEmailPerError = $OneEmailPerError;
	}

	/*
		Sets if errors should be reported to the standard output or not.
	*/
	public function ReportToOutput ( $Val )
	{
		$this->reportToOutput = $Val;
	}

	/*
		Adds a page to be checked.
		Params:
			(string)$Url - URL of the page to check
			(string/null)$PreCheckUrl - URL of the page to open before opening $Url, set to NULL if you don't need this
			(string/regex)$StringToCheck - a string or regular expression to check for on the $Url page
			(bool)$IsRegex - is $StringToCheck a regular expression or not
	*/
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

	/*
		Runs checks on all pages defined with 'AddCheck()' method.
	*/
	public function RunChecks ()
	{
		if ( $this->reportToOutput === false && $this->recipientEmail === null )
			throw new BadMethodCallException ( 'No reporting defined. Call "ReportToEmail()" or/and "ReportToOutput()" method.' );

		if ( Count ( $this->checks ) === 0 )
			throw new BadMethodCallException ( 'No checks defined. Add at least one check using "AddCheck()" method.' );

		$errors = Array ();

		foreach ( $this->checks as $key => $check )
		{
			if ( $check['pre_url'] !== null )
				File_Get_Contents ( $check['pre_url'] ); // just fetch the page, we don't need it's contents

			$response = File_Get_Contents ( $check['url'] );

			if ( $response === false )
			{
				$errors[] = Array ( $check, 'URL not reachable' );
				continue;
			}

			$checkFound = false;
			if ( $check['is_regex'] === false )
				$checkFound = ( StrPos ( $response, $check['check'] ) !== false );
			else
			{
				$matchResult = Preg_Match ( $check['check'], $response );
				if ( $matchResult === false )
				{
					$errors[] = Array( $check, 'Regular expression is not valid' );
					continue;
				}
				else if ( $matchResult > 0 )
					$checkFound = true;
			}

			if ( $checkFound === false )
				$errors[] = Array ( $check, 'Check string not found' );
		}

		if ( Count ( $errors ) > 0 )
		{
			$errorsBody = '';
			foreach ( $errors as $key => $error )
			{
				if ( $this->recipientEmail !== null )
				{
					if ( $this->oneEmailPerError === true )
						$errorsBody .= $this->getErrorMessage ( $error ) . "\n\n";
					else
						$this->sendErrorEmail ( $this->getErrorMessage ( $error ) );
				}

				if ( $this->reportToOutput === true )
				{
					if ( $this->inCli === true )
						echo $this->getErrorMessage ( $error ) . "\n\n";
					else
						echo Nl2Br ( $this->getErrorMessage ( $error ) ) . "<br /><br />";
				}
			}

			if ( $this->recipientEmail !== null && $this->oneEmailPerError === true )
				$this->sendErrorEmail ( $errorsBody );
		}
	}

	protected function getErrorMessage ( $Error )
	{
		$errorMessage = '';
		$errorMessage = "URL: " . $Error[0]['url'] . "\n";
		$errorMessage .= "Pre check URL: " . ( $Error[0]['pre_url'] === null ? 'n/a' : $Error[0]['pre_url'] ) . "\n";
		$errorMessage .= "Check: " . $Error[0]['check'] . "\n";
		$errorMessage .= "Regex: " . ( $Error[0]['is_regex'] === true ? 'yes' : 'no' ) . "\n";
		$errorMessage .= "Error: " . $Error[1];

		return $errorMessage;
	}

	protected function sendErrorEmail ( $Body )
	{
		Mail (
			$this->recipientEmail,
			'SimplePageCheck check error',
			$Body
		);
	}
}

?>