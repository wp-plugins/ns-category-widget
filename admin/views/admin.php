<?php
/**
 * Represents the view for the administration dashboard.
 *
 * @package   Simple_Register
 * @author    Nilambar Sharma <nilambar@outlook.com>
 * @license   GPL-2.0+
 * @link      http://www.nilambar.net
 * @copyright 2014 Nilambar Sharma
 */
?>
<div class="wrap">

  <h2><?php echo esc_html( get_admin_page_title() ); ?></h2>

  <div id="poststuff">

    <div id="post-body" class="metabox-holder columns-2">

      <!-- main content -->
      <div id="post-body-content">

      <form action="options.php" method="post">
        <?php settings_fields('nscw-plugin-options-group'); ?>

          <div class="meta-box-sortables ui-sortable">

            <div class="postbox">

              <div class="inside">
               <?php do_settings_sections('nscw-general'); ?>
             </div> <!-- .inside -->

            </div> <!-- .postbox -->

          </div> <!-- .meta-box-sortables .ui-sortable -->

          <div class="meta-box-sortables ui-sortable">

            <div class="postbox">

             <div class="inside">
               <?php do_settings_sections('nscw-tree'); ?>
             </div> <!-- .inside -->

            </div> <!-- .postbox -->



          </div> <!-- .meta-box-sortables .ui-sortable -->

          <?php submit_button(__('Save Changes', 'ns-category-widget')); ?>
          </form>

      </div> <!-- post-body-content -->

      <!-- sidebar -->
      <div id="postbox-container-1" class="postbox-container">

        <?php require_once( NS_CATEGORY_WIDGET_DIR.'/admin/views/admin-right.php'); ?>

      </div> <!-- #postbox-container-1 .postbox-container -->

    </div> <!-- #post-body .metabox-holder .columns-2 -->

    <br class="clear">
  </div> <!-- #poststuff -->

</div> <!-- .wrap -->
