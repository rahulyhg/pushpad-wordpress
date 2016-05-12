<?php
/*
 * Plugin Name: Pushpad - Web Push Notifications
 * Plugin URI: https://pushpad.xyz/docs/wordpress
 * Description: Real push notifications for your website. Uses the W3C Push API for Chrome and Firefox and supports Safari.
 * Version: 1.0.0
 * Author: Pushpad
 * Author URI: https://pushpad.xyz
 * Text Domain: pushpad
 */

register_deactivation_hook( __FILE__, 'pushpad_deactivate_plugin' );
include plugin_dir_path( __FILE__ ) . '/admin/pushpad-admin.php';
include plugin_dir_path( __FILE__ ) . '/admin/pushpad-settings.php';
include plugin_dir_path( __FILE__ ) . '/includes/widget.php';
include plugin_dir_path( __FILE__ ) . '/includes/shortcode.php';
include plugin_dir_path( __FILE__ ) . '/includes/metabox.php';
require_once plugin_dir_path( __FILE__ ) . '/pushpad/pushpad.php';
require_once plugin_dir_path( __FILE__ ) . '/pushpad/notification.php';

function pushpad_activate_plugin() {
	exit ( wp_redirect ( admin_url ( 'admin.php?page=pushpad-admin' ) ) );
}
add_action ( 'activated_plugin', 'pushpad_activate_plugin' );

function pushpad_deactivate_plugin() {
	
}

function pushpad_admin_pages() {
	add_menu_page ( 'Pushpad', 'Pushpad', 'manage_options', 'pushpad-admin', 'pushpad_admin' );
	add_submenu_page ( 'pushpad-admin', 'Settings', 'Settings', 'manage_options', 'pushpad-settings', 'pushpad_settings' );
}
add_action ( 'admin_menu', 'pushpad_admin_pages' );

function pushpad_add_wp_head() {
	$pushpad_settings = get_option ( 'pushpad_settings', array () );
	if ( !isset($pushpad_settings ["api"]) || $pushpad_settings ["api"] != 'custom' ) return;
	echo '<link rel="manifest" href="' . plugins_url ( 'manifest.json', __FILE__ ) . '">';
?>

<script>
	(function(p,u,s,h,x){p.pushpad=p.pushpad||function(){(p.pushpad.q=p.pushpad.q||[]).push(arguments)};h=u.getElementsByTagName('head')[0];x=u.createElement('script');x.async=1;x.src=s;h.appendChild(x);})(window,document,'https://pushpad.xyz/pushpad.js');

<?php
	echo "pushpad('init', '" . $pushpad_settings ["project_id"] . "');";
	if ( $pushpad_settings ["subscribe_on_load"] ) {
		echo "pushpad('subscribe');";
	}
?>

	jQuery(function () {
		var updateButton = function (isSubscribed) {
			jQuery('button.pushpad-button').each(function () {
				var btn = jQuery(this);
				if (isSubscribed) {
					btn.html(btn.data('unsubscribe-text'));
					btn.removeClass('unsubscribed').addClass('subscribed');
				} else {
					btn.html(btn.data('subscribe-text'));
					btn.removeClass('subscribed').addClass('unsubscribed');
				}
			});
		};
		pushpad('status', updateButton);
		jQuery(".pushpad-button").on("click", function(e) {
			e.preventDefault();
			if (jQuery(this).hasClass('subscribed')) {
				pushpad('unsubscribe', function () { updateButton(false); });
			} else {
				pushpad('subscribe', function () { updateButton(true); });
			}
		});
	});
</script>

<?php
}
add_action ( 'wp_head', 'pushpad_add_wp_head' );

function pushpad_script() {
	wp_enqueue_script ( 'pushpad-script', plugins_url ( '/js/pushpad-admin.js', __FILE__ ) );
}
add_action ( 'admin_enqueue_scripts', 'pushpad_script' );

function pushpad_frontend_style() {
	wp_enqueue_style ( 'style', plugins_url ( '/css/style.css', __FILE__ ) );
}
add_action ( 'wp_enqueue_scripts', 'pushpad_frontend_style' );