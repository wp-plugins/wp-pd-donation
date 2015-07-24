jQuery(function($) {

	var months = {
		1: "January",
		2: "February",
		3: "March",
		4: "April",
		5: "May",
		6: "June",
		7: "July",
		8: "August",
		9: "September",
		10: "October",
		11: "November",
		12: "December"
	};
	
	var donation_years = $("#donation_years");
	
	var donation_months = $("#donation_months");
	
	var default_selected_year = donation_years.find("option:selected");
	
	if( default_selected_year.length > 0 && default_selected_year.val() != 0 ) {	
		var selected = donation_years.data("selected");
		var visible_months = default_selected_year.data("months");
		
		$.each(visible_months, function(index, value) {	
			var _selected = value==selected ? 'selected="selected"' : '';
			
			donation_months.append('<option value="'+value+'" '+_selected+'>'+months[value]+'</option>');
		});
	}
	
	donation_years.change(function() {
		var value = $(this).val();
		
		var ymonths = donation_years.find("option:selected").data("months");
						
		donation_months.find("option:gt(0)").remove();
		
		if( ymonths != undefined && ymonths.length > 0 ) {
			$.each(ymonths, function(index, value) {	
				donation_months.append('<option value="'+value+'">'+months[value]+'</option>');
			});
		} else { // reset
			window.location.href = GEMCPAC.donate_index;
		}
	});
	
	donation_months.change(function(e) {
		var value = $(this).val();
		
		var redirect = GEMCPAC.donate_index;
		
		var year = donation_years.val();
		
		var month = donation_months.val();
			
		if( value > 0 ) {
			window.location.href = redirect + "&dyear="+year+"&dmonth="+month;
		} 
	});

	$(".delete-donation").click(function(e) {
		
		var answer = confirm("Are you sure you want to delete this entry?");
		if( ! answer ) {
			e.preventDefault();	
		}

	});
	
});