window.addEvent("domready", function() {

	$$(".tl_chosen_add_option").chosen();
	
	$$(".tl_chosen_add_option input").addEvent("keyup", function(evt) {

		if (evt.target.value === '')
		{
			return;
		}
		
		if (evt.key === 'enter' || evt.key === 'tab') {
			evt.preventDefault();
			for (let [key, option] of Object.entries(evt.target.getParent().getParent(".tl_chosen_add_option").getPrevious("select.tl_chosen_add_option").getChildren())) {
				if (option.value === evt.target.value) {
					return;
				}
			}
				
			evt.target.getParent(".tl_chosen_add_option").getPrevious("select.tl_chosen_add_option").appendHTML(
				'<option value="'+evt.target.get('value')+'" selected>'+evt.target.get('value')+'</option>'
			);
			
			$$(".tl_chosen_add_option").fireEvent('liszt:updated');
			evt.target.getParent(".tl_chosen_add_option").fireEvent('mouseleave');
			event.target.fireEvent('blur').blur();
		}
	});
	
	$$(".tl_chosen_add_option input").addEvent("keydown", function(evt) {

		Locale.define(Locale.getCurrent().name, 'Chosen', {
			'noResults': evt.target.getParent(".tl_chosen_add_option").getPrevious("select.tl_chosen_add_option").get('data-noresult')
		})

		if (evt.key === 'tab') {
			evt.preventDefault();
		}
	});

	$$(".tl_chosen_add_option input").addEvent("blur", function(evt) {
		
		Locale.define(Locale.getCurrent().name, 'Chosen', {
			'noResults': ''
		})
	});
	
	$$(".tl_chosen_add_option div").addEvent("click", function(evt) {

		//if ($(this).getElements(".no-results").length > 0) {
		if (evt.target.className == 'no-results') {
			console.log("start adding");
			for (let [key, option] of Object.entries(evt.target.getSiblings())) {
				if (option.value === evt.target.value) {
				//	return;
				}
			}
			console.log("adding");
			parent = evt.target.getParent(".tl_chosen_add_option");
				console.log(parent);
			parent.getPrevious("select.tl_chosen_add_option").appendHTML(
				'<option value="'+parent.getElements("input").get('value')+'" selected>'+parent.getElements("input").get('value')+'</option>'
			);
			
			$$(".tl_chosen_add_option").fireEvent('liszt:updated');
			evt.target.getParent(".tl_chosen_add_option").fireEvent('mouseleave');
			event.target.fireEvent('blur').blur();
		}
	});
})
