<?php
/**
 * Plugin Name: WP Google Dorks AI
 * Plugin URI: https://kevin-benabdelhak.fr/plugins/wp-google-dorks-ai/
 * Description: WP Google Dorks AI est un plugin WordPress innovant qui facilite la génération de requêtes Google Dork pour optimiser vos recherches en ligne. Grâce à l'intégration de l'API OpenAI, ce plugin permet de créer facilement des requêtes adaptées à votre contenu en quelques clics.
 * Version: 1.0
 * Author: Kevin Benabdelhak
 * Author URI: https://kevin-benabdelhak.fr/
 * Contributors: kevinbenabdelhak
 */

if (!defined('ABSPATH')) exit;





if ( !class_exists( 'YahnisElsts\\PluginUpdateChecker\\v5\\PucFactory' ) ) {
    require_once __DIR__ . '/plugin-update-checker/plugin-update-checker.php';
}
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$monUpdateChecker = PucFactory::buildUpdateChecker(
    'https://github.com/kevinbenabdelhak/WP-Google-Dorks-AI/', 
    __FILE__,
    'wp-google-dorks-ai' 
);
$monUpdateChecker->setBranch('main');










define('WP_GDAI_PATH', plugin_dir_path(__FILE__));

require_once WP_GDAI_PATH . 'includes/categories.php';
require_once WP_GDAI_PATH . 'includes/options-page.php';
require_once WP_GDAI_PATH . 'includes/metabox.php';
require_once WP_GDAI_PATH . 'includes/ajax.php';
require_once WP_GDAI_PATH . 'includes/bulk-export.php';

register_activation_hook(__FILE__, function() {
    if(get_option('wp_google_dorks_ai_categories') === false) {
        update_option('wp_google_dorks_ai_categories', wp_gdai_default_categories());
    }
});