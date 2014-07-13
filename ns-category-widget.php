<?php
   /**
   * Plugin Name: NS Category Widget
   * Plugin URI: http://nilambar.net
   * Description: A widget plugin for listing categories in the way you want.
   * Version: 1.4.2
   * Author: Nilambar Sharma
   * Author URI: http://nilambar.net
   * License: GPLv2 or later
   * License URI: http://www.gnu.org/licenses/gpl-2.0.html
   */
  // If this file is called directly, abort.
  if (!defined('WPINC')) {
    die;
  }
  define('NS_CATEGORY_WIDGET_NAME','NS Category Widget');
  define('NS_CATEGORY_WIDGET_SLUG','ns-category-widget');

 /**
 * NS_Category_Widget
 */
 class NS_Category_Widget extends WP_Widget {
  protected $plugin_slug = NS_CATEGORY_WIDGET_SLUG ;
  /**
  * Declares the NS_Category_Widget class.
  *
  */

  function NS_Category_Widget() {

    load_plugin_textdomain( 'ns-category-widget', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

    $widget_ops = array(
      'classname'     =>  'widget_ns_category_widget',
      'description'   =>  __( "Widget for displaying categories in your way",'ns-category-widget') );

    $this->WP_Widget( NS_CATEGORY_WIDGET_SLUG, NS_CATEGORY_WIDGET_NAME, $widget_ops );
  }

  function widget( $args, $instance ) {

    extract($args);

    $title            =   apply_filters('widget_title', $instance['title']);
    $taxonomy         =   $instance['taxonomy'];
    $parent_category  =   $instance['parent_category'];
    $depth            =   $instance['depth'];
    $orderby          =   $instance['orderby'];
    $order            =   $instance['order'];
    $hide_empty       =   $instance['hide_empty'];
    $show_post_count  =   $instance['show_post_count'];
    $number           =   $instance['number'];
    $include_category =   $instance['include_category'];
    $exclude_category =   $instance['exclude_category'];
    if( '' != $exclude_category  ){
      $include_category = '';
    }

    echo $before_widget;
    if ( $title ){
      echo $before_title . $title . $after_title;
    }
    $cat_args = array(
      'title_li'    =>  '',
      'depth'       =>  $depth,
      'orderby'     =>  $orderby,
      'order'       =>  $order,
      'hide_empty'  =>  $hide_empty,
      'show_count'  =>  $show_post_count,
      'number'      =>  $number,
      'include'     =>  $include_category,
      'exclude'     =>  $exclude_category,
      'taxonomy'    =>  $taxonomy,

      );

    if($parent_category){
      $cat_args['child_of'] = $parent_category;
    }
    echo '<ul>';
    wp_list_categories(apply_filters('widget_categories_args', $cat_args));
    echo '</ul>';
    echo $after_widget;
  }

  function update($new_instance, $old_instance) {

    $instance = array();

    $instance['title']            = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
    $instance['parent_category']  = strip_tags($new_instance['parent_category']);
    $instance['taxonomy']         = strip_tags($new_instance['taxonomy']);
    $instance['depth']            = esc_attr($new_instance['depth']);
    $instance['orderby']          = esc_attr($new_instance['orderby']);
    $instance['order']            = esc_attr($new_instance['order']);
    $instance['hide_empty']       = !empty($new_instance['hide_empty']) ? 1 : 0;
    $instance['show_post_count']  = !empty($new_instance['show_post_count']) ? 1 : 0;
    $instance['number']           = ( ! empty( $new_instance['number'] ) ) ? intval( strip_tags( $new_instance['number'] )  ): '';
    $instance['include_category'] = trim( strip_tags($new_instance['include_category']) );

    $str = explode( ',', $instance['include_category'] );
    array_walk( $str, 'intval' );
    $instance['include_category'] = implode(',',  $str );

    $instance['exclude_category'] = strip_tags($new_instance['exclude_category']);
    $str = explode( ',', $instance['exclude_category'] );
    array_walk( $str, 'intval' );
    $instance['exclude_category'] = implode(',',  $str );

    return $instance;

  }
  function form($instance) {

    global $wp_taxonomies;

    //Defaults
    $instance = wp_parse_args( (array) $instance, array(
      'title'           =>  'Categories',
      'taxonomy'        =>  'category',
      'parent_category' =>  '',
      'depth '          =>  1,
      'orderby'         =>  'name',
      'order'           =>  'asc',
      'hide_empty'      =>  0,
      'show_post_count' =>  0,
      'number'          =>  '',
      'include_category'=>  '',
      'exclude_category'=>  '',
      ) );
    $title            =   htmlspecialchars($instance['title']);
    $taxonomy         =   htmlspecialchars($instance['taxonomy']);
    $parent_category  =   isset($instance['parent_category']) ? esc_attr($instance['parent_category']) : '';
    $depth            =   isset($instance['depth']) ? esc_attr($instance['depth']) : '';
    $orderby          =   isset($instance['orderby']) ? esc_attr($instance['orderby']) : '';
    $order            =   isset($instance['order']) ? esc_attr($instance['order']) : '';
    $hide_empty       =   isset($instance['hide_empty']) ? esc_attr($instance['hide_empty']) : '';
    $show_post_count  =   isset($instance['show_post_count']) ? esc_attr($instance['show_post_count']) : '';
    $number           =   htmlspecialchars($instance['number']);
    $include_category =   htmlspecialchars($instance['include_category']);
    $exclude_category =   htmlspecialchars($instance['exclude_category']);

    ?>
    <p>
      <label for="<?php echo $this->get_field_id('title'); ?>">
        <?php _e('Title:', 'ns-category-widget'); ?>
      </label>
      <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>"
      name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
    </p>
    <p>
      <label for="<?php echo $this->get_field_id('taxonomy'); ?>">
        <?php _e('Taxonomy:', 'ns-category-widget'); ?>
      </label>
      <select id="<?php echo $this->get_field_id( 'taxonomy' ); ?>" name="<?php echo $this->get_field_name( 'taxonomy' ); ?>" class="nscw-taxonomy" data-name="<?php echo $this->get_field_name('parent_category'); ?>" data-id="<?php echo $this->get_field_id('parent_category'); ?>" >
        <?php foreach ($wp_taxonomies as $taxonomy_item) {
          if ( in_array($taxonomy_item->name, array('post_format', 'post_tag', 'link_category', 'nav_menu' ) ) == false ) {
            echo '<option '.selected( $taxonomy, $taxonomy_item->name, false ) . ' value="'.$taxonomy_item->name.'">'.$taxonomy_item->label.'</option>';
          }
        } ?>
      </select>
    </p>
    <p>
      <label for="<?php echo $this->get_field_id('parent_category'); ?>">
        <?php _e('Parent Category:', 'ns-category-widget'); ?>
      </label>
      <?php
      $cat_args = array(
        'orderby'         =>  'slug',
        'hide_empty'      =>  0,
        'taxonomy'      =>  $taxonomy,
        'name'            =>  $this->get_field_name('parent_category'),
        'id'              =>  $this->get_field_id('parent_category'),
        'class'           =>  'nscw-cat-list',
        'selected'        =>  $parent_category,
        'show_option_all' =>  __('Show All','ns-category-widget'),
        );
      wp_dropdown_categories(apply_filters('widget_categories_dropdown_args', $cat_args));
      ?>
    </p>
    <p>
      <label for="<?php echo $this->get_field_id('depth'); ?>">
        <?php _e('Depth:', 'ns-category-widget'); ?>
      </label>
      <select name="<?php echo $this->get_field_name('depth'); ?>" id="<?php echo $this->get_field_id('depth'); ?>">
        <option value="0" <?php selected($depth, '0') ;?>><?php _e('Show All','ns-category-widget') ;?> </option>
        <option value="1" <?php selected($depth, '1') ;?> > <?php _e('1', 'ns-category-widget'); ?> </option>
        <option value="2" <?php selected($depth, '2') ;?> > <?php _e('2', 'ns-category-widget'); ?> </option>
        <option value="3" <?php selected($depth, '3') ;?> > <?php _e('3', 'ns-category-widget'); ?> </option>
        <option value="4" <?php selected($depth, '4') ;?> > <?php _e('4', 'ns-category-widget'); ?> </option>
        <option value="5" <?php selected($depth, '5') ;?> > <?php _e('5', 'ns-category-widget'); ?> </option>
        <option value="6" <?php selected($depth, '6') ;?> > <?php _e('6', 'ns-category-widget'); ?> </option>
        <option value="7" <?php selected($depth, '7') ;?> > <?php _e('7', 'ns-category-widget'); ?> </option>
        <option value="8" <?php selected($depth, '8') ;?> > <?php _e('8', 'ns-category-widget'); ?> </option>
        <option value="9" <?php selected($depth, '9') ;?> > <?php _e('9', 'ns-category-widget'); ?> </option>
        <option value="10" <?php selected($depth, '10') ;?> > <?php _e('10', 'ns-category-widget'); ?> </option>

      </select>
    </p>
    <p>
      <label for="<?php echo $this->get_field_id('orderby'); ?>">
        <?php _e('Sort By:', 'ns-category-widget'); ?>
      </label>
      <select name="<?php echo $this->get_field_name('orderby'); ?>" id="<?php echo $this->get_field_id('orderby'); ?>">
        <option value="ID" <?php selected($orderby, 'ID') ;?>><?php _e('ID', 'ns-category-widget'); ?></option>
        <option value="name" <?php selected($orderby, 'name') ;?>><?php _e('Name', 'ns-category-widget'); ?></option>
        <option value="slug" <?php selected($orderby, 'slug') ;?>><?php _e('Slug', 'ns-category-widget'); ?></option>
        <option value="count" <?php selected($orderby, 'count') ;?>><?php _e('Count', 'ns-category-widget'); ?></option>
      </select>
    </p>
    <p>
      <label for="<?php echo $this->get_field_id('order'); ?>">
        <?php _e('Sort Order:', 'ns-category-widget'); ?>
      </label>
      <select name="<?php echo $this->get_field_name('order'); ?>" id="<?php echo $this->get_field_id('order'); ?>">
        <option value="asc" <?php selected($order, 'asc') ;?>><?php _e('Ascending', 'ns-category-widget'); ?></option>
        <option value="desc" <?php selected($order, 'desc') ;?>><?php _e('Descending', 'ns-category-widget'); ?></option>
      </select>
    </p>
    <p>
      <input id="<?php echo $this->get_field_id('hide_empty'); ?>" name="<?php echo $this->get_field_name('hide_empty'); ?>" type="checkbox" <?php checked(isset($instance['hide_empty']) ? $instance['hide_empty'] : 0); ?> />
      <label for="<?php echo $this->get_field_id('hide_empty'); ?>">
        <?php _e('Hide Empty', 'ns-category-widget'); ?>
      </label>

    </p>
    <p>
      <input id="<?php echo $this->get_field_id('show_post_count'); ?>" name="<?php echo $this->get_field_name('show_post_count'); ?>" type="checkbox" <?php checked(isset($instance['show_post_count']) ? $instance['show_post_count'] : 0); ?> />
      <label for="<?php echo $this->get_field_id('show_post_count'); ?>">
        <?php _e('Show Post Count', 'ns-category-widget'); ?>
      </label>

    </p>
    <p>
      <label for="<?php echo $this->get_field_id('number'); ?>">
        <?php _e('Limit:', 'ns-category-widget'); ?>
        <input class="" type="number" min="0" id="<?php echo $this->get_field_id('number'); ?>"
        name="<?php echo $this->get_field_name('number'); ?>" type="text" value="<?php echo $number; ?>" size="3"/>&nbsp;<small><?php _e('Enter limit in number', 'ns-category-widget'); ?></small>
      </label>

    </p>
    <p>
      <label for="<?php echo $this->get_field_id('include_category'); ?>">
        <?php _e('Include category:', 'ns-category-widget'); ?>
        <input class="widefat" id="<?php echo $this->get_field_id('include_category'); ?>"
        name="<?php echo $this->get_field_name('include_category'); ?>" type="text" value="<?php echo $include_category; ?>"/>
        <small><?php _e('Category IDs, separated by commas.', 'ns-category-widget'); ?>[<strong><?php _e('Only displays these categories', 'ns-category-widget'); ?></strong>]</small>
      </label>

    </p>

    <p>
          <label for="<?php echo $this->get_field_id('exclude_category'); ?>">
            <?php _e('Exclude category:', 'ns-category-widget'); ?>
            <input class="widefat" id="<?php echo $this->get_field_id('exclude_category'); ?>"
            name="<?php echo $this->get_field_name('exclude_category'); ?>" type="text" value="<?php echo $exclude_category; ?>"/>
            <small><?php _e('Category IDs, separated by commas.', 'ns-category-widget'); ?></small>
          </label>

        </p>


    <?php
  }

}

/**
  * Register  widget.
  *
  * Calls 'widgets_init' action after widget has been registered.
  */
function register_ns_category_widget() {
  register_widget('NS_Category_Widget');
}
add_action( 'widgets_init', 'register_ns_category_widget');


// queue up the necessary js
function ns_category_widget_upload_enqueue($hook)
{
  if( $hook != 'widgets.php' )
      return;

  wp_register_script( 'ns-category-widget-script', plugins_url( 'ns-category-widget.js', __FILE__ ) );
  wp_localize_script( 'ns-category-widget-script', 'ns_category_widget_ajax_object', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
  wp_enqueue_script('ns-category-widget-script');

}
add_action('admin_enqueue_scripts', 'ns_category_widget_upload_enqueue');


function ns_category_widget_ajax_populate_categories(){
  $output = array();
  $output['status'] = 0;

  // wp_send_json_success($_POST);

  $taxonomy = $_POST['taxonomy'];
  $name = $_POST['name'];
  $id = $_POST['id'];

  $cat_args = array(
    'orderby'         =>  'slug',
    'taxonomy'         =>  $taxonomy,
    'echo'         =>  '0',
    'hide_empty'      =>  0,
    'name'            =>  $name,
    'id'              =>  $id,
    'class'           =>  'nscw-cat-list',
    'show_option_all' =>  __('Show All','ns-category-widget'),
    );
  $output['html'] = wp_dropdown_categories(apply_filters('widget_categories_dropdown_args', $cat_args));
  $output['status'] = 1;

  wp_send_json($output);
}

add_action( 'wp_ajax_populate_categories', 'ns_category_widget_ajax_populate_categories' );
add_action( 'wp_ajax_nopriv_populate_categories', 'ns_category_widget_ajax_populate_categories' );

