<?php

/*
Plugin Name:    trendcounter Stats for WordPress
Plugin URI:     http://www.trendcounter.com
Description:    See in real-time what is happening on your blog with trendcounter
Version:        0.1
Author:         trendcounter
Author URI:     http://www.trendcounter.com
*/


// simple security check
defined('ABSPATH') or die('No direct access allowed!');

// define constants
define('TCWIDGET_DEBUG', false);
define('TCWIDGET_SETTINGS', 'tcwidget_settings');
define('TCWIDGET_POSITION', 'tcwidget_position');


/**
 * tcWidget
 *
 * @uses WP_Widget
 */

class tcWidget extends WP_Widget
{


    function tcWidget()
    {

        $this->WP_Widget(
            false,
            'trendcounter',
            array(
                'description' => 'Use this widget to add trendcounter to your blog'
            )
        );

    }


    function widget($args, $instance)
    {

        extract($args);

        $title = apply_filters('widget_title', $instance['title']);

        echo $before_widget;

        if ($title) {
           echo $before_title . $title . $after_title;
        }

        tcwidget_render();

        echo $after_widget;

    }


    function update($new_instance, $old_instance)
    {
        $instance = $old_instance;
        $instance['title'] = strip_tags($new_instance['title']);
        return $instance;
    }


    function form($instance)
    {

        $title = '';
        if ($instance) {
            $title = esc_attr($instance['title']);
        }

        ?>
        <p>
        <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
        <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
        <p>Please change your settings <a href='options-general.php?page=trendcounter/trendcounter.php'>here</a></p>

        </p>
        <?php

    }


}


/**
 * myplugin_register_widgets
 *
 * @access public
 * @return void
 */

function myplugin_register_widgets()
{
    register_widget('tcWidget');
}


/**
 * tcwidget_debug
 *
 * @param mixed $msg
 * @access public
 * @return void
 */

function tcwidget_debug($msg)
{

    if (!WP_DEBUG || !TCWIDGET_DEBUG) {
        return false;
    }

    error_log('tcwidget: ' . $msg);

}


/**
 * tcwidget_activate_plugin
 *
 * @access public
 * @return void
 */

function tcwidget_activate_plugin()
{
    add_option('tcwidget_key', 'test');
}


/**
 * tcwidget_options_add
 *
 * @access public
 * @return void
 */

function tcwidget_options_add()
{

    add_submenu_page(
        'plugins.php',
        'trendcounter Web Analytics',
        'trendcounter Options',
        'manage_options',
        __FILE__,
        'tcwidget_options_content'
    );

}


/**
 * tcwidget_options_content
 *
 * @access public
 * @return void
 */

