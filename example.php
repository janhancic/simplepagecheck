<?php

Include ( 'SimplePageCheck.php' );

// initialize a new SimplePageCheck object
$spc = new SimplePageCheck ();

// tell SimplePageCheck to send errors to your@email.com and to send one email with all errors
$spc->ReportToEmail ( 'your@email.com', false );

// tell SimplePageCheck to output errors to the standard output
$spc->ReportToOutput ( true );

// add some checks
$spc->AddCheck ( 'http://www.example.com', null, 'page by typing' );
$spc->AddCheck ( 'http://www.example.org', null, 'Section 3test.' ); // this will fail
$spc->AddCheck ( 'http://www.example.org', 'http://www.example.org', '/You (.*) testreached/is', true ); // this too will fail

// run all the added checks
$spc->RunChecks ();

?>