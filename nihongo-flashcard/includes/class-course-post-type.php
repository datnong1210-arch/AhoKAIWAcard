<?php
/**
 * Course Custom Post Type
 *
 * @package NihongoFlashcard
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Nihongo_Course_Post_Type
 */
class Nihongo_Course_Post_Type {

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
            'name'                  => _x( 'Khóa học', 'Post type general name', 'nihongo-flashcard' ),
            'singular_name'         => _x( 'Khóa học', 'Post type singular name', 'nihongo-flashcard' ),
            'menu_name'             => _x( 'Khóa học', 'Admin Menu text', 'nihongo-flashcard' ),
            'name_admin_bar'        => _x( 'Khóa học', 'Add New on Toolbar', 'nihongo-flashcard' ),
            'add_new'               => __( 'Thêm mới', 'nihongo-flashcard' ),
            'add_new_item'          => __( 'Thêm khóa học mới', 'nihongo-flashcard' ),
            'new_item'              => __( 'Khóa học mới', 'nihongo-flashcard' ),
            'edit_item'             => __( 'Sửa khóa học', 'nihongo-flashcard' ),
            'view_item'             => __( 'Xem khóa học', 'nihongo-flashcard' ),
            'all_items'             => __( 'Tất cả khóa học', 'nihongo-flashcard' ),
            'search_items'          => __( 'Tìm kiếm khóa học', 'nihongo-flashcard' ),
            'parent_item_colon'     => __( 'Khóa học cha:', 'nihongo-flashcard' ),
            'not_found'             => __( 'Không tìm thấy khóa học nào.', 'nihongo-flashcard' ),
            'not_found_in_trash'    => __( 'Không có khóa học nào trong thùng rác.', 'nihongo-flashcard' ),
            'featured_image'        => _x( 'Ảnh đại diện', 'Overrides the "Featured Image" phrase', 'nihongo-flashcard' ),
            'set_featured_image'    => _x( 'Đặt ảnh đại diện', 'Overrides the "Set featured image" phrase', 'nihongo-flashcard' ),
            'remove_featured_image' => _x( 'Xóa ảnh đại diện', 'Overrides the "Remove featured image" phrase', 'nihongo-flashcard' ),
            'use_featured_image'    => _x( 'Dùng làm ảnh đại diện', 'Overrides the "Use as featured image" phrase', 'nihongo-flashcard' ),
            'archives'              => _x( 'Lưu trữ khóa học', 'The post type archive label', 'nihongo-flashcard' ),
            'insert_into_item'      => _x( 'Chèn vào khóa học', 'Overrides the "Insert into post" phrase', 'nihongo-flashcard' ),
            'uploaded_to_this_item' => _x( 'Tải lên khóa học này', 'Overrides the "Uploaded to this post" phrase', 'nihongo-flashcard' ),
            'filter_items_list'     => _x( 'Lọc danh sách khóa học', 'Screen reader text', 'nihongo-flashcard' ),
            'items_list_navigation' => _x( 'Điều hướng danh sách khóa học', 'Screen reader text', 'nihongo-flashcard' ),
            'items_list'            => _x( 'Danh sách khóa học', 'Screen reader text', 'nihongo-flashcard' ),
        );

        $args = array(
            'labels'             => $labels,
            'public'             => false,
            'publicly_queryable' => false,
            'show_ui'            => true,
            'show_in_menu'       => 'nihongo-flashcard',
            'query_var'          => true,
            'rewrite'            => array( 'slug' => 'nihongo-course' ),
            'capability_type'    => 'post',
            'has_archive'        => false,
            'hierarchical'       => false,
            'menu_position'      => null,
            'supports'           => array( 'title', 'thumbnail', 'editor' ),
            'show_in_rest'       => true,
        );

        register_post_type( 'nihongo_course', $args );
    }

    /**
     * Get course decks
     *
     * @param int $course_id Course ID.
     * @return array
     */
    public static function get_decks( $course_id ) {
        $deck_ids = get_post_meta( $course_id, '_nihongo_course_decks', true );
        return is_array( $deck_ids ) ? $deck_ids : array();
    }

    /**
     * Save course decks
     *
     * @param int   $course_id Course ID.
     * @param array $deck_ids  Deck IDs.
     * @return bool
     */
    public static function save_decks( $course_id, $deck_ids ) {
        return update_post_meta( $course_id, '_nihongo_course_decks', array_map( 'intval', $deck_ids ) );
    }

    /**
     * Get total card count for course
     *
     * @param int $course_id Course ID.
     * @return int
     */
    public static function get_total_cards( $course_id ) {
        $deck_ids = self::get_decks( $course_id );
        $total    = 0;

        foreach ( $deck_ids as $deck_id ) {
            $total += Nihongo_Flashcard_Post_Type::get_card_count( $deck_id );
        }

        return $total;
    }

    /**
     * Get course progress for user
     *
     * @param int $course_id Course ID.
     * @param int $user_id   User ID.
     * @return array
     */
    public static function get_progress( $course_id, $user_id = 0 ) {
        if ( ! $user_id ) {
            $user_id = get_current_user_id();
        }

        $deck_ids     = self::get_decks( $course_id );
        $total_cards  = 0;
        $learned      = 0;

        foreach ( $deck_ids as $deck_id ) {
            $card_count = Nihongo_Flashcard_Post_Type::get_card_count( $deck_id );
            $total_cards += $card_count;

            $progress = Nihongo_Progress_Tracker::get_deck_progress( $user_id, $deck_id );
            $learned += $progress['learned'];
        }

        return array(
            'total'      => $total_cards,
            'learned'    => $learned,
            'percentage' => $total_cards > 0 ? round( ( $learned / $total_cards ) * 100 ) : 0,
        );
    }
}

// Initialize the class
new Nihongo_Course_Post_Type();
