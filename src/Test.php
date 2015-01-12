<?php

namespace BRS\PerformanceDiff;
use BRS\PerformanceDiff\Executor;

class Test {
	
	private $name          = '';
	private $test_callback = NULL;
	
	private $results = array();
	private $tare = NULL;
	
	public function __construct($name, $test_callback) {
		$this->name = $name;
		$this->test_callback = $test_callback;
	}
	
	public function getResults($reset = FALSE) {
		$results = $this->results;
		
		if(!empty($reset)) {
			$this->results = array();
		}
		
		return $results;
	}
	
	public function getName() {
		return $this->name;
	}
	
	public function setTare(Test $tare) {
		if(!($this->tare instanceof Test)) { 
			$this->tare = $tare;
			return TRUE;
		}
		return FALSE;
	}
	
	function updateProgress($total, $current, $force_last_lines = NULL) {
		static $last_lines = 0;
		
		if(!is_null($force_last_lines)) {
			$last_lines = (int) $force_last_lines;
		}
		
		$term_width = exec('tput cols', $toss, $status);
		if($status) {
			$term_width = 64; // Arbitrary fall-back term width.
		}
		
		$p_done = $current/$total;
		
		$message = str_pad(
			str_repeat('=',max(0, floor(($term_width-6)*$p_done)-1)) . (($total<=$current) ? "=" : ">"),
			$term_width-6
		).str_pad(round($p_done*100, 1) . "%", 6, ' ', STR_PAD_LEFT);
		
		$line_count = 0;
		foreach(explode("\n", $message) as $line) {
			$line_count += count(str_split($line, $term_width));
		}
		
		// Erasure MAGIC: Clear as many lines as the last output had.
		for($i = 0; $i < $last_lines; $i++) {
			echo "\r\033[K\033[1A\r\033[K\r";
		}
		
		$last_lines = $line_count;
		
		echo $message."\n";
	}
	
	public function execute(Executor $executor) {
		
		echo "Starting ".$this->name.":\n";
		
		$full_status = TRUE;
		
		$should_progress = $executor->checkFlag(Executor::PROGRESS);
		
		$iterations = $executor->getIterations();
		$rerun      = $executor->getRerun();
		$payload    = $executor->getPayload();
		
		$callabck = $this->test_callback;
		
		$wall_start = microtime(TRUE);
		
		$results = array();
		
		for($r = 0; $r < $rerun; $r++) {
			
			echo "Executing Rerun ".($r + 1)." of $rerun for {$this->name}\n";
			
			try {
				
				
				if($should_progress) {
					$start = microtime(TRUE);
					$this->__execWithProgress($callabck, $iterations, $payload);
					$stop  = microtime(TRUE);
				} else {
					$start = microtime(TRUE);
					$this->__execWithoutProgress($callabck, $iterations, $payload);
					$stop  = microtime(TRUE);
				}
				$status = TRUE;
			} catch (\Exception $e) {
				$stop   = microtime(TRUE);
				$status = $full_status = FALSE;
			}
			
			$this->updateProgress($iterations, $iterations);
			
			$results['runs'][] = array(
				'start' => $start,
				'stop'  => $stop,
				'wall'  => ($stop - $start),
				'iterations' => $iterations,
				'status' => $status,
			);
			
			echo "Rerun ".($r+1)." of $rerun for {$this->name} completed ".($status?'':'un')."successfully in ".round(($stop - $start),2)." seconds.\n";
		}
		
		$wall_stop  = microtime(TRUE);
		
		if($this->tare instanceof Test) {
			$this->tare->execute($executor);
			$results['tare'] = $this->tare->getResults();
		}
		
		$this->results[] = $results;
		
		echo "Tests for {$this->name} completed ".($full_status?'':'un')."successfully in ".round(($wall_stop - $wall_start),2)." seconds.\n";
		
		return $status;
	}
	
	private function __execWithProgress($callback, $iterations, $payload = NULL) {
		
		$iteration_chunk_size      = floor($iterations/100);
		$iteration_chunk_remainder = $iterations % 100;
		
		for($p = 0; $p < 100; $p++) {
			
			$this->updateProgress($iterations, $iteration_chunk_size * $p);
			
			for($i = $iteration_chunk_size; $i > 0; $i--) {
				$callback($payload);
			}
			
		}
		
		$this->updateProgress($iterations, $iteration_chunk_size * $p);
		
		for($i = $iteration_chunk_remainder; $i > 0; $i--) {
			$callback($payload);
		}
		
	}
	
	private function __execWithoutProgress($callback, $iterations, $payload = NULL) {
		for($i = $iterations; $i > 0; $i--) {
			$callback($payload);
		}
	}
	
}