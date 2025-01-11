$(document).ready(function() {
	// Popup window
	const popup = $(".popup_container");
	// Popup that the user can interact with
	const popupAvailable = $(".popup_available");
	// Popup that the user can not interact with
	const popupUnavailable = $(".popup_unavailable");
	// Packets
	var gid = 0;

	// Rows in packets table
	$(".selectible tr[data-gid]").each(function() {
		$(this).on("click", function() {
			var packet_name = $(this).data("packet");
			gid = $(this).data("gid");

			$(".popup_show_packet").text(packet_name);

			popup.addClass("show");
			popupAvailable.addClass("show_popup");
		})
	})

	// Accept button
	$(document).on("click", ".accept_button", function() {
		var dataSend = "gid=" + gid;

		$.post("dynamic/packet_transition.php", dataSend)
		.done(function(data) {
			data = JSON.parse(data);

			if (data['check'] == "false") {
				popup.addClass("popup_container_active");
				popupAvailable.removeClass("show_popup");
				popupUnavailable.addClass("show_popup");
			} else {
				location.href = "/";
			}
		})
	})

	// Cancel button
	$(document).on("click", ".cancel_button", function() {
		popup.removeClass("show");
		popupUnavailable.removeClass("show_popup");
	})
})