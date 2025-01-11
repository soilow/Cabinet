$(document).ready(function() {
	// Popup window
	const popup = $(".popup_container");
	// Popup that the user can interact with
	const popupAvailable = $(".popup_available");

	$(document).on("click", ".money_exchange_button", function(e) {
		e.preventDefault();

		var form = $("div.money_exchange_container :input").filter(function(index, element) {
			return $(element).val() != '';
		}).serialize();

		form += "&get_fio=1";

		$.post("dynamic/money_exchange.php", form)
		.done(function(data) {
			data = JSON.parse(data);

			$(".money_error_warning").css("display", "none");

			if (data['code'] == "error") {
				$(".money_error_warning").css("display", "flex");
				$(".money_error_text").text(data['text']);
			} else {
				$(".span_summa").text($("#money_input").val())
				$(".span_fio").text(data['text']);

				popup.addClass("show");
				popupAvailable.addClass("show_popup");
			}
		});
	});

	// Accept button
	$(document).on("click", "#money_accept_button", function() {
		var form = $("div.money_exchange_container :input").filter(function(index, element) {
			return $(element).val() != '';
		}).serialize();

		$.post("dynamic/money_exchange.php", form)
		.done(function(data) {
			data = JSON.parse(data);

			popup.removeClass("show");

			$(".money_error_warning").css("display", "none");
			$(".money_success_warning").css("display", "none");

			if (data['code'] == "error") {
				$(".money_error_warning").css("display", "flex");
				$(".money_error_text").text(data['text']);
			} else {
				$(".money_success_warning").css("display", "flex");
				$(".money_success_text").text(data['text']);
			}
		});
	})

	// Cancel button
	$(document).on("click", ".cancel_button", function() {
		popup.removeClass("show");
	})
});