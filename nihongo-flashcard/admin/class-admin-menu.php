<?php
/**
 * Admin Menu
 *
 * @package NihongoFlashcard
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Nihongo_Admin_Menu
 */
class Nihongo_Admin_Menu {

    /**
     * Constructor
     */
    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_menu' ) );
    }

    /**
     * Add admin menu
     */
    public function add_menu() {
        // Main menu
        add_menu_page(
            __( 'Nihongo Flashcard', 'nihongo-flashcard' ),
            __( 'Nihongo Flashcard', 'nihongo-flashcard' ),
            'manage_options',
            'nihongo-flashcard',
            array( $this, 'render_dashboard' ),
            'dashicons-welcome-learn-more',
            30
        );

        // Dashboard submenu
        add_submenu_page(
            'nihongo-flashcard',
            __( 'Bảng điều khiển', 'nihongo-flashcard' ),
            __( 'Bảng điều khiển', 'nihongo-flashcard' ),
            'manage_options',
            'nihongo-flashcard',
            array( $this, 'render_dashboard' )
        );

        // Import/Export submenu
        add_submenu_page(
            'nihongo-flashcard',
            __( 'Nhập/Xuất CSV', 'nihongo-flashcard' ),
            __( 'Nhập/Xuất CSV', 'nihongo-flashcard' ),
            'manage_options',
            'nihongo-flashcard-import-export',
            array( $this, 'render_import_export' )
        );

        // Help submenu
        add_submenu_page(
            'nihongo-flashcard',
            __( 'Hướng dẫn', 'nihongo-flashcard' ),
            __( 'Hướng dẫn', 'nihongo-flashcard' ),
            'manage_options',
            'nihongo-flashcard-help',
            array( $this, 'render_help' )
        );
    }

    /**
     * Render dashboard page
     */
    public function render_dashboard() {
        // Get stats
        $deck_count   = wp_count_posts( 'nihongo_deck' )->publish;
        $course_count = wp_count_posts( 'nihongo_course' )->publish;

        // Get total cards
        $total_cards = 0;
        $decks       = get_posts(
            array(
                'post_type'      => 'nihongo_deck',
                'posts_per_page' => -1,
                'post_status'    => 'publish',
            )
        );
        foreach ( $decks as $deck ) {
            $total_cards += Nihongo_Flashcard_Post_Type::get_card_count( $deck->ID );
        }
        ?>
        <div class="wrap nihongo-dashboard">
            <h1><?php esc_html_e( 'Nihongo Flashcard - Bảng điều khiển', 'nihongo-flashcard' ); ?></h1>

            <div class="nihongo-stats-grid">
                <div class="nihongo-stat-card">
                    <span class="dashicons dashicons-index-card"></span>
                    <div class="nihongo-stat-content">
                        <h3><?php echo esc_html( $deck_count ); ?></h3>
                        <p><?php esc_html_e( 'Bộ thẻ', 'nihongo-flashcard' ); ?></p>
                    </div>
                </div>

                <div class="nihongo-stat-card">
                    <span class="dashicons dashicons-book"></span>
                    <div class="nihongo-stat-content">
                        <h3><?php echo esc_html( $course_count ); ?></h3>
                        <p><?php esc_html_e( 'Khóa học', 'nihongo-flashcard' ); ?></p>
                    </div>
                </div>

                <div class="nihongo-stat-card">
                    <span class="dashicons dashicons-media-default"></span>
                    <div class="nihongo-stat-content">
                        <h3><?php echo esc_html( $total_cards ); ?></h3>
                        <p><?php esc_html_e( 'Thẻ từ vựng', 'nihongo-flashcard' ); ?></p>
                    </div>
                </div>
            </div>

            <div class="nihongo-quick-actions">
                <h2><?php esc_html_e( 'Hành động nhanh', 'nihongo-flashcard' ); ?></h2>
                <div class="nihongo-actions-grid">
                    <a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=nihongo_deck' ) ); ?>" class="button button-primary button-hero">
                        <span class="dashicons dashicons-plus-alt"></span>
                        <?php esc_html_e( 'Tạo bộ thẻ mới', 'nihongo-flashcard' ); ?>
                    </a>
                    <a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=nihongo_course' ) ); ?>" class="button button-secondary button-hero">
                        <span class="dashicons dashicons-plus-alt"></span>
                        <?php esc_html_e( 'Tạo khóa học mới', 'nihongo-flashcard' ); ?>
                    </a>
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=nihongo-flashcard-import-export' ) ); ?>" class="button button-secondary button-hero">
                        <span class="dashicons dashicons-upload"></span>
                        <?php esc_html_e( 'Nhập/Xuất CSV', 'nihongo-flashcard' ); ?>
                    </a>
                </div>
            </div>

            <div class="nihongo-shortcode-info">
                <h2><?php esc_html_e( 'Cách sử dụng Shortcode', 'nihongo-flashcard' ); ?></h2>
                <table class="widefat">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'Shortcode', 'nihongo-flashcard' ); ?></th>
                            <th><?php esc_html_e( 'Mô tả', 'nihongo-flashcard' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>[nihongo_deck id="X"]</code></td>
                            <td><?php esc_html_e( 'Hiển thị bộ thẻ với ID là X', 'nihongo-flashcard' ); ?></td>
                        </tr>
                        <tr>
                            <td><code>[nihongo_course id="X"]</code></td>
                            <td><?php esc_html_e( 'Hiển thị khóa học với ID là X (grid các bộ thẻ)', 'nihongo-flashcard' ); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }

    /**
     * Render import/export page
     */
    public function render_import_export() {
        include NIHONGO_FLASHCARD_PLUGIN_DIR . 'admin/views/import-export.php';
    }

    /**
     * Render help page
     */
    public function render_help() {
        ?>
        <div class="wrap nihongo-help">
            <h1><?php esc_html_e( 'Hướng dẫn sử dụng Nihongo Flashcard', 'nihongo-flashcard' ); ?></h1>

            <div class="nihongo-help-section">
                <h2><?php esc_html_e( '1. Tạo bộ thẻ (Deck)', 'nihongo-flashcard' ); ?></h2>
                <ol>
                    <li><?php esc_html_e( 'Vào menu "Nihongo Flashcard" > "Bộ thẻ" > "Thêm mới"', 'nihongo-flashcard' ); ?></li>
                    <li><?php esc_html_e( 'Nhập tên bộ thẻ', 'nihongo-flashcard' ); ?></li>
                    <li><?php esc_html_e( 'Thêm URL audio (nếu có)', 'nihongo-flashcard' ); ?></li>
                    <li><?php esc_html_e( 'Thêm các thẻ từ vựng với phần trên (tiếng Nhật) và phần dưới (nghĩa, phiên âm)', 'nihongo-flashcard' ); ?></li>
                    <li><?php esc_html_e( 'Lưu bộ thẻ', 'nihongo-flashcard' ); ?></li>
                </ol>
            </div>

            <div class="nihongo-help-section">
                <h2><?php esc_html_e( '2. Tạo khóa học (Course)', 'nihongo-flashcard' ); ?></h2>
                <ol>
                    <li><?php esc_html_e( 'Vào menu "Nihongo Flashcard" > "Khóa học" > "Thêm mới"', 'nihongo-flashcard' ); ?></li>
                    <li><?php esc_html_e( 'Nhập tên khóa học', 'nihongo-flashcard' ); ?></li>
                    <li><?php esc_html_e( 'Chọn các bộ thẻ muốn đưa vào khóa học', 'nihongo-flashcard' ); ?></li>
                    <li><?php esc_html_e( 'Kéo thả để sắp xếp thứ tự', 'nihongo-flashcard' ); ?></li>
                    <li><?php esc_html_e( 'Lưu khóa học', 'nihongo-flashcard' ); ?></li>
                </ol>
            </div>

            <div class="nihongo-help-section">
                <h2><?php esc_html_e( '3. Nhập/Xuất CSV', 'nihongo-flashcard' ); ?></h2>
                <p><?php esc_html_e( 'Cấu trúc file CSV:', 'nihongo-flashcard' ); ?></p>
                <ul>
                    <li><strong>front_top:</strong> <?php esc_html_e( 'Phần trên thẻ (từ vựng tiếng Nhật)', 'nihongo-flashcard' ); ?></li>
                    <li><strong>front_bottom:</strong> <?php esc_html_e( 'Phần dưới thẻ (nghĩa, phiên âm, ví dụ)', 'nihongo-flashcard' ); ?></li>
                    <li><strong>audio_url:</strong> <?php esc_html_e( 'URL file audio (không bắt buộc)', 'nihongo-flashcard' ); ?></li>
                </ul>
                <p><strong><?php esc_html_e( 'Lưu ý:', 'nihongo-flashcard' ); ?></strong> <?php esc_html_e( 'File CSV phải được lưu với encoding UTF-8 BOM để không bị lỗi font tiếng Nhật, tiếng Việt.', 'nihongo-flashcard' ); ?></p>
            </div>

            <div class="nihongo-help-section">
                <h2><?php esc_html_e( '4. Sử dụng Shortcode', 'nihongo-flashcard' ); ?></h2>
                <p><?php esc_html_e( 'Chèn shortcode vào bài viết hoặc trang để hiển thị:', 'nihongo-flashcard' ); ?></p>
                <ul>
                    <li><code>[nihongo_deck id="123"]</code> - <?php esc_html_e( 'Hiển thị bộ thẻ ID 123', 'nihongo-flashcard' ); ?></li>
                    <li><code>[nihongo_course id="456"]</code> - <?php esc_html_e( 'Hiển thị khóa học ID 456', 'nihongo-flashcard' ); ?></li>
                </ul>
            </div>

            <div class="nihongo-help-section">
                <h2><?php esc_html_e( '5. Phím tắt học thẻ', 'nihongo-flashcard' ); ?></h2>
                <ul>
                    <li><kbd>←</kbd> <?php esc_html_e( 'Thẻ trước', 'nihongo-flashcard' ); ?></li>
                    <li><kbd>→</kbd> <?php esc_html_e( 'Thẻ sau', 'nihongo-flashcard' ); ?></li>
                    <li><kbd>Space</kbd> <?php esc_html_e( 'Lật thẻ', 'nihongo-flashcard' ); ?></li>
                    <li><kbd>S</kbd> <?php esc_html_e( 'Xáo trộn', 'nihongo-flashcard' ); ?></li>
                </ul>
            </div>
        </div>
        <?php
    }
}

// Initialize the class
new Nihongo_Admin_Menu();
