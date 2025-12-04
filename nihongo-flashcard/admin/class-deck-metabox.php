<?php
/**
 * Deck Metabox
 *
 * @package NihongoFlashcard
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Nihongo_Deck_Metabox
 */
class Nihongo_Deck_Metabox {

    /**
     * Constructor
     */
    public function __construct() {
        add_action( 'add_meta_boxes', array( $this, 'add_metaboxes' ) );
        add_action( 'save_post_nihongo_deck', array( $this, 'save_metaboxes' ), 10, 2 );
    }

    /**
     * Add metaboxes
     */
    public function add_metaboxes() {
        // Audio URL metabox
        add_meta_box(
            'nihongo_deck_audio',
            __( 'Audio bộ thẻ', 'nihongo-flashcard' ),
            array( $this, 'render_audio_metabox' ),
            'nihongo_deck',
            'normal',
            'high'
        );

        // Cards metabox
        add_meta_box(
            'nihongo_deck_cards',
            __( 'Danh sách thẻ', 'nihongo-flashcard' ),
            array( $this, 'render_cards_metabox' ),
            'nihongo_deck',
            'normal',
            'high'
        );

        // Shortcode metabox
        add_meta_box(
            'nihongo_deck_shortcode',
            __( 'Shortcode', 'nihongo-flashcard' ),
            array( $this, 'render_shortcode_metabox' ),
            'nihongo_deck',
            'side',
            'high'
        );
    }

    /**
     * Render audio metabox
     *
     * @param WP_Post $post Post object.
     */
    public function render_audio_metabox( $post ) {
        wp_nonce_field( 'nihongo_deck_save', 'nihongo_deck_nonce' );

        $audio_url = get_post_meta( $post->ID, '_nihongo_audio_url', true );
        ?>
        <div class="nihongo-audio-metabox">
            <p>
                <label for="nihongo_audio_url"><?php esc_html_e( 'URL Audio (MP3, WAV):', 'nihongo-flashcard' ); ?></label>
                <input type="url" id="nihongo_audio_url" name="nihongo_audio_url" value="<?php echo esc_url( $audio_url ); ?>" class="widefat" placeholder="https://example.com/audio.mp3">
            </p>
            <p>
                <button type="button" class="button nihongo-upload-audio" data-target="#nihongo_audio_url">
                    <span class="dashicons dashicons-upload"></span>
                    <?php esc_html_e( 'Tải lên audio', 'nihongo-flashcard' ); ?>
                </button>
                <?php if ( $audio_url ) : ?>
                <button type="button" class="button nihongo-preview-audio" data-url="<?php echo esc_url( $audio_url ); ?>">
                    <span class="dashicons dashicons-controls-play"></span>
                    <?php esc_html_e( 'Nghe thử', 'nihongo-flashcard' ); ?>
                </button>
                <?php endif; ?>
            </p>
            <p class="description">
                <?php esc_html_e( 'Audio này sẽ được hiển thị với player ở phía trên bộ thẻ. Hỗ trợ MP3, WAV.', 'nihongo-flashcard' ); ?>
            </p>
        </div>
        <?php
    }

    /**
     * Render cards metabox
     *
     * @param WP_Post $post Post object.
     */
    public function render_cards_metabox( $post ) {
        $cards = Nihongo_Flashcard_Post_Type::get_cards( $post->ID );
        ?>
        <div class="nihongo-cards-metabox">
            <div class="nihongo-cards-toolbar">
                <button type="button" class="button button-primary nihongo-add-card">
                    <span class="dashicons dashicons-plus-alt2"></span>
                    <?php esc_html_e( 'Thêm thẻ mới', 'nihongo-flashcard' ); ?>
                </button>
                <span class="nihongo-card-count">
                    <?php
                    printf(
                        /* translators: %d: number of cards */
                        esc_html__( 'Tổng: %d thẻ', 'nihongo-flashcard' ),
                        count( $cards )
                    );
                    ?>
                </span>
            </div>

            <div class="nihongo-cards-list" id="nihongo-cards-list">
                <?php
                if ( ! empty( $cards ) ) {
                    foreach ( $cards as $index => $card ) {
                        $this->render_card_row( $index, $card );
                    }
                }
                ?>
            </div>

            <script type="text/template" id="nihongo-card-template">
                <?php $this->render_card_row( '{{INDEX}}', array() ); ?>
            </script>
        </div>
        <?php
    }

