$(document).ready(function() {
	// Popup window
	const popup = $(".popup_container");
	// Popup that the user can interact with
	const popupAvailable = $(".popup_available");

	// Night unlim control
	var unlimState = -1;

	$("#control_unlim").on("click", function() {
		unlimState = $(this).data("action");

		if (unlimState == 0) {
			$(".unlim_enable").css("display", "none")
			$(".unlim_disable").css("display", "inline-block")
		} else {
			$(".unlim_disable").css("display", "none")
			$(".unlim_enable").css("display", "inline-block")
		}

		popup.addClass("show");
		popupAvailable.addClass("show_popup");
	});

	// Accept button
	$(document).on("click", "#unlim_button", function() {
		var data = "unlim=" + unlimState;
		
		$.post("dynamic/unlim_control.php", data)
		.done(function() {
			location.href = location.href;
		})
	})

	// Cancel button
	$(document).on("click", ".cancel_button", function() {
		popup.removeClass("show");
	})

	// Mini-statistic in main page
	// Close things when click is outside of the elements
	$(document).on("click", function() {
		$(".dropdown_list").removeClass("show");
		$(".online_explanation_window").removeClass("show");
	})

	// Open dropdown by the button
	$(".dropdown_button").on("click", function(e) {
		e.stopPropagation();
		$(".dropdown_list").addClass("show");
	});

	// Process dropdown items
	$(".dropdown_list_item").each(function() {
		$(this).on("click", function(e) {
			e.stopPropagation();

			// Get new action name
			var actionName = $(this).text();
			// Form a query to database
			var dataSend = "action=" + $(this).data("value");

			// Do a new dropdown item as active
			$(".dropdown_button h5").html(actionName);
			$(".dropdown_item_active").removeClass("dropdown_item_active");
			$(this).addClass("dropdown_item_active");

			// Close dropdown menu
			$(".dropdown_list").removeClass("show");

			$.post("dynamic/statistic.php", dataSend)
			.done(function(dataGet) {
				var i = 0

				data = JSON.parse(dataGet);

				$(".statistic_data .dynamic_data").each(function() {
					$(this).html(data[i++]);
				})
			})
		})
	})
	
	// Online table corner hint
	$(".online_explanation").on("click", function(e) {
		e.stopPropagation();
		$(".online_explanation_window").addClass("show");
	})

	// Handle clicking on the hint itself, so that is does not close
	$(".online_explanation_window").on("click", function(e) {
		e.stopPropagation();
	})
})