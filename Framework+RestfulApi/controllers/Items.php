<?php
namespace Controllers;

use App\Tasks\Tasks;
use App\Tasks\Task;

class Items {
	
	
	public function index() { //main page		
		return view('index');
	}
	
	
	public function item(int $id) { //get one item
		try {			
			$item = Tasks::get($id);			
		}
		catch (\Exception $e) {
				return response('', $e->getCode());
		}		
		return view('item', array('item' => $item));
	}
	
	
	
	public function items() {	//get all intems as JSON
				
		$items = Tasks::all_as_array();
		
		return response($items, 200);
	}
	
	
	
	public function add() { //add task
		
		try {
			list($name, $task) = request()->getList(array('name', 'task')); 		
			Tasks::add($name, $task);		
			return $this->items();
		}
		catch (\Exception $e) {
				return response($e->getMessage(), 500);
		}
	}
	
	
	public function delete(int $id) { //delete task
		
		try {
			Tasks::delete($id);
		
		}
		catch (\Exception $e) {
				return response('', $e->getCode());
		}
		return response(['result' => 'ok'], 200);
	}
	
	
	public function done(int $id) { //make task done
		try {
			Tasks::get($id)->done();
		}
		catch (\Exception $e) {
				return response('', $e->getCode());
		}
		return response(['result' => 'ok'], 200	);
	}
}

?>