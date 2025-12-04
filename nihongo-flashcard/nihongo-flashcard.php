<?php
/**
 * Plugin Name: Nihongo Flashcard - Học tiếng Nhật
 * Plugin URI: https://github.com/datnong1210-arch/AhoKAIWAcard
 * Description: Plugin WordPress học tiếng Nhật dành cho người Việt với hệ thống Flashcard chuyên nghiệp, đẹp mắt. Hỗ trợ Audio Player, Import/Export CSV, Course System.
 * Version: 1.0.0
 * Author: AhoKAIWA
 * Author URI: https://github.com/datnong1210-arch
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: nihongo-flashcard
 * Domain Path: /languages
 * Requires at least: 5.0
 * Requires PHP: 7.4
 *
 * @package NihongoFlashcard
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Plugin constants
define( 'NIHONGO_FLASHCARD_VERSION', '1.0.0' );
define( 'NIHONGO_FLASHCARD_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'NIHONGO_FLASHCARD_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'NIHONGO_FLASHCARD_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Main plugin class
 */
final class Nihongo_Flashcard {

    /**
     * Single instance of the class
     *
     * @var Nihongo_Flashcard
     */
    private static $instance = null;

    /**
     * Get single instance of the class
     *
     * @return Nihongo_Flashcard
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->load_dependencies();
        $this->init_hooks();
    }

    /**
     * Load required files
     */
    private function load_dependencies() {
        // Core classes
        require_once NIHONGO_FLASHCARD_PLUGIN_DIR . 'includes/class-flashcard-post-type.php';
        require_once NIHONGO_FLASHCARD_PLUGIN_DIR . 'includes/class-course-post-type.php';
        require_once NIHONGO_FLASHCARD_PLUGIN_DIR . 'includes/class-csv-handler.php';
        require_once NIHONGO_FLASHCARD_PLUGIN_DIR . 'includes/class-shortcodes.php';
        require_once NIHONGO_FLASHCARD_PLUGIN_DIR . 'includes/class-ajax-handler.php';
        require_once NIHONGO_FLASHCARD_PLUGIN_DIR . 'includes/class-progress-tracker.php';

        // Admin classes
        if ( is_admin() ) {
            require_once NIHONGO_FLASHCARD_PLUGIN_DIR . 'admin/class-admin-menu.php';
            require_once NIHONGO_FLASHCARD_PLUGIN_DIR . 'admin/class-deck-metabox.php';
            require_once NIHONGO_FLASHCARD_PLUGIN_DIR . 'admin/class-course-metabox.php';
        }
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Load text domain
        add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );

        // Enqueue scripts and styles
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );

        // Activation and deactivation hooks
        register_activation_hook( __FILE__, array( $this, 'activate' ) );
        register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );
    }

    /**
     * Load plugin text domain
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            'nihongo-flashcard',
            false,
            dirname( NIHONGO_FLASHCARD_PLUGIN_BASENAME ) . '/languages/'
        );
    }

    /**
     * Enqueue frontend assets
     */
    public function enqueue_frontend_assets() {
        // Only load on pages with our shortcodes
        global $post;
        if ( is_a( $post, 'WP_Post' ) && ( has_shortcode( $post->post_content, 'nihongo_deck' ) || has_shortcode( $post->post_content, 'nihongo_course' ) ) ) {
            // CSS
            wp_enqueue_style(
                'nihongo-flashcard-frontend',
                NIHONGO_FLASHCARD_PLUGIN_URL . 'assets/css/frontend.css',
                array(),
                NIHONGO_FLASHCARD_VERSION
            );

            wp_enqueue_style(
                'nihongo-flashcard-audio-player',
                NIHONGO_FLASHCARD_PLUGIN_URL . 'assets/css/audio-player.css',
                array(),
                NIHONGO_FLASHCARD_VERSION
            );

            // JavaScript
            wp_enqueue_script(
                'nihongo-flashcard-audio-player',
                NIHONGO_FLASHCARD_PLUGIN_URL . 'assets/js/audio-player.js',
                array(),
                NIHONGO_FLASHCARD_VERSION,
                true
            );

            wp_enqueue_script(
                'nihongo-flashcard-frontend',
                NIHONGO_FLASHCARD_PLUGIN_URL . 'assets/js/frontend.js',
                array( 'nihongo-flashcard-audio-player' ),
                NIHONGO_FLASHCARD_VERSION,
                true
            );

            // Localize script
            wp_localize_script(
                'nihongo-flashcard-frontend',
                'nihongoFlashcard',
                array(
                    'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
                    'nonce'    => wp_create_nonce( 'nihongo_flashcard_nonce' ),
                    'i18n'     => array(
                        'card'        => __( 'Thẻ', 'nihongo-flashcard' ),
                        'of'          => __( 'của', 'nihongo-flashcard' ),
                        'shuffled'    => __( 'Đã xáo trộn!', 'nihongo-flashcard' ),
                        'completed'   => __( 'Hoàn thành!', 'nihongo-flashcard' ),
                        'loading'     => __( 'Đang tải...', 'nihongo-flashcard' ),
                        'error'       => __( 'Có lỗi xảy ra', 'nihongo-flashcard' ),
                    ),
                )
            );
        }
    }

    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets( $hook ) {
        $screen = get_current_screen();

        // Only load on our plugin pages
        if ( ! $screen || ! in_array( $screen->post_type, array( 'nihongo_deck', 'nihongo_course' ), true ) ) {
            // Check if it's the import/export page
            if ( strpos( $hook, 'nihongo-flashcard' ) === false ) {
                return;
            }
        }

        wp_enqueue_style(
            'nihongo-flashcard-admin',
            NIHONGO_FLASHCARD_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            NIHONGO_FLASHCARD_VERSION
        );

        wp_enqueue_script(
            'nihongo-flashcard-admin',
            NIHONGO_FLASHCARD_PLUGIN_URL . 'assets/js/admin.js',
            array( 'jquery', 'jquery-ui-sortable' ),
            NIHONGO_FLASHCARD_VERSION,
            true
        );

        wp_localize_script(
            'nihongo-flashcard-admin',
            'nihongoFlashcardAdmin',
            array(
                'ajaxUrl' => admin_url( 'admin-ajax.php' ),
                'nonce'   => wp_create_nonce( 'nihongo_flashcard_admin_nonce' ),
                'i18n'    => array(
                    'confirmDelete' => __( 'Bạn có chắc chắn muốn xóa?', 'nihongo-flashcard' ),
                    'saved'         => __( 'Đã lưu!', 'nihongo-flashcard' ),
                    'error'         => __( 'Có lỗi xảy ra', 'nihongo-flashcard' ),
                    'importing'     => __( 'Đang nhập...', 'nihongo-flashcard' ),
                    'exporting'     => __( 'Đang xuất...', 'nihongo-flashcard' ),
                ),
            )
        );

        // Media uploader
        wp_enqueue_media();
    }

    /**
     * Plugin activation
     */
    public function activate() {
        // Create custom post types
        Nihongo_Flashcard_Post_Type::register();
        Nihongo_Course_Post_Type::register();

        // Flush rewrite rules
        flush_rewrite_rules();

        // Create database table for progress tracking
        Nihongo_Progress_Tracker::create_table();

        // Set default options
        add_option( 'nihongo_flashcard_version', NIHONGO_FLASHCARD_VERSION );
    }

    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
    }
}

/**
 * Initialize the plugin
 *
 * @return Nihongo_Flashcard
 */
function nihongo_flashcard() {
    return Nihongo_Flashcard::get_instance();
}

// Start the plugin
nihongo_flashcard();
