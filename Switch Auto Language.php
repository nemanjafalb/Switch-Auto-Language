<?php
/*
Plugin Name: Switch Auto Language
Plugin URI: https://github.com/nemanjafalb/Switch-Auto-Language
Description: Automatically redirects users from one language to another based on their browser preferences.
Version: 1.1
Author: Nemanja Falb
Author URI: https://pixelpioneerpro.net
License: GPL3
*/

// Adding notification upon plugin activation
function switch_auto_language_activation_notice() {
    ?>
    <div class="notice notice-success is-dismissible">
        <p>Thank you for installing the Switch Auto Language plugin! To configure, go to <a href="<?php echo admin_url('options-general.php?page=switch_auto_language_settings'); ?>">Settings > Switch Auto Language</a>. Visit our <a href="https://pixelpioneerpro.net">office page</a>.</p>
    </div>
    <?php
}
register_activation_hook( __FILE__, 'switch_auto_language_activation_notice' );

// Adding option in the Settings menu
function switch_auto_language_add_settings_menu() {
    add_options_page( 'Switch Auto Language Settings', 'Switch Auto Language', 'manage_options', 'switch_auto_language_settings', 'switch_auto_language_settings_page' );
}
add_action( 'admin_menu', 'switch_auto_language_add_settings_menu' );

// Displaying plugin settings page
function switch_auto_language_settings_page() {
    ?>
    <div class="wrap">
        <h1>Switch Auto Language Settings</h1>
        <form method="post" action="options.php">
            <?php settings_fields( 'switch_auto_language_settings_group' ); ?>
            <?php do_settings_sections( 'switch_auto_language_settings_group' ); ?>
            <table class="form-table">
				<tr valign="top">
                    <p>
						Welcome to Switch Auto Language plugin settings! Here you can see your default WordPress language as well as the languages enabled by TranslatePress. All unchecked checkboxes mean that the plugin will automatically redirect every user based on their browser language to the corresponding language of the website. For example, if your browser is set to English, this plugin will automatically redirect you to the English version of the site. Thank you for using this plugin. This is a beta version, and we're soon going live! :)
					</p>
                </tr>
				<tr valign="top">
                    <th scope="row">Default Language:</th>
                    <td><?php echo get_locale(); ?></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Enabled Languages:</th>
                    <td>
                        <?php
                            $enabled_languages = get_option( 'languages', array() );
                            foreach ( $enabled_languages as $language ) {
                                echo '<input type="checkbox" name="languages[]" value="' . esc_attr( $language ) . '" checked> ' . esc_html( $language ) . '<br>';
                            }
                        ?>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

// Registering plugin settings
function switch_auto_language_register_settings() {
    register_setting( 'switch_auto_language_settings_group', 'languages' );
}
add_action( 'admin_init', 'switch_auto_language_register_settings' );

// Redirecting users to the appropriate language
function switch_auto_language_redirect() {
    // Check if the redirection has already been performed
    if ( ! isset( $_COOKIE['language_redirected'] ) ) {
        // Enabled languages from plugin settings
        $enabled_languages = get_option( 'languages', array() );

        // Browser language preference
        $browser_language = isset( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) ? substr( $_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2 ) : '';

        // Check if browser language matches any enabled language
        if ( in_array( $browser_language, $enabled_languages ) ) {
            // Get the current URL
            $current_url = home_url( $_SERVER['REQUEST_URI'] );

            // Check if the current URL already contains a language extension
            if ( strpos( $current_url, '/' . $browser_language . '/' ) === false ) {
                // Redirect to the appropriate language version of the website
                wp_redirect( home_url( '/' . $browser_language ) );
                setcookie( 'language_redirected', true, time() + 3600 ); // Set cookie to prevent further redirects
                exit;
            }
        }
    }
}
add_action( 'template_redirect', 'switch_auto_language_redirect' );

// Adding link to the plugin in the Settings menu
function switch_auto_language_settings_link( $links ) {
    $settings_link = '<a href="' . admin_url( 'options-general.php?page=switch_auto_language_settings' ) . '">Settings</a>';
    array_push( $links, $settings_link );
    return $links;
}
$plugin = plugin_basename( __FILE__ );
add_filter( "plugin_action_links_$plugin", 'switch_auto_language_settings_link' );
