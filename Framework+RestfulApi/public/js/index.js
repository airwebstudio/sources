import item_template from './item_template.js'

var _items = {
	
	all: async function() { //get all items		
		let res;
		
		await fetch(
			'\items',
			{
			method: 'GET',
			}
		).then(response => response.json())
		.then(
			data => res = _tasks_table.gen_table(data));
		
		
		return res;
	},
	
	add: async function(data) {		//add item
		let res;
		
		await fetch('\items', {			
			method: 'POST', 
			headers: { 'Content-type': 'application/x-www-form-urlencoded' },
			body: data
		}).then(response => response.json())
		.then(
			data => res = _tasks_table.gen_table(data));
				
		return res;
	},
	
	done: async function(id) { //done item
		
		await fetch(
			'\items/'+id,
			{
			method: 'PUT',
			}
		)
		
	},
	
	delete: async function(id) { //delete item
		
		await fetch(
			'\items/'+id,
			{
			method: 'DELETE',
			}
		)
		
	}
}

//generate task list
var _tasks_table = {
	gen_table: function(data) {
		
			let res = $('<div/>', {'class': 'list-group'});
			for (let ind in data) {
				let item = data[ind];
				
				let out = $(item_template);
				out.find('.item-name').text(item.name);
				out.find('.item-name').prop('href', '\items/'+item.index);
				out.find('.item-desc').text(item.task);
				
				out.attr('data-index', item.index);
				
				if (item.status == 'done') {
					out.addClass('done text-white bg-dark');		
				}
				
				res.append(out);	
			}
			//console.log(res.replaceWith());
			return res;
	}
	
}

$(document).ready(function() {
	_items.all().then(r => $('#items').html(r));
	
	
	//global events
	$('#add_item').on('submit', function() {
		_items.add($(this).serialize()).then(r => $('#items').html(r));
		$(this).find('input[type=text], textarea').val('');
		return false;
		
	});
	
	$('#items').on('click', '.done-btn', function(){ //click done
		let item = $(this).closest('.item');
		let id = item.data('index');
		_items.done(id).then(r=>item.addClass('done text-white bg-dark'));
		
		$(this).closest('.dropdown-menu').removeClass('show');
		
		return false;
		
	});
	
	$('#items').on('click', '.delete-btn', function(){ //click delete
		let item = $(this).closest('.item');
		let id = item.data('index');
		_items.delete(id).then(r=>item.remove());
		
		$(this).closest('.dropdown-menu').removeClass('show');
		
		return false;
		
	});
});