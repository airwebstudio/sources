<?php
namespace App\Tasks;

class Tasks {//class for access to Tasks
		static $tasks = Array();
		static $last_index = 0;
		
		static function all():array { //getting tasks as Task array
			self::load();
			return self::$tasks;
		}
		
		static function all_as_array():array { //getting tasks as array
			self::load();
			$tasks = array();
			
			foreach (self::$tasks as $task) {
					$tasks[] = $task->asArray();
			}
						
			return array_reverse($tasks);
		}
		
		static function count():int { //get tasks count
				return sizeof(self::$tasks);
		}
		
		static function add(string $name, string $task) { //add new task
				self::load();
				$index = self::$last_index+1;
				self::$tasks[$index] = new Task($name, $task, $index);	
				
				self::$last_index++;
				self::save();
								
		}
		
		static function get(int $index):Task { //get task by id
				self::load();
				if (!isset(self::$tasks[$index])) {
					throw new \Exception('Not found', 404); 
				}
				return self::$tasks[$index];
		}
		
		static function delete(int $index) { //delete task
			self::load();
			unset(self::$tasks[$index]);
			self::save();
		}
		
		static function load():bool { //load tasks from session
			if(session_status() !== PHP_SESSION_ACTIVE) session_start();
			
			if (!isset($_SESSION['_tasks']) || !isset($_SESSION['_last_index']))
				return false;
			
			self::$tasks = $_SESSION['_tasks'];
			self::$last_index = $_SESSION['_last_index'];
			
			return true;
		}
		
		static function save() { //save tasks to session
			if(session_status() !== PHP_SESSION_ACTIVE) session_start();
			
			$_SESSION['_tasks'] = self::$tasks;
			$_SESSION['_last_index'] = self::$last_index;
			
			//var_dump($_SESSION['_tasks']);
		}
	
}
?>