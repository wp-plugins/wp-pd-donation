jQuery(function($) {

	var ppForm = $("#ppform");

	var donateForm = $("#donate-form");

	var email_pattern = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;

	var year_pattern = /^([1-9][0-9]{3})$/;
	
	var active_jqxhr = null;

	var contributions = {
		1: {
			'amount': 25,
			'name': '$25 Member'
		},
		2: {
			'amount': 50,
			'name': '$50 Representative\'s Club'
		},
		3: {
			'amount': 100,
			'name': '$100 Senator\'s Club'
		},
		4: {
			'amount': 250,
			'name': '$250 Governor\'s Club'
		},
		5: {
			'amount': 500,
			'name': '$500 President\'s Club'
		},
		6: {
			'amount': 0,
			'name': 'Annual recurring payroll deduction'
		}

	};

	var queryStringAppend = function( url, obj ) {

		if( url.length == 0 ) {
			return '';
		}

		if( obj.length == 0 ) {
			return '';
		}

		var index = url.indexOf('?');
		var separator = "";
		var new_url = url;

		if( index != -1 ) {
			separator = "&";
		} else {
			separator = "?";
		}

		for( key in obj ) {
			new_url += separator + key + "=" + obj[key];			

			if( separator == "?" ) {
				separator = "&";
			}
		}

		return new_url;
	}


	var loaded_contribution = $("input[name='contribution']:checked", donateForm).val();

	if( loaded_contribution == 6 ) {
		$('.deduction-amount, .donate-form-note').fadeIn(200);
	}

	$("input[name='contribution']", donateForm).click(function() {
		var value = parseInt($(this).val());

		if( value == 6 ) {
			$(this).parent().find('.deduction-amount').fadeIn(200);
			$(this).parent().find('.donate-form-note').fadeIn(200);
		} else {
			$(this).parents('.donate-form-field').find('.deduction-amount').css("display", "none");
			$(this).parents('.donate-form-field').find('.donate-form-note').css("display", "none");
		}
	});

	
	//$("#contribution-member", donateForm).attr("checked", "checked");


	$("form", donateForm).submit(function(e) {
		var form = $(this),
			fields = {
				year: 				$("#membership_year", form),
				contribution: 		$("input[name='contribution']:checked", form),
				deduction_amount: 	$("#deduction-amount", form),
				name: 				$("#donor_name", form),
				employer: 			$("#donor_employer", form),
				occupation: 		$("#donor_occupation", form),
				system_name: 		$("#donor_system_name", form),
				home_address: 		$("#donor_home_address", form),
				city: 				$("#donor_city", form),
				zipcode: 			$("#donor_zipcode", form),
				state: 				$("#donor_state", form),
				email: 				$("#donor_email", form)
			},
			values = {},
			error = false;
			
		if( active_jqxhr != null ) {
			return;
		}


		// reset classses
		$("input", donateForm).removeClass("field-error");
		$(".field-error-message", donateForm).css("display", "none");
		$("#donate-form-feedback").css("display", "none");
		$("#donate-form-feedback .form-message").removeClass("error");

		// trim every fields
		$.each( fields, function(index, value) {
			values[index] = $.trim(value.val());
		});

		
		if( ! email_pattern.test(values.email) ) {
			error = true;

			fields.email.addClass("field-error");

			fields.email.next('.field-error-message').html('Enter a valid email address').css("display","block");
		}

		if( ! year_pattern.test( values.year ) ) {
			error = true;

			fields.year.addClass("field-error");

			fields.year.next('.field-error-message').html('Enter a valid year').css("display","block");
		}

		if( ! values.contribution ) {
			$("#contribution-member", donateForm).attr("checked", "checked");
		}
		
		console.log(values.deduction_amount);

		if( values.contribution == 6 && ( !$.isNumeric(values.deduction_amount) || values.deduction_amount <= 0 ) ) {

			error = true;

			fields.deduction_amount.addClass("field-error");

			fields.deduction_amount.next('.field-error-message').html('Enter a valid amount').css("display","block");
		}


		if( values.name.length == 0 ) {
			error = true;

			fields.name.addClass("field-error");	

			fields.name.next('.field-error-message').html('This is a required field.').css("display","block");
		}

		if( values.employer.length == 0 ) {
			error = true;

			fields.employer.addClass("field-error");	

			fields.employer.next('.field-error-message').html('This is a required field.').css("display","block");
		}

		if( values.occupation.length == 0 ) {
			error = true;

			fields.occupation.addClass("field-error");	

			fields.occupation.next('.field-error-message').html('This is a required field.').css("display","block");
		}

		if( values.system_name.length == 0 ) {
			error = true;

			fields.system_name.addClass("field-error");	

			fields.system_name.next('.field-error-message').html('This is a required field.').css("display","block");
		}

		if( values.home_address.length == 0 ) {
			error = true;

			fields.home_address.addClass("field-error");	

			fields.home_address.next('.field-error-message').html('This is a required field.').css("display","block");
		}

		if( values.city.length == 0 ) {
			error = true;

			fields.city.addClass("field-error");	

			fields.city.next('.field-error-message').html('This is a required field.').css("display","block");
		}

		if( values.zipcode.length == 0 ) {
			error = true;

			fields.zipcode.addClass("field-error");	

			fields.zipcode.next('.field-error-message').html('This is a required field.').css("display","block");
		}

		if( values.state.length == 0 ) {
			error = true;

			fields.state.addClass("field-error");	

			fields.state.next('.field-error-message').html('This is a required field.').css("display","block");
		}


		if( error ) {
			$(".formfeedback", form)
		} else {
			var donation_amount = 0;

			var donation_name = contributions[values.contribution].name;

			if( values.contribution == 6 ) {
				donation_amount = values.deduction_amount;
			} else {
				donation_amount = contributions[values.contribution].amount;
			}

			ppForm.find("input[name='amount']").val(donation_amount);
			ppForm.find("input[name='item_number']").val(donation_name);

			values.action = "register_pac_donor";
			values._donate_form_nonce = donateForm.find("#_donate_form_nonce").val();

			active_jqxhr = $.ajax({
				url: GEMCPAC.ajaxurl,
				type: "POST",
				data: values,
				dataType: "json",
				beforeSend: function() {
					donateForm.find(".form-icon-loading").css("display", "inline-block");
					//donateForm.find("input[type='submit']").addClass("inactive");
					donateForm.find("input[type='submit']").attr("disabled", "disabled");
				}
			}).done(function( response, status ) {

				var return_url = ppForm.find("input[name='return']").val();

				var custom_param  = '';

				if( response.error == false ) {

					//return_url = queryStringAppend(return_url, '_nonce', response.d_id);
					/*
					return_url = queryStringAppend(return_url, {
						'_nonce': response._nonce,
						'd_id': response.d_id
					}); 
					ppForm.find("input[name='return']").val(return_url);
					*/
					//custom_param = response.d_id + "_" + response._nonce;
					ppForm.find("input[name='custom']").val(response.d_id);

					ppForm.submit();

				} else {
					$("#donate-form-feedback").fadeIn(200);
					$("#donate-form-feedback .form-message").addClass("error").html(response.message);
					donateForm.find("input[type='submit']").removeAttr("disabled");
				}

			}).fail(function() {

				$("#donate-form-feedback").fadeIn(200);
				$("#donate-form-feedback .form-message").addClass("error").html("Failed to process the form submission. Please try again.");
				donateForm.find("input[type='submit']").removeAttr("disabled");

			}).always(function() {
				donateForm.find(".form-icon-loading").css("display", "none");
				//donateForm.find("input[type='submit']").removeClass("inactive");
				
				active_jqxhr = null;
			});
			

		}

		

		e.preventDefault();

	});

});