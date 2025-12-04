<?php
/**
 * Flashcard (Deck) Custom Post Type
 *
 * @package NihongoFlashcard
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Nihongo_Flashcard_Post_Type
 */
class Nihongo_Flashcard_Post_Type {

    /**
     * Constructor
     */
    public function __construct() {
        add_action( 'init', array( __CLASS__, 'register' ) );
    }

    /**
     * Register custom post type
     */
    public static function register() {
        $labels = array(
            'name'                  => _x( 'Bộ thẻ', 'Post type general name', 'nihongo-flashcard' ),
            'singular_name'         => _x( 'Bộ thẻ', 'Post type singular name', 'nihongo-flashcard' ),
            'menu_name'             => _x( 'Bộ thẻ', 'Admin Menu text', 'nihongo-flashcard' ),
            'name_admin_bar'        => _x( 'Bộ thẻ', 'Add New on Toolbar', 'nihongo-flashcard' ),
            'add_new'               => __( 'Thêm mới', 'nihongo-flashcard' ),
            'add_new_item'          => __( 'Thêm bộ thẻ mới', 'nihongo-flashcard' ),
            'new_item'              => __( 'Bộ thẻ mới', 'nihongo-flashcard' ),
            'edit_item'             => __( 'Sửa bộ thẻ', 'nihongo-flashcard' ),
            'view_item'             => __( 'Xem bộ thẻ', 'nihongo-flashcard' ),
            'all_items'             => __( 'Tất cả bộ thẻ', 'nihongo-flashcard' ),
            'search_items'          => __( 'Tìm kiếm bộ thẻ', 'nihongo-flashcard' ),
            'parent_item_colon'     => __( 'Bộ thẻ cha:', 'nihongo-flashcard' ),
            'not_found'             => __( 'Không tìm thấy bộ thẻ nào.', 'nihongo-flashcard' ),
            'not_found_in_trash'    => __( 'Không có bộ thẻ nào trong thùng rác.', 'nihongo-flashcard' ),
            'featured_image'        => _x( 'Ảnh đại diện', 'Overrides the "Featured Image" phrase', 'nihongo-flashcard' ),
            'set_featured_image'    => _x( 'Đặt ảnh đại diện', 'Overrides the "Set featured image" phrase', 'nihongo-flashcard' ),
            'remove_featured_image' => _x( 'Xóa ảnh đại diện', 'Overrides the "Remove featured image" phrase', 'nihongo-flashcard' ),
            'use_featured_image'    => _x( 'Dùng làm ảnh đại diện', 'Overrides the "Use as featured image" phrase', 'nihongo-flashcard' ),
            'archives'              => _x( 'Lưu trữ bộ thẻ', 'The post type archive label', 'nihongo-flashcard' ),
            'insert_into_item'      => _x( 'Chèn vào bộ thẻ', 'Overrides the "Insert into post" phrase', 'nihongo-flashcard' ),
            'uploaded_to_this_item' => _x( 'Tải lên bộ thẻ này', 'Overrides the "Uploaded to this post" phrase', 'nihongo-flashcard' ),
            'filter_items_list'     => _x( 'Lọc danh sách bộ thẻ', 'Screen reader text', 'nihongo-flashcard' ),
            'items_list_navigation' => _x( 'Điều hướng danh sách bộ thẻ', 'Screen reader text', 'nihongo-flashcard' ),
            'items_list'            => _x( 'Danh sách bộ thẻ', 'Screen reader text', 'nihongo-flashcard' ),
        );

        $args = array(
            'labels'             => $labels,
            'public'             => false,
            'publicly_queryable' => false,
            'show_ui'            => true,
            'show_in_menu'       => 'nihongo-flashcard',
            'query_var'          => true,
            'rewrite'            => array( 'slug' => 'nihongo-deck' ),
            'capability_type'    => 'post',
            'has_archive'        => false,
            'hierarchical'       => false,
            'menu_position'      => null,
            'supports'           => array( 'title', 'thumbnail' ),
            'show_in_rest'       => true,
        );

        register_post_type( 'nihongo_deck', $args );
    }

    /**
     * Get deck cards
     *
     * @param int $deck_id Deck ID.
     * @return array
     */
    public static function get_cards( $deck_id ) {
        $cards = get_post_meta( $deck_id, '_nihongo_cards', true );
        return is_array( $cards ) ? $cards : array();
    }

    /**
     * Save deck cards
     *
     * @param int   $deck_id Deck ID.
     * @param array $cards   Cards data.
     * @return bool
     */
    public static function save_cards( $deck_id, $cards ) {
        return update_post_meta( $deck_id, '_nihongo_cards', $cards );
    }

    /**
     * Get deck audio URL
     *
     * @param int $deck_id Deck ID.
     * @return string
     */
    public static function get_audio_url( $deck_id ) {
        return get_post_meta( $deck_id, '_nihongo_audio_url', true );
    }

    /**
     * Save deck audio URL
     *
     * @param int    $deck_id   Deck ID.
     * @param string $audio_url Audio URL.
     * @return bool
     */
    public static function save_audio_url( $deck_id, $audio_url ) {
        return update_post_meta( $deck_id, '_nihongo_audio_url', sanitize_url( $audio_url ) );
    }

    /**
     * Get card count
     *
     * @param int $deck_id Deck ID.
     * @return int
     */
    public static function get_card_count( $deck_id ) {
        $cards = self::get_cards( $deck_id );
        return count( $cards );
    }
}

// Initialize the class
new Nihongo_Flashcard_Post_Type();
