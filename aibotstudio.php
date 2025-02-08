<?php
/**
 * Plugin Name: AIBot Studio
 * Description: AIBot Studio allows you to display a customizable AI chatbot on selected pages of your website.
 * Version: 1.0.6
 * Author: AIBot Studio
 * Author URI: https://aibotstudio.org/plugin
 * License: GPL2
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Include Composer autoloader for Plugin Update Checker
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require __DIR__ . '/vendor/autoload.php';
}

use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$updateChecker = PucFactory::buildUpdateChecker(
    'https://github.com/kloewer/aibotstudio/',
    __FILE__,
    'aibotstudio'
);
$updateChecker->setBranch('main');

// Add settings menu
function aibotstudio_add_menu() {
    add_menu_page(
        'AIBot Studio Settings',
        'AIBot Studio',
        'manage_options',
        'aibotstudio',
        'aibotstudio_settings_page',
        'dashicons-format-chat' // Changed icon to a chat bubble
    );
}
add_action('admin_menu', 'aibotstudio_add_menu');

// Register settings
function aibotstudio_register_settings() {
    register_setting('aibotstudio_settings_group', 'aibotstudio_enabled');
    register_setting('aibotstudio_settings_group', 'aibotstudio_id');
    register_setting('aibotstudio_settings_group', 'aibotstudio_include_pages');
    register_setting('aibotstudio_settings_group', 'aibotstudio_exclude_pages');
}
add_action('admin_init', 'aibotstudio_register_settings');

// Updated vendor dependencies
// Updated changelog handling
// Updated plugin description for better clarity

// Settings page
function aibotstudio_settings_page() {
    ?>
    <div class="wrap">
        <h1>AIBot Studio Settings</h1>
        <form method="post" action="options.php">
            <?php settings_fields('aibotstudio_settings_group'); ?>
            <?php do_settings_sections('aibotstudio_settings_group'); ?>
            <table class="form-table">
                <tr>
                    <th scope="row">Enable AI Bot</th>
                    <td>
                        <input type="checkbox" name="aibotstudio_enabled" value="1" <?php checked(1, get_option('aibotstudio_enabled'), true); ?> />
                    </td>
                </tr>
                <tr>
                    <th scope="row">Chatbot ID</th>
                    <td>
                        <input type="text" name="aibotstudio_id" value="<?php echo esc_attr(get_option('aibotstudio_id', '67a674541088f801a5a18277')); ?>" size="50" />
                        <p>Enter the chatbot ID or full tag: <code>&lt;ai-bot-bubble id="..." /&gt;</code></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Included Pages</th>
                    <td>
                        <textarea name="aibotstudio_include_pages" rows="10" cols="50"><?php echo esc_textarea(get_option('aibotstudio_include_pages', '')); ?></textarea>
                        <p>Specify the pages where the chatbot should appear (wildcards * allowed, e.g., /contact/*).</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Excluded Pages</th>
                    <td>
                        <textarea name="aibotstudio_exclude_pages" rows="10" cols="50"><?php echo esc_textarea(get_option('aibotstudio_exclude_pages', '')); ?></textarea>
                        <p>Specify the pages where the chatbot should NOT appear (wildcards * allowed, e.g., /blog/*).</p>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

// Enqueue chatbot script in the header
function aibotstudio_enqueue_script() {
    if (!get_option('aibotstudio_enabled')) {
        return;
    }
    echo '<script src="https://m.aibotstudio.com/script/index.js"></script>';
}
add_action('wp_head', 'aibotstudio_enqueue_script');

// Display chatbot on allowed pages
function aibotstudio_display_chatbot() {
    if (!get_option('aibotstudio_enabled')) {
        return;
    }

    $chatbot_id = get_option('aibotstudio_id', '67a674541088f801a5a18277');
    if (!preg_match('/<ai-bot-bubble.*?>/', $chatbot_id)) {
        $chatbot_id = '<ai-bot-bubble id="' . esc_attr($chatbot_id) . '" />';
    }

    $included_pages = array_filter(array_map('trim', explode("\n", get_option('aibotstudio_include_pages', ''))));
    $excluded_pages = array_filter(array_map('trim', explode("\n", get_option('aibotstudio_exclude_pages', ''))));
    $current_path = $_SERVER['REQUEST_URI'];

    foreach ($excluded_pages as $exclude) {
        if (fnmatch($exclude, $current_path)) {
            return;
        }
    }
    
    foreach ($included_pages as $include) {
        if (fnmatch($include, $current_path)) {
            echo $chatbot_id;
            return;
        }
    }
}
add_action('wp_footer', 'aibotstudio_display_chatbot');

// Add custom links to the plugin listing
function aibotstudio_plugin_links($links, $file) {
    if ($file === plugin_basename(__FILE__)) {
        $links[] = '<a href="https://aibotstudio.org/donate" target="_blank">Donate</a>';
        $links[] = '<a href="https://aibotstudio.org/support" target="_blank">Support</a>';
    }
    return $links;
}
add_filter('plugin_row_meta', 'aibotstudio_plugin_links', 10, 2);