<?php

namespace BRS\PerformanceDiff;

$executor = new Executor(
	'Testing getting a char in a string using indexing vs substrings.',
	1000000, Executor::TARE | Executor::PROGRESS);

$executor->setPrepCallback(function($executor) {
	$string = '';
	$pool = '1234567890qwertyuiopasdfghjklzxcvbnm';
	while(strlen($string) > 10000) {
		// hint hint...
		$string .= $string[rand(0, strlen($pool)-1)];
	}
	$executor->setPayload(array(
		strlen($string),
		$string
	));
});

$executor->setRerun(10);



$executor->addTest(new Test(
	'Index', function($payload) {
		return $payload[1][rand(0, $payload[0]-1)];
	}
));


$executor->addTest(new Test(
	'Substr', function($payload) {
		return substr($string, rand(0, $payload[0]), 1);
	}
));



$executor->execute();

$executor->log(basename(__FILE__).'.'.time());


/*

$string = 'kjgkjgkjgkjgkjgkjgkjgkjgkjgkjgkjgkjgkjgkjhgkhgjgjgkjgjgkjgjhgkjgkjgkjgkjgkjgkjgkjgkjgkjgkjgkjgkjgkjgkjhgkhgjgjgkjgjgkjgjhgkjgkjgkjgkjgkjgkjgkjgkjgkjgkjgkjgkjgkjgkjhgkhgjgjgkjgjgkjgjhgkjgkjgkjgkjgkjgkjgkjgkjgkjgkjgkjgkjgkjgkjhgkhgjgjgkjgjgkjgjhgkjgkjgkjgkjgkjgkjgkjgkjgkjgkjgkjgkjgkjgkjhgkhgjgjgkjgjgkjgjhgkjgkjgkjgkjgkjgkjgkjgkjgkjgkjgkjgkjgkjgkjhgkhgjgjgkjgjgkjgjhgkjgkjgkjgkjgkjgkjgkjgkjgkjgkjgkjgkjgkjgkjhgkhgjgjgkjgjgkjgjhgkjgkjgkjgkjgkjgkjgkjgkjgkjgkjgkjgkjgkjgkjhgkhgjgjgkjgjgkjgjhg';

$times = (20 /* chars */ /* 3000000 /* codes *//*);







$time = microtime(TRUE);

for($i = $times; $i > 0; $i--) {
	$chr = $string[10];
}

$end = microtime(TRUE);

echo ($end - $time)." seconds total, or ".round((($end - $time) / $times)*1000000, 5)." microseconds per\n";







$time = microtime(TRUE);

for($i = $times; $i > 0; $i--) {
	$chr = substr($string, 10, 1);
}

$end = microtime(TRUE);

echo ($end - $time)." seconds total, or ".round((($end - $time) / $times)*1000000, 5)." microseconds per\n";