function tcwidget_options_content()
{

    if (isset($_POST['update_option'])) {

        tcwidget_debug('update option');

        $params = array(
            TCWIDGET_SETTINGS,
            TCWIDGET_POSITION,
         );

        foreach ($params AS $key) {

            if (isset($_POST[$key])) {

                if ($key == TCWIDGET_POSITION && ($_POST[$key] != 'footer' && $_POST[$key] != 'widget')) {
                    continue;
                }

                update_option($key, $_POST[$key]);
            }

        }

    }

    // some simple checks
    $errorlist = '';

    $tcwidget_parsed_settings = tcwidget_get_config('key');

    if (get_option(TCWIDGET_SETTINGS) == '') {
        $errorlist .= tcwidget_add_error('Please copy the settings from your account at <a href="http://www.trendcounter.com" target="_blank">http://www.trendcounter.com</a>');
    } else if ($tcwidget_parsed_settings == false) {
        $errorlist .= tcwidget_add_error('Please enter valid project settings and make sure that you have pasted all characters here!');
    }

?>
    <div class="wrap">
    <div class="icon32" id="icon-options-general"><br /></div>
    <h2>trendcounter Web Analytics</h2>

    <?php echo $errorlist; ?>

    <form method="post">

    <p>
       Please <a href="http://www.trendcounter.com/login.htm" target="_blank">login</a> to your trendcounter.com account (or <a href="http://www.trendcounter.com/signup.htm" target="_blank">signup</a> for a new one) and go to the <b>Project HTML</b> area, where you can copy your personal WordPress settings for this plugin.
    </p>

    <h3>Plugin Settings</h3>

    <table class="form-table">
        <tr valign="top">
            <th scope="row" style="width: 200px;">Project Settings</th>
            <td>
            <textarea style="width:350px;height:120px;" name="<?php echo TCWIDGET_SETTINGS; ?>"><?php echo htmlentities(get_option(TCWIDGET_SETTINGS)); ?></textarea>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row" style="width: 200px;">Display Settings</th>
            <td>
            <select <?php if (tcwidget_get_config('widget') == 'sidebar') { echo 'disabled="disabled"'; } ?>name="<?php echo TCWIDGET_POSITION; ?>">
                <option value="footer"<?php if (tcwidget_get_config('wp_position') == 'footer') echo ' selected';?>>Display at Footer</option>
                <option value="widget"<?php if (tcwidget_get_config('wp_position') == 'widget') echo ' selected';?>>Add as Widget</option>
            </select>
            <?php if ($errorlist == '' && tcwidget_get_config('wp_position') == 'widget') { ;?>
                <span class="description">Now available at your <a href="widgets.php">widgets</a></span>
            <?php } ?>
            <?php if ($errorlist == '' && tcwidget_get_config('widget') == 'sidebar') { ;?>
                <span class="description">The sidebar Widget doesn't need this option!</span>
            <?php } ?>
            </td>
        </tr>
        <?php if ($errorlist == '') { ?>
        <tr valign="top">
            <th scope="row" style="width: 200px;">Current Settings</th>
            <td>
                <b>Your key:</b> <?php echo tcwidget_get_config('key'); ?><br />
                <b>Widget style:</b> <?php echo tcwidget_get_config('widget'); ?>
                <br /><span class="description" style="width: 350px;word-wrap:break-word;display:block;">Need a new look? Something has changed? Go to trendcounter.com and edit your project. Then copy the settings here again.</span>
            </td>
        </tr>
        <?php } ?>
    </table>

    <p class="submit">
    <input type="hidden" name="update_option" value="1" />
    <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
    </p>

    </form>
    </div>

<?php
}


/**
 * tcwidget_add_error
 *
 * @param mixed $msg
 * @access public
 * @return void
 */

function tcwidget_add_error($msg)
{
    return '<div class="error settings-error"><p><strong>' . $msg . '</strong></p></div>';
}


/**
 * tcwidget_render
 *
 * @access public
 * @return void
 */

