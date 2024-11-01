(function($, window, document) {
	var WelightEI = function($container) {
		this.$container = $($container);
		this.$owlOng = null;

		// Initialize
		this.init();
	};

	// Show Container
	WelightEI.prototype.initContainer = function() {
		var welightContainer = this.$container;

		if (welightContainer.length && welightContainer.hasClass("hidden")) {
			welightContainer.removeClass("hidden");
		}
	};

	// Ong Carousel initialize
	WelightEI.prototype.initCarousel = function() {
		var ongCarousel = "#carousel-ongs";

		// calculate width
		var widthContainer = this.$container.closest("#order_review").width(),
			maxWidth = 250;
		var owlItems =
			widthContainer >= 250 ? Math.round(widthContainer / maxWidth) : 2;

		// simple style
		const hasSimpleOng = $(ongCarousel)
			.find("> .ong:eq(0)")
			.hasClass("style-simple-ong");

		if (hasSimpleOng) {
			// modify items quantity in container
			owlItems =
				widthContainer >= 150 ? Math.round(widthContainer / 150) : 3;

			// add class to container
			$(ongCarousel).addClass("style-simple-ong");
		}

		// Instanciate.
		this.$owlOng = $(ongCarousel).owlCarousel({
			autoHeight: true,
			lazyLoad: true,
			nav: true,
			dots: false,
			margin: 5,
			navText: [
				'<span aria-label="Anterior">‹</span>',
				'<span aria-label="Próximo">›</span>'
			],
			responsive: {
				0: {
					items: 1,
					nav: false
				},
				768: {
					items: owlItems,
					nav: true
				}
			}
		});
	};

	// Tippy
	WelightEI.prototype.initTippy = function() {
		if (typeof tippy === "function") {
			tippy(".tippify", { animation: "scale" });
		}
	};

	// Morelink
	WelightEI.prototype.moreLink = function() {
		this.$container.on("click", ".more-link > a", function() {
			var $this = $(this);
			$this
				.closest(".ong")
				.find(".ong-info")
				.addClass("active");
		});

		this.$container.on("click", ".ong-info > .close", function() {
			var $this = $(this);
			$this.closest(".ong-info").removeClass("active");
		});
	};

	// Initialize prototype
	WelightEI.prototype.init = function() {
		this.initCarousel(); // OngsCarousel.
		this.initContainer(); // Show container.
		this.initTippy(); // Tippy.js
		this.moreLink(); // More link click.
	};

	// jQuery selector function
	$.fn.welight_easy_impact = function() {
		new WelightEI(this);
		return this;
	};

	// Initialize WelightEI.
	$(document).ready(() => $(".welight-container").welight_easy_impact());
})(jQuery, window, document);
