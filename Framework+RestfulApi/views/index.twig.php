{% extends "base.twig.php" %}

{%block body%}
<div class="card card-default w-50 mx-auto pt-md-3 mb-3">
	<div class="card-header">Add new task</div>
  <div class="card-body">
		
		<form id="add_item" class="">

			<div class="form-group">
				<label for="task_name">Task name</label>
				<input type="text" id="task_name" class="form-control" name="name"  placeholder="Enter task name" required="required">

		  </div>
		  
		  <div class="form-group">
				<label for="task_desc">Task descrpition</label>
				<textarea  id="task_desc" class="form-control" name="task" placeholder="Enter task description" required="required"></textarea>

		  </div>
			

		<button type="submit" class="btn btn-success">Add a task</button>
		</form>
		
	</div>
	
</div>


<div id="items"></div>
{%endblock%}