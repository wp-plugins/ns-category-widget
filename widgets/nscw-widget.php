<?php
class NSCW_Widget extends WP_Widget {

  function __construct() {

    $widget_ops = array(
      'classname'     =>  'widget_ns_category_widget',
      'description'   =>  __( "Widget for displaying categories in your way",'ns-category-widget') );

    parent::__construct('ns-category-widget', NS_CATEGORY_WIDGET_NAME, $widget_ops );
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
    $enable_tree       =   $instance['enable_tree'];
    $tree_show_icons       =   $instance['tree_show_icons'];
    $tree_show_dots       =   $instance['tree_show_dots'];
    $tree_save_state       =   $instance['tree_save_state'];

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
    $class_text = 'nscw-inactive-tree';
    if ( 1 == $enable_tree ) {
      $class_text = 'nscw-active-tree';
    }
    echo '<div class="' . $class_text . '">';
      echo '<ul class="cat-list">';
        wp_list_categories(apply_filters('widget_categories_args', $cat_args));
      echo '</ul>';
    echo '</div>';

    $obj_nscw = NS_Category_Widget::get_instance();
    $nscw_field_enable_tree_script = $obj_nscw->get_option('nscw_field_enable_tree_script');

    if ( 1 == $enable_tree && 1 == $nscw_field_enable_tree_script ) {
      $tree_plugins = array();
      if ( 1 == $tree_save_state ) {
        $tree_plugins[] = 'state';
      }
      ?>
      <script>
        (function ( $ ) {
          "use strict";

          $(function () {

            $('#<?php echo $widget_id; ?> div').jstree({
              'plugins':[<?php echo '"'.implode('","', $tree_plugins).'"'; ?>],
              'core' : {
                'themes' : {
                  'icons' : <?php echo ( 1 == $tree_show_icons ) ? 'true' : 'false' ; ?>,
                  'dots' : <?php echo ( 1 == $tree_show_dots ) ? 'true' : 'false' ; ?>
                }
              }
            });
            //
            $('body').on('click','#<?php echo $widget_id; ?> div a', function(e){
              window.location = $(this).attr('href');
            });
          });

        }(jQuery));

      </script>
      <?php
    } //end if

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

    $str                          = explode( ',', $instance['include_category'] );
    array_walk( $str, 'intval' );
    $instance['include_category'] = implode(',',  $str );

    $instance['exclude_category'] = strip_tags($new_instance['exclude_category']);
    $str                          = explode( ',', $instance['exclude_category'] );
    array_walk( $str, 'intval' );
    $instance['exclude_category'] = implode(',',  $str );
    $instance['enable_tree']      = !empty($new_instance['enable_tree']) ? 1 : 0;
    $instance['tree_show_icons']  = !empty($new_instance['tree_show_icons']) ? 1 : 0;
    $instance['tree_show_dots']   = !empty($new_instance['tree_show_dots']) ? 1 : 0;
    $instance['tree_save_state']  = !empty($new_instance['tree_save_state']) ? 1 : 0;

    return $instance;

  }
  function form($instance) {

    global $wp_taxonomies;

    //Defaults
    $instance = wp_parse_args( (array) $instance, array(
      'title'            =>  'Categories',
      'taxonomy'         =>  'category',
      'parent_category'  =>  '',
      'depth '           =>  1,
      'orderby'          =>  'name',
      'order'            =>  'asc',
      'hide_empty'       =>  0,
      'show_post_count'  =>  0,
      'number'           =>  '',
      'include_category' =>  '',
      'exclude_category' =>  '',
      'enable_tree'      =>  0,
      'tree_show_icons'  =>  0,
      'tree_show_dots'   =>  1,
      'tree_save_state'  =>  1,
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
    $enable_tree      =   isset($instance['enable_tree']) ? esc_attr($instance['enable_tree']) : '';
    $tree_show_icons  =   isset($instance['tree_show_icons']) ? esc_attr($instance['tree_show_icons']) : '';
    $tree_show_dots   =   isset($instance['tree_show_dots']) ? esc_attr($instance['tree_show_dots']) : '';
    $tree_save_state  =   isset($instance['tree_save_state']) ? esc_attr($instance['tree_save_state']) : '';

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
    <p>
      <input id="<?php echo $this->get_field_id('enable_tree'); ?>" name="<?php echo $this->get_field_name('enable_tree'); ?>" type="checkbox" <?php checked(isset($instance['enable_tree']) ? $instance['enable_tree'] : 0); ?> />
      <label for="<?php echo $this->get_field_id('enable_tree'); ?>">
        <?php _e('Enable Tree', 'ns-category-widget'); ?>
      </label>
    </p>

    <p><a href="#" class="btn-show-advanced-tree-settings">Advanced Tree settings</a></p>

    <div class="advanced-tree-settings-wrap" style="display:none;">
      <p>
        <input id="<?php echo $this->get_field_id('tree_show_icons'); ?>" name="<?php echo $this->get_field_name('tree_show_icons'); ?>" type="checkbox" <?php checked(isset($instance['tree_show_icons']) ? $instance['tree_show_icons'] : 0); ?> />
        <label for="<?php echo $this->get_field_id('tree_show_icons'); ?>">
          <?php _e('Show Tree Icons', 'ns-category-widget'); ?>
        </label>
      </p>
      <p>
        <input id="<?php echo $this->get_field_id('tree_show_dots'); ?>" name="<?php echo $this->get_field_name('tree_show_dots'); ?>" type="checkbox" <?php checked(isset($instance['tree_show_dots']) ? $instance['tree_show_dots'] : 0); ?> />
        <label for="<?php echo $this->get_field_id('tree_show_dots'); ?>">
          <?php _e('Show Dots', 'ns-category-widget'); ?>
        </label>
      </p>
      <p>
        <input id="<?php echo $this->get_field_id('tree_save_state'); ?>" name="<?php echo $this->get_field_name('tree_save_state'); ?>" type="checkbox" <?php checked(isset($instance['tree_save_state']) ? $instance['tree_save_state'] : 0); ?> />
        <label for="<?php echo $this->get_field_id('tree_save_state'); ?>">
          <?php _e('Save Tree State', 'ns-category-widget'); ?>
        </label>
      </p>

    </div>



    <?php
  }

}
