<?php
namespace App\Tasks;

class Task { //class for one task
	
		private $name;
		private $task;
		private $index;
		private $status = '';
	
		public function __construct($name, $task, $index) {
			$this->name = $name;
			$this->task = $task;
			$this->index = $index;

		}
		
		public function asArray():array
		{
			return [
				'name' => $this->name,
				'task'  => $this->task,
				'status' => $this->status,
				'index' => $this->index
			];
		}
		
		public function done() {
				$this->status = 'done';
		}
		
		public function getName(): string {
				return $this->name;
		}
		
		public function getTask(): string {
				return $this->task;
		}
		
		public function getIndex(): int {
				return $this->index;
		}
		
}

?>