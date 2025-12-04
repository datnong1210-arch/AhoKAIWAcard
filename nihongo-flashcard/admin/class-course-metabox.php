<?php
/**
 * Course Metabox
 *
 * @package NihongoFlashcard
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Nihongo_Course_Metabox
 */
class Nihongo_Course_Metabox {

    /**
     * Constructor
     */
    public function __construct() {
        add_action( 'add_meta_boxes', array( $this, 'add_metaboxes' ) );
        add_action( 'save_post_nihongo_course', array( $this, 'save_metaboxes' ), 10, 2 );
    }

    /**
     * Add metaboxes
     */
    public function add_metaboxes() {
        // Decks selection metabox
        add_meta_box(
            'nihongo_course_decks',
            __( 'Các bộ thẻ trong khóa học', 'nihongo-flashcard' ),
            array( $this, 'render_decks_metabox' ),
            'nihongo_course',
            'normal',
            'high'
        );

        // Shortcode metabox
        add_meta_box(
            'nihongo_course_shortcode',
            __( 'Shortcode', 'nihongo-flashcard' ),
            array( $this, 'render_shortcode_metabox' ),
            'nihongo_course',
            'side',
            'high'
        );

        // Stats metabox
        add_meta_box(
            'nihongo_course_stats',
            __( 'Thống kê', 'nihongo-flashcard' ),
            array( $this, 'render_stats_metabox' ),
            'nihongo_course',
            'side',
            'default'
        );
    }

