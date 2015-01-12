<?php

namespace BRS\PerformanceDiff;
use BRS\PerformanceDiff\Test;

class Executor {
	
	const TARE     = 1;
	const PROGRESS = 2;
	
	private $flags = FALSE;
	
	private $name       = '';
	private $iterations = 0;
	private $iteration_rerun = 5;
	private $loops = 1;
	private $current_mod_loop = 0;
	private $current_test_index = NULL;
	
	private $payload = NULL;
	
	private $tests = array();
	
	private $executing = FALSE;
	
	function __construct($name, $iterations, $flags) {
		
		$this->name       = $name;
		$this->iterations = $iterations;
		
		$this->flags = $flags;
	}
	
	public function setRerun($times = 5) {
		if(!$this->executing) {
			$this->iteration_rerun = (int) $times;
			return TRUE;
		}
		
		return FALSE;
	}
	
	public function getRerun() {
		return (int) $this->iteration_rerun;
	}
	
	public function setPrepCallback($callback, $loops = 1) {
		if(!$this->executing && is_callable($callback)) {
			$this->loops = max((int) $loops, 1);
			$this->prep_callback = $callback;
		}
		return FALSE;
	}
	
	public function getCurrentModLoop() {
		return $this->current_mod_loop;
	}
	
	public function getIterations() {
		return (int) $this->iterations;
	}
	
	public function checkFlag($check) {
		return (bool) ($this->flags & $check) == $check;
	}
	
	public function setPayload($payload) {
		if($this->preparing) {
			$this->payloads[$this->current_mod_loop] = $payload;
			return TRUE;
		}
		return FALSE;
	}
	
	public function getPayload($for_loop = NULL) {
		if(!is_null($for_loop) || $this->executing) {
			if(is_null($for_loop)) {
				$for_loop = $this->current_mod_loop;
			}
			return isset($this->payloads[$for_loop]) ? $this->payloads[$this->current_mod_loop] : NULL;
		}
		
		return NULL;
	}
	
	public function getResults() {
		$results = array();
		
		if(!$this->executing) {
			foreach($this->tests as $test) {
				$results[] = array(
					'name' => $test->getName(),
					'results' => $test->getResults()
				);
			}
		}
		
		return (array) $results;
	}
	
	public function addTest(Test $test) {
		
		if(!$this->executing) {
			$this->tests[] = $test;
			return TRUE;
		}
		
		return FALSE;
	}
	
	public function setTareFunction($callable) {
		
		if(!$this->executing) {
			
			if(!is_callable($callable)) {
				throw new \Exception('Executor::setTareFunction expects parameter 1 to be callable.');
			}
			
			$this->tare_function = $callable;
			return TRUE;
		
		}
		
		return FALSE;
	}
	
	public function getTareFunction() {
		
		if(!is_callable($this->tare_function)) {
			$this->tare_function = function($payload){return TRUE;};
		}
		
		return $this->tare_function;
	
	}
	
	public function execute() {
		
		$return = FALSE;
		
		if(!$this->executing) {
			
			$this->executing = TRUE;
			
			try {
				
				for($mod_loop = 1; $mod_loop <= $this->loops; $mod_loop++) {
				
					$this->current_mod_loop = $mod_loop;
					
					// Prepare
					if(is_callable($this->prep_callback)) {
						$this->preparing = TRUE;
						
						$prep = $this->prep_callback;
						
						$prep($this);
						
						$this->preparing = FALSE;
					}
				
					// Execute Tests
					foreach($this->tests as $i => $test) {
				
						$this->current_test_index = $i;
				
						if($this->checkFlag(Executor::TARE)) {
							$test->setTare(new Test('Tare', $this->getTareFunction()));
						}
						
						if(!$test->execute($this)) {
							//throw new \Exception(); //?
						}
						
					}
				
				}
				
				$return = TRUE;
			
			} catch(\Exception $e) {
				print_r("Performance Tester Execution Exception Caught: ".$e->getMessage()." - Exiting\n");
			}
			
			$this->executing = FALSE;
			
		}
		
		return (bool) $return;
	}
	
}
