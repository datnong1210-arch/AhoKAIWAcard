<?php
/**
 * AJAX Handler
 *
 * @package NihongoFlashcard
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Nihongo_Ajax_Handler
 */
class Nihongo_Ajax_Handler {

    /**
     * Constructor
     */
    public function __construct() {
        // Frontend AJAX
        add_action( 'wp_ajax_nihongo_save_progress', array( $this, 'save_progress' ) );
        add_action( 'wp_ajax_nopriv_nihongo_save_progress', array( $this, 'save_progress_guest' ) );

        add_action( 'wp_ajax_nihongo_get_deck_cards', array( $this, 'get_deck_cards' ) );
        add_action( 'wp_ajax_nopriv_nihongo_get_deck_cards', array( $this, 'get_deck_cards' ) );

        // Admin AJAX
        add_action( 'wp_ajax_nihongo_save_cards', array( $this, 'save_cards' ) );
        add_action( 'wp_ajax_nihongo_delete_card', array( $this, 'delete_card' ) );
        add_action( 'wp_ajax_nihongo_reorder_cards', array( $this, 'reorder_cards' ) );
    }

    /**
     * Save user progress
     */
    public function save_progress() {
        // Verify nonce
        if ( ! check_ajax_referer( 'nihongo_flashcard_nonce', 'nonce', false ) ) {
            wp_send_json_error( array( 'message' => __( 'Xác thực thất bại.', 'nihongo-flashcard' ) ) );
        }

        $user_id  = get_current_user_id();
        $deck_id  = isset( $_POST['deck_id'] ) ? intval( $_POST['deck_id'] ) : 0;
        $card_idx = isset( $_POST['card_index'] ) ? intval( $_POST['card_index'] ) : 0;
        $learned  = isset( $_POST['learned'] ) ? (bool) $_POST['learned'] : false;

        if ( ! $user_id || ! $deck_id ) {
            wp_send_json_error( array( 'message' => __( 'Dữ liệu không hợp lệ.', 'nihongo-flashcard' ) ) );
        }

        // Save progress
        $result = Nihongo_Progress_Tracker::mark_card( $user_id, $deck_id, $card_idx, $learned );

        if ( $result ) {
            $progress = Nihongo_Progress_Tracker::get_deck_progress( $user_id, $deck_id );
            wp_send_json_success(
                array(
                    'message'  => __( 'Đã lưu tiến độ.', 'nihongo-flashcard' ),
                    'progress' => $progress,
                )
            );
        } else {
            wp_send_json_error( array( 'message' => __( 'Lỗi khi lưu tiến độ.', 'nihongo-flashcard' ) ) );
        }
    }

    /**
     * Guest progress (store in session/local storage only)
     */
    public function save_progress_guest() {
        wp_send_json_success(
            array(
                'message' => __( 'Tiến độ được lưu tạm thời. Đăng nhập để lưu vĩnh viễn.', 'nihongo-flashcard' ),
                'guest'   => true,
            )
        );
    }

    /**
     * Get deck cards
     */
    public function get_deck_cards() {
        // Verify nonce
        if ( ! check_ajax_referer( 'nihongo_flashcard_nonce', 'nonce', false ) ) {
            wp_send_json_error( array( 'message' => __( 'Xác thực thất bại.', 'nihongo-flashcard' ) ) );
        }

        $deck_id = isset( $_POST['deck_id'] ) ? intval( $_POST['deck_id'] ) : 0;

        if ( ! $deck_id ) {
            wp_send_json_error( array( 'message' => __( 'ID bộ thẻ không hợp lệ.', 'nihongo-flashcard' ) ) );
        }

        $cards     = Nihongo_Flashcard_Post_Type::get_cards( $deck_id );
        $audio_url = Nihongo_Flashcard_Post_Type::get_audio_url( $deck_id );

        wp_send_json_success(
            array(
                'cards'     => $cards,
                'audio_url' => $audio_url,
            )
        );
    }

    /**
     * Save cards (admin)
     */
    public function save_cards() {
        // Verify nonce
        if ( ! check_ajax_referer( 'nihongo_flashcard_admin_nonce', 'nonce', false ) ) {
            wp_send_json_error( array( 'message' => __( 'Xác thực thất bại.', 'nihongo-flashcard' ) ) );
        }

        // Check permissions
        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_send_json_error( array( 'message' => __( 'Bạn không có quyền.', 'nihongo-flashcard' ) ) );
        }