    /**
     * Render decks metabox
     *
     * @param WP_Post $post Post object.
     */
    public function render_decks_metabox( $post ) {
        wp_nonce_field( 'nihongo_course_save', 'nihongo_course_nonce' );

        $selected_decks = Nihongo_Course_Post_Type::get_decks( $post->ID );

        // Get all available decks
        $all_decks = get_posts(
            array(
                'post_type'      => 'nihongo_deck',
                'posts_per_page' => -1,
                'post_status'    => 'publish',
                'orderby'        => 'title',
                'order'          => 'ASC',
            )
        );
        ?>
        <div class="nihongo-course-decks-metabox">
            <div class="nihongo-course-decks-toolbar">
                <p class="description">
                    <?php esc_html_e( 'Chọn các bộ thẻ để thêm vào khóa học. Kéo thả để sắp xếp thứ tự.', 'nihongo-flashcard' ); ?>
                </p>
            </div>

            <div class="nihongo-course-decks-container">
                <!-- Available decks -->
                <div class="nihongo-available-decks">
                    <h4><?php esc_html_e( 'Bộ thẻ có sẵn', 'nihongo-flashcard' ); ?></h4>
                    <div class="nihongo-deck-search">
                        <input type="text" id="nihongo-deck-search" placeholder="<?php esc_attr_e( 'Tìm kiếm...', 'nihongo-flashcard' ); ?>">
                    </div>
                    <div class="nihongo-deck-list" id="nihongo-available-decks">
                        <?php foreach ( $all_decks as $deck ) : ?>
                            <?php if ( ! in_array( $deck->ID, $selected_decks, true ) ) : ?>
                                <div class="nihongo-deck-item" data-id="<?php echo esc_attr( $deck->ID ); ?>">
                                    <span class="dashicons dashicons-index-card"></span>
                                    <span class="nihongo-deck-title"><?php echo esc_html( $deck->post_title ); ?></span>
                                    <span class="nihongo-deck-card-count">
                                        <?php
                                        printf(
                                            /* translators: %d: number of cards */
                                            esc_html__( '(%d thẻ)', 'nihongo-flashcard' ),
                                            Nihongo_Flashcard_Post_Type::get_card_count( $deck->ID )
                                        );
                                        ?>
                                    </span>
                                    <button type="button" class="button nihongo-add-deck-to-course" title="<?php esc_attr_e( 'Thêm vào khóa học', 'nihongo-flashcard' ); ?>">
                                        <span class="dashicons dashicons-plus"></span>
                                    </button>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Selected decks -->
                <div class="nihongo-selected-decks">
                    <h4><?php esc_html_e( 'Bộ thẻ đã chọn', 'nihongo-flashcard' ); ?></h4>
                    <div class="nihongo-deck-list nihongo-sortable" id="nihongo-selected-decks">
                        <?php foreach ( $selected_decks as $deck_id ) : ?>
                            <?php
                            $deck = get_post( $deck_id );
                            if ( ! $deck ) {
                                continue;
                            }
                            ?>
                            <div class="nihongo-deck-item" data-id="<?php echo esc_attr( $deck_id ); ?>">
                                <span class="nihongo-deck-handle dashicons dashicons-menu"></span>
                                <span class="dashicons dashicons-index-card"></span>
                                <span class="nihongo-deck-title"><?php echo esc_html( $deck->post_title ); ?></span>
                                <span class="nihongo-deck-card-count">
                                    <?php
                                    printf(
                                        /* translators: %d: number of cards */
                                        esc_html__( '(%d thẻ)', 'nihongo-flashcard' ),
                                        Nihongo_Flashcard_Post_Type::get_card_count( $deck_id )
                                    );
                                    ?>
                                </span>
                                <input type="hidden" name="nihongo_course_decks[]" value="<?php echo esc_attr( $deck_id ); ?>">
                                <button type="button" class="button nihongo-remove-deck-from-course" title="<?php esc_attr_e( 'Xóa khỏi khóa học', 'nihongo-flashcard' ); ?>">
                                    <span class="dashicons dashicons-minus"></span>
                                </button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php if ( empty( $selected_decks ) ) : ?>
                        <p class="nihongo-no-decks-selected"><?php esc_html_e( 'Chưa có bộ thẻ nào được chọn.', 'nihongo-flashcard' ); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Render shortcode metabox
     *
     * @param WP_Post $post Post object.
     */
    public function render_shortcode_metabox( $post ) {
        if ( 'auto-draft' === $post->post_status ) {
            echo '<p>' . esc_html__( 'Lưu khóa học để xem shortcode.', 'nihongo-flashcard' ) . '</p>';
            return;
        }
        ?>
        <div class="nihongo-shortcode-box">
            <p><?php esc_html_e( 'Sử dụng shortcode sau để hiển thị khóa học:', 'nihongo-flashcard' ); ?></p>
            <code class="nihongo-shortcode-display">[nihongo_course id="<?php echo esc_attr( $post->ID ); ?>"]</code>
            <button type="button" class="button nihongo-copy-shortcode" data-shortcode='[nihongo_course id="<?php echo esc_attr( $post->ID ); ?>"]'>
                <span class="dashicons dashicons-clipboard"></span>
                <?php esc_html_e( 'Sao chép', 'nihongo-flashcard' ); ?>
            </button>
        </div>
        <?php
    }

    /**
     * Render stats metabox
     *
     * @param WP_Post $post Post object.
     */
    public function render_stats_metabox( $post ) {
        $deck_ids    = Nihongo_Course_Post_Type::get_decks( $post->ID );
        $total_decks = count( $deck_ids );
        $total_cards = Nihongo_Course_Post_Type::get_total_cards( $post->ID );
        ?>
        <div class="nihongo-stats-box">
            <p>
                <strong><?php esc_html_e( 'Số bộ thẻ:', 'nihongo-flashcard' ); ?></strong>
                <?php echo esc_html( $total_decks ); ?>
            </p>
            <p>
                <strong><?php esc_html_e( 'Tổng số thẻ:', 'nihongo-flashcard' ); ?></strong>
                <?php echo esc_html( $total_cards ); ?>
            </p>
        </div>
        <?php
    }

    /**
     * Save metaboxes
     *
     * @param int     $post_id Post ID.
     * @param WP_Post $post    Post object.
     */
    public function save_metaboxes( $post_id, $post ) {
        // Verify nonce
        if ( ! isset( $_POST['nihongo_course_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nihongo_course_nonce'] ) ), 'nihongo_course_save' ) ) {
            return;
        }

        // Check autosave
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        // Check permissions
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        // Save deck IDs
        if ( isset( $_POST['nihongo_course_decks'] ) && is_array( $_POST['nihongo_course_decks'] ) ) {
            $deck_ids = array_map( 'intval', $_POST['nihongo_course_decks'] );
            // Filter out invalid IDs
            $deck_ids = array_filter(
                $deck_ids,
                function ( $id ) {
                    $deck = get_post( $id );
                    return $deck && 'nihongo_deck' === $deck->post_type;
                }
            );
            update_post_meta( $post_id, '_nihongo_course_decks', array_values( $deck_ids ) );
        } else {
            update_post_meta( $post_id, '_nihongo_course_decks', array() );
        }
    }
}

// Initialize the class
new Nihongo_Course_Metabox();
