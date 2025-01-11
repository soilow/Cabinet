function request_to_database(request) {
	// Loader
	const loader = $(".loader_container")

	// Loader animation start
	loader.addClass("show");

	$.post("dynamic/full_statistic.php", request)
	.done(function(data) {
		data = JSON.parse(data);

		// Pagination
		$(".pagination_container").html(data['pages']);

		// Data loading
		$("#brief-container").html(data['brief']);
		$("#detailed-container").html(data['detailed']);
	})
	// Loader animation end
	.always(function() {
		loader.removeClass("show");
	})
}

function todays_date() {
	// Module for date conversion to PHP format
	const dateFormatter = new DateFormatter();
	// Date. JS object
	const date = new Date();

	// Today's date
	let temp = `${date.getFullYear()}-${date.getMonth() + 1}-${date.getDate()}`;
	// Formatting in PHP style
	var current = dateFormatter.formatDate(temp, "Y-m-d");

	var request = "from=" + current + " 00:00:00&" + "to=" + current + " 23:59:59";

	return request;
}


// Handle date picker and pagination
$(document).ready(function() {
	// Module for date conversion to PHP format
	const dateFormatter = new DateFormatter();
	// Request to database
	var request = "";
	// Flatpickr config
	var flatpickrConfig = {
		locale: "ru",
		mode: "range",
		dateFormat: "d/m/Y",
		onChange: function(selectedDates, datestr, instance) {
			var from = dateFormatter.formatDate(selectedDates[0], "Y-m-d H:i:s");
			var to = dateFormatter.formatDate(selectedDates[1], "Y-m-d");

			// Add "23:59:59" to select all rows in the last day of selecting
			request = "from=" + from + "&" + "to=" + to + " 23:59:59";

			// Send data only after set the second date
			if (to)
				request_to_database(request);
		}
	};

	// Load today's statistic immediately after the page loads
	request_to_database(todays_date());

	// Flatpickr
	$(".date").flatpickr(flatpickrConfig);

	// Handle pagination working
	$(document).on("click", ".pagination_container a[data-page]", function() {
		var page = $(this).data("page");
		var pageRequest = "";

		// If date picker wasn't used, then pagination will be used for "todays" request
		if (request == "")
			pageRequest = todays_date() + "&page=" + page;
		else
			pageRequest = request + "&page=" + page;

		request_to_database(pageRequest);
	})
})