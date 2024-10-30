<?php
/**
 * Clean up the option table after uninstalling the plugin
 */

if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

$options_to_remove = [
	'wbvp_display',
	'wbvp_format',
	'wbvp_better_variation',
	'wbvp_hide_reset'
];

foreach ( $options_to_remove as $option ) {
	delete_option($option);
	delete_site_option($option);
}
