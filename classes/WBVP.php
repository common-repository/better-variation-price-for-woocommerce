<?php

if(! class_exists('WBVP')) :

class WBVP {

	/**
	 * @var string
	 */
	public $version;

	/**
	 * @var string
	 */
	private $plugin_url;

	/**
	 * @var string
	 */
	private $plugin_path;

	/**
	 * @var string
	 */
	private $plugin_name;

	/**
	 * @var string
	 */
	private $text_domain;

	/**
	 * @var array
	 */
	private $options;

	public function __construct() {
		$this->plugin_url = plugin_dir_url(dirname(__FILE__));
		$this->plugin_path = plugin_dir_path(dirname(__FILE__));
		$this->plugin_name = 'woo-better-variation-price';
		$this->text_domain = 'better-variation-price-for-woocommerce';
		$this->options['display'] = get_option('wbvp_display', 'min');
		$this->options['better_variation'] = get_option('wbvp_better_variation', 'yes');
		$this->options['hide_reset'] = get_option('wbvp_hide_reset', 'no');
	}

	/**
	 * Initialize the plugin
	 */
	public function initialize() {

		$this->setup_translation();
		$this->hpos_compatibility();
		$this->set_format();

		if ($this->options['display'] != 'none')
			$this->overwrite_price();

		if ($this->options['better_variation'] == 'yes')
			$this->better_variation();

		if ($this->options['hide_reset'] == 'yes')
			$this->hide_reset();

		if (is_admin())
			$this->create_option_page();

	}

	private function set_format() {
		$this->options['format'] = get_option('wbvp_format', 'From: {price}');
		if (!empty($this->options['format'])) return;
		if ($this->options['display'] == 'max') $this->options['format'] = __('Up to: {price}', 'better-variation-price-for-woocommerce');
		else $this->options['format'] = __('From: {price}', 'better-variation-price-for-woocommerce');
	}

	/**
	 * Activate Better Variation
	 */
	private function better_variation() {
		add_action('wp_enqueue_scripts', function () {
			wp_enqueue_script('wbvp', $this->plugin_url . 'assets/js/plugin.min.js', ['jquery'], false, true);
		});
	}

	/**
	 * Hide reset variation link
	 */
	private function hide_reset() {
		add_filter('woocommerce_reset_variations_link', '__return_null');
	}

	/**
	 * Show lowest price instead of price range
	 */
	private function overwrite_price() {
		add_filter('woocommerce_variable_sale_price_html', [$this, 'better_price_html'], 10, 2);
		add_filter('woocommerce_variable_price_html', [$this, 'better_price_html'], 10, 2);
	}

	public function better_price_html ($price, $product) {

		$variation_prices = $product->get_variation_prices();

		if ($this->options['display'] == 'min') :
			$lowest_regular_price = min($variation_prices['regular_price']);
			$lowest_sale_price = min($variation_prices['sale_price']);

			if (floatval($lowest_sale_price) < floatval($lowest_regular_price)) {
				$variation_id = array_search($lowest_sale_price, $variation_prices['sale_price']);
			} else {
				$variation_id = array_search($lowest_regular_price, $variation_prices['regular_price']);
			}
		elseif ($this->options['display'] == 'max') :
			$biggest_regular_price = max($variation_prices['regular_price']);
			$biggest_sale_price = max($variation_prices['sale_price']);

			if (floatval($biggest_sale_price) > floatval($biggest_regular_price)) {
				$variation_id = array_search($biggest_sale_price, $variation_prices['sale_price']);
			} else {
				$variation_id = array_search($biggest_regular_price, $variation_prices['regular_price']);
			}
		endif;

		$variation = wc_get_product($variation_id);
		$variation_price_html = $variation->get_price_html();
		$html_price = str_replace('{price}', $variation_price_html, $this->options['format']);
		return $html_price;

	}

