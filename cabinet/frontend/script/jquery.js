$(document).ready(function() {
	// Sidebar
	const sidebar = $(".sidebar");
	// Dark background
	const background = $(".background");
	// Burger (button to show mobile sidebar)
	const burger = $(".burger");
	// Window reference
	var $window = $(window);

	// Sidebar parts
	const sidebar_item_text = $(".sidebar_item_text");
	const logo = $(".logo");
	const sidebar_links = $(".sidebar_links");

	function check_width() {
		var windowSize = $window.width();

		if (windowSize > 1200) {
			// Darkering background
			sidebar.mouseenter(function() {
				background.addClass("background-active-sidebar");
				sidebar_item_text.addClass("sidebar_show");
				logo.addClass("sidebar_show");
				sidebar_links.addClass("sidebar_links_show");
			});
			sidebar.mouseleave(function() {
				background.removeClass("background-active-sidebar");
				sidebar_item_text.removeClass("sidebar_show");
				logo.removeClass("sidebar_show");
				sidebar_links.removeClass("sidebar_links_show");
			});
		} else {
			background.on("click", function() {
				background.removeClass("background-active-sidebar");
				sidebar.removeClass("mobile_sidebar_show");
			})

			burger.on("click", function() {
				background.addClass("background-active-sidebar");
				sidebar.addClass("mobile_sidebar_show");
			})

		}
	}

	// Dynamically check width to correct the page display
	check_width();
	$(window).resize(check_width);
})