        $deck_id = isset( $_POST['deck_id'] ) ? intval( $_POST['deck_id'] ) : 0;
        $cards   = isset( $_POST['cards'] ) ? $_POST['cards'] : array();

        if ( ! $deck_id ) {
            wp_send_json_error( array( 'message' => __( 'ID bộ thẻ không hợp lệ.', 'nihongo-flashcard' ) ) );
        }

        // Sanitize cards
        $sanitized_cards = array();
        foreach ( $cards as $card ) {
            $sanitized_cards[] = array(
                'front_top'    => isset( $card['front_top'] ) ? sanitize_textarea_field( wp_unslash( $card['front_top'] ) ) : '',
                'front_bottom' => isset( $card['front_bottom'] ) ? sanitize_textarea_field( wp_unslash( $card['front_bottom'] ) ) : '',
                'audio_url'    => isset( $card['audio_url'] ) ? sanitize_url( $card['audio_url'] ) : '',
            );
        }

        // Save cards
        $result = Nihongo_Flashcard_Post_Type::save_cards( $deck_id, $sanitized_cards );

        if ( $result !== false ) {
            wp_send_json_success( array( 'message' => __( 'Đã lưu thành công!', 'nihongo-flashcard' ) ) );
        } else {
            wp_send_json_error( array( 'message' => __( 'Lỗi khi lưu.', 'nihongo-flashcard' ) ) );
        }
    }

    /**
     * Delete card (admin)
     */
    public function delete_card() {
        // Verify nonce
        if ( ! check_ajax_referer( 'nihongo_flashcard_admin_nonce', 'nonce', false ) ) {
            wp_send_json_error( array( 'message' => __( 'Xác thực thất bại.', 'nihongo-flashcard' ) ) );
        }

        // Check permissions
        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_send_json_error( array( 'message' => __( 'Bạn không có quyền.', 'nihongo-flashcard' ) ) );
        }

        $deck_id   = isset( $_POST['deck_id'] ) ? intval( $_POST['deck_id'] ) : 0;
        $card_idx  = isset( $_POST['card_index'] ) ? intval( $_POST['card_index'] ) : -1;

        if ( ! $deck_id || $card_idx < 0 ) {
            wp_send_json_error( array( 'message' => __( 'Dữ liệu không hợp lệ.', 'nihongo-flashcard' ) ) );
        }

        $cards = Nihongo_Flashcard_Post_Type::get_cards( $deck_id );

        if ( ! isset( $cards[ $card_idx ] ) ) {
            wp_send_json_error( array( 'message' => __( 'Thẻ không tồn tại.', 'nihongo-flashcard' ) ) );
        }

        // Remove card
        array_splice( $cards, $card_idx, 1 );

        // Save cards
        Nihongo_Flashcard_Post_Type::save_cards( $deck_id, $cards );

        wp_send_json_success( array( 'message' => __( 'Đã xóa thẻ.', 'nihongo-flashcard' ) ) );
    }

    /**
     * Reorder cards (admin)
     */
    public function reorder_cards() {
        // Verify nonce
        if ( ! check_ajax_referer( 'nihongo_flashcard_admin_nonce', 'nonce', false ) ) {
            wp_send_json_error( array( 'message' => __( 'Xác thực thất bại.', 'nihongo-flashcard' ) ) );
        }

        // Check permissions
        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_send_json_error( array( 'message' => __( 'Bạn không có quyền.', 'nihongo-flashcard' ) ) );
        }

        $deck_id = isset( $_POST['deck_id'] ) ? intval( $_POST['deck_id'] ) : 0;
        $order   = isset( $_POST['order'] ) ? array_map( 'intval', $_POST['order'] ) : array();

        if ( ! $deck_id || empty( $order ) ) {
            wp_send_json_error( array( 'message' => __( 'Dữ liệu không hợp lệ.', 'nihongo-flashcard' ) ) );
        }

        $cards = Nihongo_Flashcard_Post_Type::get_cards( $deck_id );

        // Reorder cards based on new order
        $new_cards = array();
        foreach ( $order as $idx ) {
            if ( isset( $cards[ $idx ] ) ) {
                $new_cards[] = $cards[ $idx ];
            }
        }

        // Save cards
        Nihongo_Flashcard_Post_Type::save_cards( $deck_id, $new_cards );

        wp_send_json_success( array( 'message' => __( 'Đã sắp xếp lại.', 'nihongo-flashcard' ) ) );
    }
}

// Initialize the class
new Nihongo_Ajax_Handler();
