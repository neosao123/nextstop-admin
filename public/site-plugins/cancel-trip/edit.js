$(function () {
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf_token"]').attr("content"),
        },
    });

    // Open modal on action button click
    $("#admin-driver-action").on("click", function () {
        const driverId = $("#driver_id").val();
        const tripId = $("#trip_id").val();
        const status = $("#admin_driver_action").val();
        const tripTotalAmount = $("#trip_total_amount").val();
        const driverPenaltyPer = $("#driver_penalty_per").val();
        const penaltyAmount = $("#driver_penalty").val();

        if (!status) {
            toast("Please select an action.", "error");
            return;
        }

        $("#trip_final_amount").val(tripTotalAmount);
        $("#driver_penalty_in_per").val(driverPenaltyPer);
        $("#penalty_amount").val(penaltyAmount);
		
        $("#documentModal").modal("show");
    });

    // Calculate penalty amount on percentage change
    $("#driver_penalty_in_per").on("input", function () {
        const percentage = $(this).val();
        const totalAmount = $("#trip_final_amount").val();
        if (percentage && totalAmount) {
            const penaltyAmount = (totalAmount * percentage) / 100;
            $("#penalty_amount").val(penaltyAmount.toFixed(2));
        } else {
            $("#penalty_amount").val("");
        }
    });

    // Submit form with confirmation
    $("#penalty_submit").on("click", function () {
        const driverId = $("#driver_id").val();
        const tripId = $("#trip_id").val();
        const status = $("#admin_driver_action").val();
        const driverPenaltyPer = $("#driver_penalty_in_per").val();
        const penaltyAmount = $("#penalty_amount").val();

        if (!driverPenaltyPer || !penaltyAmount) {
            toast("Please fill all required fields.", "error");
            return;
        }

        if (driverPenaltyPer < 1 || driverPenaltyPer > 100) {
            toast("Penalty percentage must be between 1 and 100.", "error");
            return;
        }

        // Confirm before making AJAX call
        $.confirm({
            icon: "fa fa-warning",
            title: "Confirm!",
            content: "Are you sure you want to apply penalty charges to the driver?",
            theme: "modern",
            draggable: false,
            type: "red",
            typeAnimated: true,
            buttons: {
                confirm: function () {
                    $.ajax({
                        url: baseUrl + "/cancel-trips/driver/penalty",
                        type: "POST",
                        data: {
                            driverId: driverId,
                            tripId: tripId,
                            status: status,
                            driverPenaltyPer: driverPenaltyPer,
                            penaltyAmount: penaltyAmount
                        },
                        success: function (response) {
                            if (response.success) {
                                toast("Driver penalty recorded successfully", "success");
                                $("#documentModal").modal("hide");

                                setTimeout(function () {
                                    location.reload();
                                }, 2000);
                            } else {
                                toast(response.error || "An error occurred while recording the penalty.", "error");
                            }
                        },
                        error: function (xhr) {
                            const errorMessage = xhr.responseJSON && xhr.responseJSON.message
                                ? xhr.responseJSON.message
                                : "An error occurred while processing your request.";
                            toast(errorMessage, "error");
                        }
                    });
                },
                cancel: function () {
                    // Optional cancel action
                }
            }
        });
    });
	
	
	// Open modal on action button click
    $("#admin-customer-action").on("click", function () {
        const driverId = $("#driver_id").val();
        const tripId = $("#trip_id").val();
        const status = $("#admin_customer_action").val();
        const tripTotalAmount = $("#trip_total_amount").val();
        const customerPenaltyPer = $("#customer_penalty_per").val();
        const penaltyAmount = $("#customer_penalty").val();

        if (!status) {
            toast("Please select an action.", "error");
            return;
        }

        $("#trip_customer_final_amount").val(tripTotalAmount);
        $("#customer_penalty_in_per").val(customerPenaltyPer);
        $("#customer_penalty_amount").val(penaltyAmount);
		
        $("#customerModal").modal("show");
    });
	
	 // Calculate penalty amount on percentage change
    $("#customer_penalty_in_per").on("input", function () {
        const percentage = $(this).val();
        const totalAmount = $("#trip_customer_final_amount").val();
        if (percentage && totalAmount) {
            const penaltyAmount = (totalAmount * percentage) / 100;
            $("#customer_penalty_amount").val(penaltyAmount.toFixed(2));
        } else {
            $("#customer_penalty_amount").val("");
        }
    });
	
	// Submit form with confirmation
    $("#customer_refund").on("click", function () {
        const customerId = $("#customer_id").val();
        const tripId = $("#trip_id").val();
        const status = $("#admin_customer_action").val();
        const customerPenaltyPer = $("#customer_penalty_in_per").val();
        const penaltyAmount = $("#customer_penalty_amount").val();

        if (!customerPenaltyPer || !penaltyAmount) {
            toast("Please fill all required fields.", "error");
            return;
        }

        if (customerPenaltyPer < 1 || customerPenaltyPer > 100) {
            toast("Penalty percentage must be between 1 and 100.", "error");
            return;
        }

        // Confirm before making AJAX call
        $.confirm({
            icon: "fa fa-warning",
            title: "Confirm!",
            content: "Are you sure you want to refund charges to the customer?",
            theme: "modern",
            draggable: false,
            type: "red",
            typeAnimated: true,
            buttons: {
                confirm: function () {
                    $.ajax({
                        url: baseUrl + "/cancel-trips/customer/refund",
                        type: "POST",
                        data: {
                            customerId: customerId,
                            tripId: tripId,
                            status: status,
                            customerPenaltyPer: customerPenaltyPer,
                            penaltyAmount: penaltyAmount
                        },
                        success: function (response) {
                            if (response.success) {
                                toast("Customer penalty recorded successfully", "success");
                                $("#customerModal").modal("hide");

                                setTimeout(function () {
                                    location.reload();
                                }, 2000);
                            } else {
                                toast(response.error || "An error occurred while recording the refund.", "error");
                            }
                        },
                        error: function (xhr) {
                            const errorMessage = xhr.responseJSON && xhr.responseJSON.message
                                ? xhr.responseJSON.message
                                : "An error occurred while processing your request.";
                            toast(errorMessage, "error");
                        }
                    });
                },
                cancel: function () {
                    // Optional cancel action
                }
            }
        });
    });
	
	
});

