<?php

namespace BRS\PerformanceDiff;

$executor = new Executor(
	'Testing finding a Key by value using foreach and array_search.',
	100, Executor::TARE | Executor::PROGRESS);

$executor->setPrepCallback(function($executor) {
	$executor->setPayload(range(10000,1000000));
});

$executor->setTareFunction(function($payload) {
	$find = rand(0, 1000000);
	return null;
});

$executor->setRerun(1);





$executor->addTest(new Test(
	'Search', function($payload) {
		$find = rand(0, 1000000);
		return array_search($find, $payload);
	}
));

$executor->addTest(new Test(
	'FlipIsSet', function($payload) {
		$find = rand(0, 1000000);
		$payload = array_flip($payload);
		return isset($payload[$find]) ? $payload[$find] : null;
	}
));

$executor->addTest(new Test(
	'ForEach', function($payload) {
		$find = rand(0, 1000000);
		foreach($payload as $key => $value) {
			if($value == $find) {
				return $key;
			}
		}
		return '';
	}
));

$executor->execute();

$executor->log(basename(__FILE__).'.'.time());