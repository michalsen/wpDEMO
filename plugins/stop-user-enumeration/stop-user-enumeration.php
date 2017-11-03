<?php
/*
Plugin Name: Fullworks WP VPS Security ( Stop User Enumeration )
Plugin URI: https://fullworks.net/products/vps-security/
Description: User enumeration is a technique used by hackers to get your login name if you are using permalinks. This plugin stops that.
Version: 1.3.12
Author: Fullworks Digital Ltd
Text Domain: stop-user-enumeration
Domain Path: /languages
Author URI: https://fullworks.net/
License: GPLv2 or later.
*/

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/
// Create a helper function for easy SDK access.
function sue_fs() {
	global $sue_fs;

	if ( ! isset( $sue_fs ) ) {
		// Include Freemius SDK.
		require_once dirname( __FILE__ ) . '/freemius/start.php';

		$sue_fs = fs_dynamic_init( array(
			'id'             => '1318',
			'slug'           => 'stop-user-enumeration',
			'type'           => 'plugin',
			'public_key'     => 'pk_bbbd29c5de1662b6753871351b01f',
			'is_premium'     => false,
			'has_addons'     => false,
			'has_paid_plans' => false,
			'menu'           => array(
				'slug'    => 'sue-settings-settings',
				'account' => false,
				'contact' => false,
				'parent'  => array(
					'slug' => 'options-general.php',
				),
			),
		) );
	}

	return $sue_fs;
}

// Init Freemius.
sue_fs();
// Signal that SDK was initiated.
do_action( 'sue_fs_loaded' );

class Stop_User_Enumeration_Plugin {
	private static $instance = null;
	private $plugin_path;
	private $plugin_url;
	private $settings_link;

	/**
	 * Initializes the plugin by setting localization, hooks, filters, and administrative functions.
	 */
	private function __construct() {
		$this->plugin_path   = plugin_dir_path( __FILE__ );
		$this->plugin_url    = plugin_dir_url( __FILE__ );
		$this->settings_link = sprintf( '<a href="options-general.php?page=sue-settings-settings">%s</a>', esc_html__( "Settings", 'stop-user-enumeration' ) );

		// Include and create a new WordPressSettingsFramework
		add_action( 'admin_menu', array( $this, 'init_settings' ), 99 );


		// run plugin from init hook as needs a plugable function

		add_action( 'plugins_loaded', array( $this, 'i18n' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'register_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'register_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_scripts' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_styles' ) );


		add_action( 'plugin_row_meta', array( $this, 'sue_plugin_row_meta' ), 10, 2 );
		add_filter( 'rest_authentication_errors', array( $this, 'only_allow_logged_in_rest_access_to_users' ) );
		register_activation_hook( __FILE__, array( $this, 'activation' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivation' ) );


		// run plugin from init hook as needs a plugable function
		add_action( 'init', array( $this, 'run_plugin' ) );
	}

	/**
	 * Creates or returns an instance of this class.
	 */
	public static function get_instance() {
		// If an instance hasn't been created and set to $instance create an instance and set it to $instance.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public function get_plugin_url() {
		return $this->plugin_url;
	}

	public function get_plugin_path() {
		return $this->plugin_path;
	}

	/**
	 * Place code that runs at plugin activation here.
	 */
	public function activation() {
	}

	/**
	 * Place code that runs at plugin deactivation here.
	 */
	public function deactivation() {
	}

	/**
	 * Enqueue and register JavaScript files here.
	 */
	public function register_scripts() {
		if ( $this->checkOption( 'general_comment_jquery' ) == 1 ) {
			wp_enqueue_script( 'comment_author', plugins_url( '/js/commentauthor.js', __FILE__ ), array( 'jquery' ) );
		}
	}

	private function checkOption( $option ) {
		$options = $this->wpsf->get_settings();
		if ( $options[ $option ] == 1 ) {
			return true;
		}

		return false;
	}

	public function i18n() {

		load_plugin_textdomain( 'stop-user-enumeration', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

	}

	/**
	 * Enqueue and register CSS files here.
	 */
	public function register_styles() {
	}

	/**
	 * functionality here.
	 */
	public function run_plugin() {

		require_once( $this->plugin_path . 'wp-settings-framework.php' );
		$this->wpsf = new WordPressSettingsFramework( $this->plugin_path . 'settings/settings-general.php', 'sue_settings' );
// Add an optional settings validation filter (recommended)
		add_filter( $this->wpsf->get_option_group() . '_settings_validate', array( &$this, 'validate_settings' ) );
		if ( ! is_user_logged_in() && isset( $_REQUEST['author'] ) ) {
			if ( $this->ContainsNumbers( $_REQUEST['author'] ) ) {
				$this->sue_log();
				wp_die( esc_html__( 'forbidden - number in author name not allowed = ', 'stop-user-enumeration' ) . esc_html( $_REQUEST['author'] ) );
			}
		} elseif ( is_admin() ) {
			$setting = wpsf_get_setting( 'sue_settings', 'general', 'stop_rest_user' );
			// if the setting is exactly false then it has never been set - set admin notice
			if ( $setting === false ) {
				add_action( 'admin_notices', array( $this, 'sue_setting_nag' ) );
			}

		}
	}

	private function ContainsNumbers( $String ) {
		return preg_match( '/\\d/', $String ) > 0;
	}

	public function sue_log() {
		if ( $this->checkOption( 'general_log_auth' ) == 1 ) {
			openlog( 'wordpress(' . sanitize_text_field( $_SERVER['HTTP_HOST'] ) . ')', LOG_NDELAY | LOG_PID, LOG_AUTH );
			syslog( LOG_INFO, "Attempted user enumeration from " . sanitize_text_field( $_SERVER['REMOTE_ADDR'] ) );
			closelog();
		}
	}

	public function only_allow_logged_in_rest_access_to_users( $access ) {
		if ( $this->checkOption( 'general_stop_rest_user' ) == 1 ) {
			if ( ( preg_match( '/users/', $_SERVER['REQUEST_URI'] ) !== 0 ) || ( isset( $_REQUEST['rest_route'] ) && ( preg_match( '/users/', $_REQUEST['rest_route'] ) !== 0 ) ) ) {
				if ( ! is_user_logged_in() ) {
					$this->sue_log();

					return new WP_Error( 'rest_cannot_access', esc_html__( 'Only authenticated users can access the User endpoint REST API.', 'stop-user-enumeration' ), array( 'status' => rest_authorization_required_code() ) );
				}
			}
		}

		return $access;
	}

	public function sue_plugin_row_meta( $links, $file = '' ) {
		if ( false !== strpos( $file, '/stop-user-enumeration.php' ) ) {
			array_unshift( $links, $this->settings_link );
		}

		return $links;
	}

	public function sue_setting_nag() {
		?>
        <div class="notice notice-warning">
            <p>
				<?php
				printf( esc_html__( 'Plugin: Stop User Enumeration now has settings, go to the %1$s page and save the new settings', 'stop-user-enumeration' ), $this->settings_link );
				?>
            </p>
        </div>
		<?php

	}

	public function init_settings() {

		$this->wpsf->add_settings_page( array(
			'parent_slug' => 'options-general.php',
			'page_title'  => esc_html__( 'Stop User Enumeration Settings', 'stop-user-enumeration' ),
			'menu_title'  => esc_html__( 'Stop User Enumeration', 'stop-user-enumeration' )
		) );

	}

	function validate_settings( $input ) {
		// Do your settings validation here
		// Same as $sanitize_callback from http://codex.wordpress.org/Function_Reference/register_setting
		return $input;
	}

}

Stop_User_Enumeration_Plugin::get_instance();


?>
