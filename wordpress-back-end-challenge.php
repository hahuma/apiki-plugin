<?php
/**
 * Plugin Name: WordPress Back End Challenge - Favorite Posts
 * Description: A plugin to manage the favorite posts
 * Version: 1.0.0
 * Author: Kayo Almondes
 * Text Domain: wordpress-back-end-challenge
 */
namespace WordPressBackEndChallenge;

defined('ABSPATH') || exit;

require_once __DIR__ . '/src/dbHandler.php';
require_once __DIR__ . '/src/restHandler.php';

function favorite_posts_plugin_activate() {
    $db = new DbHandler();
    $db->createTable();
}

register_activation_hook(__FILE__, __NAMESPACE__ . '\\favorite_posts_plugin_activate');


class WordPressBackEndChallenge {
    private $dbHandler;

    public function __construct() {
        $this->dbHandler = new DbHandler();
        new RestHandler();

        add_filter( 'the_content', [$this, 'addFavoritePostIcon'] );
        add_action( 'wp_enqueue_scripts', [$this, 'enqueueScripts'] );
    }

    public function addFavoritePostIcon( $content )
    {
        if ( !is_singular() || !in_the_loop() || !is_main_query() || !is_user_logged_in() ) :
            return $content;
        endif;

        $is_favorite = $this->dbHandler->getFavoriteByUserAndPost(get_current_user_id(), get_the_ID());

        $custom_content = "<button class='favorite-post-button" . ($is_favorite || !empty($is_favorite) ? ' is-favorite' : '') . "' data-post-id='" . get_the_ID() . "' data-component='favorite-post' type='button'>
            <img src='" . esc_url( plugin_dir_url( __FILE__ ) . 'assets/icons/heart.png' ) . "' alt=''/>
        </button>";

        $content = $custom_content . $content;

        return $content;
    }

    public function enqueueScripts() {
        wp_enqueue_script(
            'wordpress-back-end-challenge',
            plugin_dir_url( __FILE__ ) . 'dist/index.js',
            [],
            filemtime(plugin_dir_path( __FILE__ ) . 'dist/index.js'),
            [
                'in_footer' => true,
                'strategy' => 'async'
            ]
        );

        wp_localize_script(
            'wordpress-back-end-challenge',
            'wpApiSettings',
            [
                'rest_namespace' => RestHandler::getRestNamespace(),
                'root'           => esc_url_raw(rest_url()),
                'nonce'          => wp_create_nonce('wp_rest'),
            ]
        );

        wp_enqueue_style(
            'wordpress-back-end-challenge',
            plugin_dir_url( __FILE__ ) . 'dist/index.css',
            [],
            filemtime(plugin_dir_path( __FILE__ ) . 'dist/index.css')
        );
    }
}

new WordPressBackEndChallenge();
