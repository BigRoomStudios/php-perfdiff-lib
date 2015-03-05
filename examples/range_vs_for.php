<?php

namespace BRS\PerformanceDiff;

$executor = new Executor(
        'Testing iteration on range() vs for loop.',
        100000000, Executor::TARE | Executor::PROGRESS);

$executor->setRerun(5);

$executor->setPayload(4800000);

$executor->addTest(new Test(
        'Range', function($payload) {
		foreach(range(1,$payload) as $i) { }
                return NULL;
        }
));


$executor->addTest(new Test(
        'For Loop', function($payload) {
		for($i = 1; $i <= $payload; $i++) { }
		return NULL;
        }
));




$executor->execute();

$executor->log(basename(__FILE__).'.'.time());