function tcwidget_render()
{

    $center = false;
    $margins = '5px 0px 0px 0px';
    if (tcwidget_get_config('wp_position') == 'footer') {
        $center = true;
        $margins = '5px 0px 10px 0px';
    }

    if (tcwidget_get_config('widget') == 'sidebar') {
        $margins = '0px';
    }

    if ($center == true) {
        echo '<center>';
    }

    $tcwidget_config  = '[';
    $tcwidget_config .= '\'' . tcwidget_get_config('widget')  . '\',';
    $tcwidget_config .= ' \'' . tcwidget_get_config('key') . '\'';

    if (tcwidget_get_config('is_customized')) {

        if (tcwidget_get_config('widget') == 'standard'
            || tcwidget_get_config('widet') == 'blog'
            || tcwidget_get_config('widget') == 'bblog'
        ) {

            $tcwidget_config .= ', \'' . tcwidget_get_config('background_color') . '\'';
            $tcwidget_config .= ', \'' . tcwidget_get_config('font_color') . '\'';

        } else if (tcwidget_get_config('widget') == 'flag') {

            $tcwidget_config .= ', \'' . tcwidget_get_config('background_color') . '\'';
            $tcwidget_config .= ', \'' . tcwidget_get_config('font_color') . '\'';
            $tcwidget_config .= ', \'' . tcwidget_get_config('border_color') . '\'';
            $tcwidget_config .= ', \'' . tcwidget_get_config('columns') . '\'';
            $tcwidget_config .= ', \'' . tcwidget_get_config('max_rows') . '\'';
            $tcwidget_config .= ', \'' . tcwidget_get_config('country_code') . '\'';

        } else if (tcwidget_get_config('widget') == 'sidebar') {

            $tcwidget_config .= ', \'' . tcwidget_get_config('position') . '\'';
            $tcwidget_config .= ', \'' . tcwidget_get_config('background_color') . '\'';
            $tcwidget_config .= ', \'' . tcwidget_get_config('font_color') . '\'';

        }

    }

    $tcwidget_config .= ']';

    ?>
    <style type="text/css">
        #tcwidget_content {
            margin: <?php echo $margins; ?>;
        }
    </style>
    <div id="tcwidget_content">
    <script type="text/javascript" id="tc_<?php echo tcwidget_get_config('key'); ?>">
        var _tcq = _tcq || [];
        _tcq.push(<?php echo $tcwidget_config; ?>);
        (function() {
        var e = document.createElement('script'); e.type = 'text/javascript'; e.async = true;
        e.src = 'http://widgets.tcimg.com/v2/<?php echo tcwidget_get_config('widget') ?>.js'; var s = document.getElementsByTagName('script')[0];
        s.parentNode.insertBefore(e, s);
        })();
    </script>
    </div>
    <?php

    if ($center == true) {
        echo '</center>';
    }

}


/**
 * tcwidget_get_config
 *
 * @param mixed $type
 * @access public
 * @return void
 */

function tcwidget_get_config($type)
{

    if (!isset($config) || !is_array($config)) {

        static $config = array();

        $tcwidget_settings = base64_decode(get_option(TCWIDGET_SETTINGS));
        if ($tcwidget_settings !== false) {
            $tcwidget_settings = json_decode($tcwidget_settings);
            if (is_null($tcwidget_settings)) {
                $tcwidget_settings = false;
            }
        }

        $key                = isset($tcwidget_settings->key) ? $tcwidget_settings->key : false;
        $widget             = isset($tcwidget_settings->widget) ? $tcwidget_settings->widget : false;
        $position           = isset($tcwidget_settings->position) ? $tcwidget_settings->position : false;
        $font_color         = isset($tcwidget_settings->font_color) ? $tcwidget_settings->font_color : false;
        $background_color   = isset($tcwidget_settings->background_color) ? $tcwidget_settings->background_color : false;
        $border_color       = isset($tcwidget_settings->border_color) ? $tcwidget_settings->border_color : false;
        $columns            = isset($tcwidget_settings->columns) ? $tcwidget_settings->columns : false;
        $max_rows           = isset($tcwidget_settings->max_rows) ? $tcwidget_settings->max_rows : false;
        $country_code       = isset($tcwidget_settings->country_code) ? $tcwidget_settings->country_code : false;

        $is_customized = false;
        if ($font_color !== false) {
            $is_customized = true;
        }

        $wp_position = get_option(TCWIDGET_POSITION);
        if ($widget == 'sidebar') {
            $wp_position = 'footer';
        }

        $config = array(
            'wp_position'       => $wp_position,
            'is_customized'     => $is_customized,
            'key'               => $key,
            'widget'            => $widget,
            'position'          => $position,
            'font_color'        => $font_color,
            'background_color'  => $background_color,
            'border_color'      => $border_color,
            'columns'           => $columns,
            'max_rows'          => $max_rows,
            'country_code'      => $country_code,
        );

    }

    return $config[$type];

}


// wordpress hooks
register_activation_hook(__FILE__, 'tcwidget_activate_plugin');
add_action('admin_menu', 'tcwidget_options_add');

if (tcwidget_get_config('wp_position') == 'widget') {
    add_action('widgets_init', 'myplugin_register_widgets');
} else if (tcwidget_get_config('wp_position') == 'footer') {
    add_action('wp_footer', 'tcwidget_render');
}

