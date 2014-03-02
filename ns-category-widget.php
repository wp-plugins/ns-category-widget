<?php
   /**
   * Plugin Name: NS Category Widget
   * Plugin URI: http://nilambar.net
   * Description: A widget plugin for listing categories in the way you want.
   * Version: 1.2
   * Author: Nilambar Sharma
   * Author URI: http://nilambar.net
   * License: GPLv2 or later
   * License URI: http://www.gnu.org/licenses/gpl-2.0.html
   */
  // If this file is called directly, abort.
   if (!defined('WPINC'))
   {
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

    load_plugin_textdomain( $this->plugin_slug, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

    $widget_ops = array(
      'classname'     =>  'widget_ns_category_widget',
      'description'   =>  __( "Widget for displaying categories in your way",$this->plugin_slug) );

    $this->WP_Widget( NS_CATEGORY_WIDGET_SLUG, NS_CATEGORY_WIDGET_NAME, $widget_ops );
  }

  function widget( $args, $instance ) {

    extract($args);

    $title            =   apply_filters('widget_title', $instance['title']);
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
    $instance['depth']            = strip_tags($new_instance['depth']);
    $instance['orderby']          = strip_tags($new_instance['orderby']);
    $instance['order']            = strip_tags($new_instance['order']);
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

    //Defaults
    $instance = wp_parse_args( (array) $instance, array(
      'title'           =>  'Categories',
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
        <?php _e('Title:', $this->plugin_slug); ?>
      </label>
      <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>"
      name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
    </p>
    <p>
      <label for="<?php echo $this->get_field_id('parent_category'); ?>">
        <?php _e('Parent Category:', $this->plugin_slug); ?>
      </label>
      <?php
      $cat_args = array(
        'orderby'             =>  'slug',
        'hide_empty'          =>  0,
        'name'                =>  $this->get_field_name('parent_category'),
        'id'                  =>  $this->get_field_id('parent_category'),
        'selected'            =>  $parent_category,
        'show_option_all'     =>  __('Show All',$this->plugin_slug),
        );
      wp_dropdown_categories(apply_filters('widget_categories_dropdown_args', $cat_args));
      ?>
    </p>
    <p>
      <label for="<?php echo $this->get_field_id('depth'); ?>">
        <?php _e('Depth:', $this->plugin_slug); ?>
      </label>
      <select name="<?php echo $this->get_field_name('depth'); ?>" id="<?php echo $this->get_field_id('depth'); ?>">
        <option value="0" <?php selected($depth, '0') ;?>><?php _e('Show All',$this->plugin_slug) ;?> </option>
        <option value="1" <?php selected($depth, '1') ;?> > <?php _e('1', $this->plugin_slug); ?> </option>
        <option value="2" <?php selected($depth, '2') ;?> > <?php _e('2', $this->plugin_slug); ?> </option>
        <option value="3" <?php selected($depth, '3') ;?> > <?php _e('3', $this->plugin_slug); ?> </option>
        <option value="4" <?php selected($depth, '4') ;?> > <?php _e('4', $this->plugin_slug); ?> </option>
        <option value="5" <?php selected($depth, '5') ;?> > <?php _e('5', $this->plugin_slug); ?> </option>
        <option value="6" <?php selected($depth, '6') ;?> > <?php _e('6', $this->plugin_slug); ?> </option>
        <option value="7" <?php selected($depth, '7') ;?> > <?php _e('7', $this->plugin_slug); ?> </option>
        <option value="8" <?php selected($depth, '8') ;?> > <?php _e('8', $this->plugin_slug); ?> </option>
        <option value="9" <?php selected($depth, '9') ;?> > <?php _e('9', $this->plugin_slug); ?> </option>
        <option value="10" <?php selected($depth, '10') ;?> > <?php _e('10', $this->plugin_slug); ?> </option>

      </select>
    </p>
    <p>
      <label for="<?php echo $this->get_field_id('orderby'); ?>">
        <?php _e('Sort By:', $this->plugin_slug); ?>
      </label>
      <select name="<?php echo $this->get_field_name('orderby'); ?>" id="<?php echo $this->get_field_id('orderby'); ?>">
        <option value="ID" <?php selected($orderby, 'ID') ;?>><?php _e('ID', $this->plugin_slug); ?></option>
        <option value="name" <?php selected($orderby, 'name') ;?>><?php _e('Name', $this->plugin_slug); ?></option>
        <option value="slug" <?php selected($orderby, 'slug') ;?>><?php _e('Slug', $this->plugin_slug); ?></option>
        <option value="count" <?php selected($orderby, 'count') ;?>><?php _e('Count', $this->plugin_slug); ?></option>
      </select>
    </p>
    <p>
      <label for="<?php echo $this->get_field_id('order'); ?>">
        <?php _e('Sort Order:', $this->plugin_slug); ?>
      </label>
      <select name="<?php echo $this->get_field_name('order'); ?>" id="<?php echo $this->get_field_id('order'); ?>">
        <option value="asc" <?php selected($order, 'asc') ;?>><?php _e('Ascending', $this->plugin_slug); ?></option>
        <option value="desc" <?php selected($order, 'desc') ;?>><?php _e('Descending', $this->plugin_slug); ?></option>
      </select>
    </p>
    <p>
      <input id="<?php echo $this->get_field_id('hide_empty'); ?>" name="<?php echo $this->get_field_name('hide_empty'); ?>" type="checkbox" <?php checked(isset($instance['hide_empty']) ? $instance['hide_empty'] : 0); ?> />
      <label for="<?php echo $this->get_field_id('hide_empty'); ?>">
        <?php _e('Hide Empty', $this->plugin_slug); ?>
      </label>

    </p>
    <p>
      <input id="<?php echo $this->get_field_id('show_post_count'); ?>" name="<?php echo $this->get_field_name('show_post_count'); ?>" type="checkbox" <?php checked(isset($instance['show_post_count']) ? $instance['show_post_count'] : 0); ?> />
      <label for="<?php echo $this->get_field_id('show_post_count'); ?>">
        <?php _e('Show Post Count', $this->plugin_slug); ?>
      </label>

    </p>
    <p>
      <label for="<?php echo $this->get_field_id('number'); ?>">
        <?php _e('Limit:', $this->plugin_slug); ?>
        <input class="" id="<?php echo $this->get_field_id('number'); ?>"
        name="<?php echo $this->get_field_name('number'); ?>" type="text" value="<?php echo $number; ?>" size="3"/>&nbsp;<small><?php _e('Enter limit in number', $this->plugin_slug); ?></small>
      </label>

    </p>
    <p>
      <label for="<?php echo $this->get_field_id('include_category'); ?>">
        <?php _e('Include category:', $this->plugin_slug); ?>
        <input class="widefat" id="<?php echo $this->get_field_id('include_category'); ?>"
        name="<?php echo $this->get_field_name('include_category'); ?>" type="text" value="<?php echo $include_category; ?>"/>
        <small><?php _e('Category IDs, separated by commas.', $this->plugin_slug); ?>[<strong><?php _e('Only displays these categories', $this->plugin_slug); ?></strong>]</small>
      </label>

    </p>

    <p>
          <label for="<?php echo $this->get_field_id('exclude_category'); ?>">
            <?php _e('Exclude category:', $this->plugin_slug); ?>
            <input class="widefat" id="<?php echo $this->get_field_id('exclude_category'); ?>"
            name="<?php echo $this->get_field_name('exclude_category'); ?>" type="text" value="<?php echo $exclude_category; ?>"/>
            <small><?php _e('Category IDs, separated by commas.', $this->plugin_slug); ?></small>
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