	/**
	 * Create Option Page
	 */
	private function create_option_page() {

		add_action('admin_enqueue_scripts', function() {
			if (isset($_GET['page']) && $_GET['page'] === 'wc-settings' && isset($_GET['section']) && $_GET['section'] === 'wbvp') {
				wp_enqueue_style('wbvp-admin', $this->plugin_url . 'assets/css/admin.min.css');
				wp_enqueue_script('wbvp-admin', $this->plugin_url . 'assets/js/admin.min.js');
				wp_localize_script('wbvp-admin', 'lang', [
					'min' => __('From: {price}', 'better-variation-price-for-woocommerce'),
					'max' => __('Up to: {price}', 'better-variation-price-for-woocommerce')
				]);
			}
		});

		/* Add section to Woocommerce product tab */
		add_filter('woocommerce_get_sections_products', function ($sections) {
			$sections['wbvp'] = __('Better Variation Price', 'better-variation-price-for-woocommerce');
			return $sections;
		});

		/* Create the option page */
		add_filter('woocommerce_get_settings_products', function ($settings, $current_section) {
			if ($current_section != 'wbvp') return $settings;

			$wbvp_settings = [
				[
					'name'		=> __('Better Variation Price for Woocommerce', 'better-variation-price-for-woocommerce'),
					'id'		=> 'wbvp',
					'type'		=> 'title',
					'desc'		=> __('The following options are used to configure Better Variation Price for Woocommerce', 'better-variation-price-for-woocommerce'),
				], [
					'name'		=> __('Display type', 'better-variation-price-for-woocommerce'),
					'id'		=> 'wbvp_display',
					'type'		=> 'select',
					'options'	=> [
						'none'		=> 'Price range (Woocommerce default)',
						'min'		=> 'Lowest price',
						'max'		=> 'Highest price'
					],
					'default'	=> 'min',
				], [
					'name'		=> __('Display format', 'better-variation-price-for-woocommerce'),
					'id'		=> 'wbvp_format',
					'type'		=> 'text',
					'placeholder' => 'From: {price}',
					'desc'		=> __('Change how the price should be displayed. Use {price} where the price should be. Leave empty to select the format automatically.', 'better-variation-price-for-woocommerce')
				], [
					'name'		=> __('Update on variation change', 'better-variation-price-for-woocommerce'),
					'id'		=> 'wbvp_better_variation',
					'type'		=> 'checkbox',
					'default'	=> 'yes',
					'desc'		=> __('Change the main price when the user selects a variation.', 'better-variation-price-for-woocommerce')
				], [
					'name'		=> __('Hide Reset Variations Link', 'better-variation-price-for-woocommerce'),
					'id'		=> 'wbvp_hide_reset',
					'type'		=> 'checkbox',
					'default'	=> 'yes',
					'desc'		=> __('Hide the "clear" link that appears when you select a variation.', 'better-variation-price-for-woocommerce')
				], [
					'type' => 'sectionend',
					'id' => 'wbvp'
				]

			];
			return $wbvp_settings;

		}, 10, 2);

		/* Plugin settings shortcut */
		$plugin_main_file = $this->text_domain . '/' . $this->plugin_name . '.php';
		add_filter('plugin_action_links_' . $plugin_main_file, function ($links) {
			$action_links = array(
				'settings' => '<a href="' . admin_url('admin.php?page=wc-settings&tab=products&section=wbvp') . '" aria-label="' . __('View Better Variation Price for Woocommerce settings', 'better-variation-price-for-woocommerce') . '">' . __('Settings', 'better-variation-price-for-woocommerce') . '</a>',
			);
			return array_merge($action_links, $links);
		}, 10, 4);

	}

	/**
	 * Setup translation
	 */
	private function setup_translation() {

		add_action('init', function() {
			load_plugin_textdomain('better-variation-price-for-woocommerce', false, 'better-variation-price-for-woocommerce/languages');
		});

		add_filter('load_textdomain_mofile', function($mofile, $domain) {
			if ('better-variation-price-for-woocommerce' === $domain && false !== strpos($mofile, WP_LANG_DIR . '/plugins/')) {
				$locale = apply_filters('plugin_locale', determine_locale(), $domain);
				$mofile = $this->plugin_path . 'languages/' . $domain . '-' . $locale . '.mo';
			}
			return $mofile;
		}, 10, 2);

	}

	private function hpos_compatibility() {
		add_action('before_woocommerce_init', function(){
			if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
				\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', $this->plugin_path . $this->plugin_name . '.php', true );
			}
		});
	}

}

endif;
