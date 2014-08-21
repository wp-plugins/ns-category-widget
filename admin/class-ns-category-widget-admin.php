<?php
/**
 * NS Category Widget.
 *
 * @package   NS_Category_Widget_Admin
 * @author    Nilambar Sharma <nilambar@outlook.com>
 * @license   GPL-2.0+
 * @link      http://www.nilambar.net
 * @copyright 2014 Nilambar Sharma
 */

/**
 * @package NS_Category_Widget_Admin
 * @author  Nilambar Sharma <nilambar@outlook.com>
 */
class NS_Category_Widget_Admin {

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Slug of the plugin screen.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_screen_hook_suffix = null;

	/**
	 * Plugin options.
	 *
	 * @since    1.0.0
	 *
	 * @var      array
	 */
	protected $options = array();

	/**
	 * Initialize the plugin by loading admin scripts & styles and adding a
	 * settings page and menu.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {

		$plugin = NS_Category_Widget::get_instance();
		$this->plugin_slug = $plugin->get_plugin_slug();

		$this->options = $plugin->get_options_array();

		// Add the options page and menu item.
		add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );

		// Add an action link pointing to the options page.
		$plugin_basename = plugin_basename( plugin_dir_path( realpath( dirname( __FILE__ ) ) ) . $this->plugin_slug . '.php' );
		add_filter( 'plugin_action_links_' . $plugin_basename, array( $this, 'add_action_links' ) );

		/*
		 * Define custom functionality.
		 */
		add_action('admin_init', array($this, 'plugin_register_settings'));

		if ( $this->options['nscw_field_enable_ns_category_widget'] ) {

			add_action( 'admin_enqueue_scripts', array( $this, 'nscw_scripts_enqueue' ) );

			add_action( 'wp_ajax_populate_categories', array( $this, 'ns_category_widget_ajax_populate_categories' ) );
			add_action( 'wp_ajax_nopriv_populate_categories', array( $this, 'ns_category_widget_ajax_populate_categories' ) );

		}

	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Enqueue widget scripts.
	 *
	 * @since    1.0.0
	 */
	function nscw_scripts_enqueue( $hook ){
		if( $hook != 'widgets.php' ){
		    return;
		}
		wp_register_script( 'nscw-widget-script', NS_CATEGORY_WIDGET_URL . '/admin/assets/js/nscw-widget.js' );
		wp_localize_script( 'nscw-widget-script', 'ns_category_widget_ajax_object', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
		wp_enqueue_script('nscw-widget-script');

	}


	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @since    1.0.0
	 */
	public function add_plugin_admin_menu() {

		$this->plugin_screen_hook_suffix = add_options_page(
			__( NS_CATEGORY_WIDGET_NAME, $this->plugin_slug ),
			__( NS_CATEGORY_WIDGET_NAME, $this->plugin_slug ),
			'manage_options',
			$this->plugin_slug,
			array( $this, 'display_plugin_admin_page' )
		);

	}

	/**
	 * Render the settings page for this plugin.
	 *
	 * @since    1.0.0
	 */
	public function display_plugin_admin_page() {
		include_once( 'views/admin.php' );
	}

	/**
	 * Add settings action link to the plugins page.
	 *
	 * @since    1.0.0
	 */
	public function add_action_links( $links ) {

		return array_merge(
			array(
				'settings' => '<a href="' . admin_url( 'options-general.php?page=' . $this->plugin_slug ) . '">' . __( 'Settings', $this->plugin_slug ) . '</a>'
			),
			$links
		);

	}

  /**
   * Register plugin settings
   */
  public function plugin_register_settings()
  {

    register_setting('nscw-plugin-options-group', 'nscw_plugin_options', array( $this, 'plugin_options_validate') );

    ////

		add_settings_section('general_settings', __( 'General Settings', 'ns-category-widget' ) , array($this, 'plugin_section_general_text_callback'), 'nscw-general');

		add_settings_field('nscw_field_enable_ns_category_widget', __( 'Enable NS Category Widget', 'ns-category-widget' ), array($this, 'nscw_field_enable_ns_category_widget_callback'), 'nscw-general', 'general_settings');

    ////
		add_settings_section('tree_settings', __( 'Tree Settings', 'ns-category-widget' ) , array($this, 'plugin_section_tree_text_callback'), 'nscw-tree');

		add_settings_field('nscw_field_enable_tree_script', __( 'Enable Tree Script', 'ns-category-widget' ), array($this, 'nscw_field_enable_tree_script_callback'), 'nscw-tree', 'tree_settings');
		add_settings_field('nscw_field_enable_tree_style', __( 'Enable Tree Style', 'ns-category-widget' ), array($this, 'nscw_field_enable_tree_style_callback'), 'nscw-tree', 'tree_settings');

    ////


  }
	/**
	 * Validate our options.
	 */
  function plugin_options_validate($input) {

		$input['nscw_field_enable_ns_category_widget'] = ( isset( $input['nscw_field_enable_ns_category_widget'] ) ) ? 1 : 0 ;

		$input['nscw_field_enable_tree_script']        = ( isset( $input['nscw_field_enable_tree_script'] ) ) ? 1 : 0 ;
		$input['nscw_field_enable_tree_style']         = ( isset( $input['nscw_field_enable_tree_style'] ) ) ? 1 : 0 ;

  	return $input;
  }

	function plugin_section_general_text_callback() {
		return;
	}
	function plugin_section_tree_text_callback() {
		return;
	}

	function nscw_field_enable_ns_category_widget_callback() {
		?>
		<input type="checkbox" name="nscw_plugin_options[nscw_field_enable_ns_category_widget]" value="1"
		<?php checked(isset($this->options['nscw_field_enable_ns_category_widget']) && 1 == $this->options['nscw_field_enable_ns_category_widget']); ?> />&nbsp;<?php _e("Enable",  'simple-register' ); ?>
		<?php
	}
	function nscw_field_enable_tree_script_callback() {
		?>
		<input type="checkbox" name="nscw_plugin_options[nscw_field_enable_tree_script]" value="1"
		<?php checked(isset($this->options['nscw_field_enable_tree_script']) && 1 == $this->options['nscw_field_enable_tree_script']); ?> />&nbsp;<?php _e("Enable",  'simple-register' ); ?>
		<?php
	}
	function nscw_field_enable_tree_style_callback() {
		?>
		<input type="checkbox" name="nscw_plugin_options[nscw_field_enable_tree_style]" value="1"
		<?php checked(isset($this->options['nscw_field_enable_tree_style']) && 1 == $this->options['nscw_field_enable_tree_style']); ?> />&nbsp;<?php _e("Enable",  'simple-register' ); ?>
		<?php
	}


	/**
	 * Ajax function to populate categories in widget settings
	 */
	function ns_category_widget_ajax_populate_categories(){

		$output           = array();
		$output['status'] = 0;

		$taxonomy         = $_POST['taxonomy'];
		$name             = $_POST['name'];
		$id               = $_POST['id'];

	  $cat_args = array(
			'orderby'         =>  'slug',
			'taxonomy'        =>  $taxonomy,
			'echo'            =>  '0',
			'hide_empty'      =>  0,
			'name'            =>  $name,
			'id'              =>  $id,
			'class'           =>  'nscw-cat-list',
			'show_option_all' =>  __('Show All','ns-category-widget'),
	    );
		$output['html']   = wp_dropdown_categories(apply_filters('widget_categories_dropdown_args', $cat_args));
		$output['status'] = 1;

	  wp_send_json($output);
	}

}