    /**
     * Render single card row
     *
     * @param int|string $index Card index.
     * @param array      $card  Card data.
     */
    private function render_card_row( $index, $card ) {
        $front_top    = isset( $card['front_top'] ) ? $card['front_top'] : '';
        $front_bottom = isset( $card['front_bottom'] ) ? $card['front_bottom'] : '';
        $card_audio   = isset( $card['audio_url'] ) ? $card['audio_url'] : '';
        ?>
        <div class="nihongo-card-row" data-index="<?php echo esc_attr( $index ); ?>">
            <div class="nihongo-card-handle">
                <span class="dashicons dashicons-menu"></span>
                <span class="nihongo-card-number"><?php echo is_numeric( $index ) ? esc_html( $index + 1 ) : ''; ?></span>
            </div>

            <div class="nihongo-card-fields">
                <div class="nihongo-card-field">
                    <label><?php esc_html_e( 'Phần trên (Tiếng Nhật):', 'nihongo-flashcard' ); ?></label>
                    <textarea name="nihongo_cards[<?php echo esc_attr( $index ); ?>][front_top]" rows="2" placeholder="日本語 / にほんご"><?php echo esc_textarea( $front_top ); ?></textarea>
                </div>

                <div class="nihongo-card-field">
                    <label><?php esc_html_e( 'Phần dưới (Nghĩa, phiên âm, ví dụ):', 'nihongo-flashcard' ); ?></label>
                    <textarea name="nihongo_cards[<?php echo esc_attr( $index ); ?>][front_bottom]" rows="3" placeholder="Tiếng Nhật / Nihongo&#10;Ví dụ: 日本語を勉強します"><?php echo esc_textarea( $front_bottom ); ?></textarea>
                </div>

                <div class="nihongo-card-field nihongo-card-audio-field">
                    <label><?php esc_html_e( 'Audio riêng (tùy chọn):', 'nihongo-flashcard' ); ?></label>
                    <input type="url" name="nihongo_cards[<?php echo esc_attr( $index ); ?>][audio_url]" value="<?php echo esc_url( $card_audio ); ?>" placeholder="https://example.com/word-audio.mp3">
                </div>
            </div>

            <div class="nihongo-card-actions">
                <button type="button" class="button nihongo-delete-card" title="<?php esc_attr_e( 'Xóa thẻ', 'nihongo-flashcard' ); ?>">
                    <span class="dashicons dashicons-trash"></span>
                </button>
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
            echo '<p>' . esc_html__( 'Lưu bộ thẻ để xem shortcode.', 'nihongo-flashcard' ) . '</p>';
            return;
        }
        ?>
        <div class="nihongo-shortcode-box">
            <p><?php esc_html_e( 'Sử dụng shortcode sau để hiển thị bộ thẻ:', 'nihongo-flashcard' ); ?></p>
            <code class="nihongo-shortcode-display">[nihongo_deck id="<?php echo esc_attr( $post->ID ); ?>"]</code>
            <button type="button" class="button nihongo-copy-shortcode" data-shortcode='[nihongo_deck id="<?php echo esc_attr( $post->ID ); ?>"]'>
                <span class="dashicons dashicons-clipboard"></span>
                <?php esc_html_e( 'Sao chép', 'nihongo-flashcard' ); ?>
            </button>
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
        if ( ! isset( $_POST['nihongo_deck_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nihongo_deck_nonce'] ) ), 'nihongo_deck_save' ) ) {
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

        // Save audio URL
        if ( isset( $_POST['nihongo_audio_url'] ) ) {
            update_post_meta( $post_id, '_nihongo_audio_url', sanitize_url( wp_unslash( $_POST['nihongo_audio_url'] ) ) );
        }

        // Save cards
        if ( isset( $_POST['nihongo_cards'] ) && is_array( $_POST['nihongo_cards'] ) ) {
            $cards = array();
            foreach ( $_POST['nihongo_cards'] as $card ) {
                // Skip empty cards
                $front_top    = isset( $card['front_top'] ) ? sanitize_textarea_field( wp_unslash( $card['front_top'] ) ) : '';
                $front_bottom = isset( $card['front_bottom'] ) ? sanitize_textarea_field( wp_unslash( $card['front_bottom'] ) ) : '';

                if ( empty( $front_top ) && empty( $front_bottom ) ) {
                    continue;
                }

                $cards[] = array(
                    'front_top'    => $front_top,
                    'front_bottom' => $front_bottom,
                    'audio_url'    => isset( $card['audio_url'] ) ? sanitize_url( wp_unslash( $card['audio_url'] ) ) : '',
                );
            }

            update_post_meta( $post_id, '_nihongo_cards', $cards );
        } else {
            // If no cards submitted, save empty array
            update_post_meta( $post_id, '_nihongo_cards', array() );
        }
    }
}

// Initialize the class
new Nihongo_Deck_Metabox();
