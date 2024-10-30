(function($) {

	// Set variables
	const productSelector = '.product.product-type-variable';
	const priceSelector = '.summary .price, .wc-block-components-product-price';

	const product = $(productSelector);
	const price = $(priceSelector);

	const defaultPrice = $(productSelector).find(priceSelector).html();
	let previousPrice = defaultPrice;

	const singleVariation = $(productSelector).find('.single_variation_wrap .single_variation');

	/**
	 * Triggered on show_variation and hide_variation
	 */
	function updatePrice(newPrice) {
		if (previousPrice === newPrice) return;
		price.fadeOut(200, function () {
			if (newPrice !== defaultPrice) {
				price.addClass('variation-price');
			} else {
				price.removeClass('variation-price');
			}
			price.html(newPrice).fadeIn(200);
			previousPrice = newPrice;
		});
	}

	/**
	 * Hide the variation div if empty
	 */
	function hideVariationIfEmpty() {
		if ($.trim(singleVariation.find('.woocommerce-variation-description').html()).length == 0 &&
			$.trim(singleVariation.find('.woocommerce-variation-availability').html()).length == 0) {
			singleVariation.stop().removeAttr('style').css('display', 'none');
		} else {
			singleVariation.stop().removeAttr('style');
		}
	}

	product.on("show_variation", function (event, variation) {
		var newPrice = $(variation.price_html).html();
		updatePrice(newPrice);

		// Hide default price
		$('.product-type-variable .single_variation_wrap .woocommerce-variation-price').css('display', 'none');

		// Hide variation container if empty
		if ($.trim(singleVariation.find('.woocommerce-variation-description').html()).length == 0 &&
			$.trim(singleVariation.find('.woocommerce-variation-availability').html()).length == 0) {
			singleVariation.stop().removeAttr('style').css('display', 'none');
		} else {
			singleVariation.stop().removeAttr('style');
		}
	});

	product.on("hide_variation", function(event) {
		updatePrice(defaultPrice);
	});

	hideVariationIfEmpty();

})(jQuery);
