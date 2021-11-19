//export html template for item view

let item_template = `
 <div class="item list-group-item list-group-item-action  mb-1" data-index="">
    <div class="d-flex w-100 justify-content-between">
	<div>
      <h5 class="mb-1 "><a href="" class="item-name" target="_blank"></a></h5>
      <small class="item-date"></small>
	  <p class="mb-1 item-desc"></p>
    </div>
	
	<div class="dropdown">
		  <button class="btn btn-secondary dropdown-toggle mr-5" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
			Actions
		  </button>
		  <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
			<a class="dropdown-item done-btn" href="#">Done it!</a>
			<a class="dropdown-item delete-btn" href="#">Delete</a>
		  </div>
	</div>
    
	</div>
    
</div>	
`;

export default item_template;
