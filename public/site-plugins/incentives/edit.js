$(document).ready(function () {
	$('#form-edit-incentives').validate({
	  rules: {
		driver: {
		  required: true
		},
		amount: {
		  required: true,
		  number: true,
		  min: 0.01
		},
		operation: {
		  required: true
		},
		reason: {
		  required: true,
		  minlength: 5
		}
	  },
	  messages: {
		driver: {
		  required: "The driver field is required."
		},
		amount: {
		  required: "Please enter the incentive amount.",
		  number: "Please enter a valid number.",
		  min: "The amount must be greater than 0."
		},
		operation: {
		  required: "Please select an operation (Add/Sub)."
		},
		reason: {
		  required: "Please provide a reason for the incentive.",
		  minlength: "The reason must be at least 5 characters long."
		}
	  },
	  errorElement: 'span',
	  errorPlacement: function (error, element) {
		error.addClass('text-danger');
		if (element.hasClass('select2') || element.next('.select2-container').length > 0) {
		  error.insertAfter(element.next('.select2-container'));
		} else {
		  error.insertAfter(element);
		}
	  },
	  submitHandler: function (form) {
		$('.backend-error').remove();
		form.submit();
	  }
	});

	$('#incentives-edit').on('click', function (e) {
	  if ($('#form-edit-incentives').valid()) {
		let formData = new FormData($('#form-edit-incentives')[0]);
		updateIncentive(formData);
	  }
	});

	function updateIncentive(formData) {
	  $.ajax({
		url: `${baseUrl}/incentives/${walletId}`,
		type: 'POST',
		data: formData,
		contentType: false,
		processData: false,
		dataType: 'json',
		beforeSend: function () {
		  $('#incentives-edit').prop('disabled', true);
		},
		success: function (response) {
		  if (response.hasOwnProperty('errors')) {
			$('.backend-error').remove();
			$.each(response.errors, function (key, value) {
			  let errorMessage = `<span class="backend-error text-danger">${value[0]}</span>`;
			  let element = $(`[name='${key}']`);
			  if (element.hasClass('select2-hidden-accessible')) {
				element.next('.select2-container').after(errorMessage);
			  } else {
				element.after(errorMessage);
			  }
			});
		  } else if (response.status === 200) {
			toast(response.message, 'success');
			$('#form-edit-incentives')[0].reset();
			setTimeout(() => {
			  window.location.href = `${baseUrl}/incentives`;
			}, 2000);
		  } else {
			toast('Something went wrong.', 'error');
		  }
		},
		error: function () {
		  toast('An error occurred. Please try again.', 'error');
		},
		complete: function () {
		  $('#incentives-edit').prop('disabled', false);
		}
	  });
	}

	$('.select2').select2({
	  placeholder: 'Select an option',
	  allowClear: true
	});

	$('#driver, #operation').on('change', function () {
	  $(this).next('.backend-error').remove();
	});

	$('#amount, #reason').on('input', function () {
	  $(this).next('.backend-error').remove();
	});
	
	
	// Select2 - Driver 
    $('#driver').select2({
        placeholder: 'Select Partner',
        allowClear: true,
        minimumInputLength: 1,
        language: {
            inputTooShort: function () {
                return 'Please enter 1 or more characters';
            },
            searching: function () {
                return 'Searching...'; 
            },
            noResults: function () {
                return 'No data found'; 
            }
        },
        ajax: {
            url: baseUrl + "/incentives/fetch/driver",
            type: "GET",
            delay: 200,
            dataType: "json",
            data: function (params) {
                return {
                    search: params.term
                };
            },
            processResults: function (response) {
                return {
                    results: response,
                };
            },
            cache: true,
        }
    });
	
});