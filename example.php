<?php

Include ( 'SimplePageCheck.php' );

$spc = new SimplePageCheck ( 'your@email.com' );

$spc->AddCheck ( 'http://www.example.com', null, 'page by typing' );
$spc->AddCheck ( 'http://www.example.org', null, 'Section 3test.' ); // this will fail


$spc->RunChecks ();

?>