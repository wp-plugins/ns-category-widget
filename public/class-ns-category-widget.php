<?php
/**
 * NS Category Widget.
 *
 * @package   NS_Category_Widget
 * @author    Nilambar Sharma <nilambar@outlook.com>
 * @license   GPL-2.0+
 * @link      http://www.nilambar.net
 * @copyright 2014 Nilambar Sharma
 */

/**
 * @package NS_Category_Widget
 * @author  Nilambar Sharma <nilambar@outlook.com>
 */
class NS_Category_Widget {

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since   1.0.0
	 *
	 * @var     string
	 */
	const VERSION = '2.0.2';

	/**
	 * Unique identifier for your plugin.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_slug = NS_CATEGORY_WIDGET_SLUG;

	/**
	 * Unique option name of the plugin.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected static $plugin_option_name = 'nscw_plugin_options';

	/**
	 * Default options of the plugin.
	 *
	 * @since    1.0.0
	 *
	 * @var      array
	 */
	protected static $default_options = null ;

	/**
	 * Plugin options.
	 *
	 * @since    1.0.0
	 *
	 * @var      array
	 */
	protected $options = array();

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {

		// Load plugin text domain
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		// Activate plugin when new blog is added
		add_action( 'wpmu_new_blog', array( $this, 'activate_new_site' ) );

		self :: $default_options = array(
				'nscw_field_enable_ns_category_widget' => 1,
				'nscw_field_enable_tree_script'        => 1,
				'nscw_field_enable_tree_style'         => 1,
		);
		// Set Default options of the plugin
		$this -> _setDefaultOptions();

		// Populate current options
    $this->_getCurrentOptions();

		// Load public-facing style sheet and JavaScript.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

	}

	/**
	 * Return the plugin slug.
	 *
	 * @since    1.0.0
	 *
	 * @return    Plugin slug variable.
	 */
	public function get_plugin_slug() {
		return $this->plugin_slug;
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Fired when the plugin is activated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses
	 *                                       "Network Activate" action, false if
	 *                                       WPMU is disabled or plugin is
	 *                                       activated on an individual blog.
	 */
	public static function activate( $network_wide ) {

		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			if ( $network_wide  ) {

				// Get all blog ids
				$blog_ids = self::get_blog_ids();

				foreach ( $blog_ids as $blog_id ) {

					switch_to_blog( $blog_id );
					self::single_activate();

					restore_current_blog();
				}

			} else {
				self::single_activate();
			}

		} else {
			self::single_activate();
		}

	}

	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses
	 *                                       "Network Deactivate" action, false if
	 *                                       WPMU is disabled or plugin is
	 *                                       deactivated on an individual blog.
	 */
	public static function deactivate( $network_wide ) {

		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			if ( $network_wide ) {

				// Get all blog ids
				$blog_ids = self::get_blog_ids();

				foreach ( $blog_ids as $blog_id ) {

					switch_to_blog( $blog_id );
					self::single_deactivate();

					restore_current_blog();

				}

			} else {
				self::single_deactivate();
			}

		} else {
			self::single_deactivate();
		}

	}

	/**
	 * Fired when a new site is activated with a WPMU environment.
	 *
	 * @since    1.0.0
	 *
	 * @param    int    $blog_id    ID of the new blog.
	 */
	public function activate_new_site( $blog_id ) {

		if ( 1 !== did_action( 'wpmu_new_blog' ) ) {
			return;
		}

		switch_to_blog( $blog_id );
		self::single_activate();
		restore_current_blog();

	}

	/**
	 * Get all blog ids of blogs in the current network that are:
	 * - not archived
	 * - not spam
	 * - not deleted
	 *
	 * @since    1.0.0
	 *
	 * @return   array|false    The blog ids, false if no matches.
	 */
	private static function get_blog_ids() {

		global $wpdb;

		// get an array of blog ids
		$sql = "SELECT blog_id FROM $wpdb->blogs
			WHERE archived = '0' AND spam = '0'
			AND deleted = '0'";

		return $wpdb->get_col( $sql );

	}

	/**
	 * Fired for each blog when the plugin is activated.
	 *
	 * @since    1.0.0
	 */
	private static function single_activate() {

		update_option(self :: $plugin_option_name, self :: $default_options);

	}

	/**
	 * Fired for each blog when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 */
	private static function single_deactivate() {
		//
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		$domain = $this->plugin_slug;
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

		load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '/' . $domain . '-' . $locale . '.mo' );
		load_plugin_textdomain( $domain, FALSE, basename( plugin_dir_path( dirname( __FILE__ ) ) ) . '/languages/' );

	}

	/**
	 * Register and enqueue public-facing style sheet.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		if ( 1 == $this->options['nscw_field_enable_tree_style']) {
			wp_enqueue_style(  $this->plugin_slug . '-tree-style', plugins_url( 'assets/css/themes/default/style.css', __FILE__ ), array(), self::VERSION );
		}
	}

	/**
	 * Register and enqueues public-facing JavaScript files.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		if ( 1 == $this->options['nscw_field_enable_tree_script']){
			wp_enqueue_script( 'tree-script', plugins_url( 'assets/js/jstree.min.js', __FILE__ ), array( 'jquery' ), self::VERSION, true );
			wp_enqueue_script( 'tree-script-state', plugins_url( 'assets/js/jstree.state.js', __FILE__ ), array( 'jquery' ), self::VERSION, true );
			wp_enqueue_script( $this->plugin_slug . '-plugin-script', plugins_url( 'assets/js/public.js', __FILE__ ), array( 'jquery' ), self::VERSION, true );
		}
	}


	/**
	 * Return current options.
	 *
	 * @since    1.0.0
	 *
	 * @return    array Current options
	 */
  public function get_options_array(){
		return $this->options;
	}
	/**
	 * Return option value.
	 *
	 * @since    1.0.0
	 *
	 * @return    array Current options
	 */
  public function get_option( $key ){
  	if (array_key_exists($key, $this->options)) {
			return $this->options[$key];
  	}
  	else{
  		return false;
  	}
	}

	// Private STARTS

	/**
	 * Populate current options.
	 *
	 * @since    1.0.0
	 */
	private function _getCurrentOptions() {
		$options = array_merge( self :: $default_options , (array) get_option( self :: $plugin_option_name, array() ) );
    $this->options = $options;
  }

  /**
   * Get default options and saves in options table.
   *
   * @since    1.0.0
   */
  private function _setDefaultOptions() {
      if( !get_option( self :: $plugin_option_name ) ) {
          update_option( self :: $plugin_option_name, self :: $default_options);
      }
  }

  // Private ENDS


}
