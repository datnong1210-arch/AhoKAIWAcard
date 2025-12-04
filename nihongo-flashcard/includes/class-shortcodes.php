<?php
/**
 * Shortcodes
 *
 * @package NihongoFlashcard
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Nihongo_Shortcodes
 */
class Nihongo_Shortcodes {

    /**
     * Constructor
     */
    public function __construct() {
        add_shortcode( 'nihongo_deck', array( $this, 'render_deck' ) );
        add_shortcode( 'nihongo_course', array( $this, 'render_course' ) );
    }

    /**
     * Render deck shortcode
     *
     * @param array $atts Shortcode attributes.
     * @return string
     */
    public function render_deck( $atts ) {
        $atts = shortcode_atts(
            array(
                'id' => 0,
            ),
            $atts,
            'nihongo_deck'
        );

        $deck_id = intval( $atts['id'] );

        if ( ! $deck_id ) {
            return '<p class="nihongo-error">' . esc_html__( 'Vui lòng cung cấp ID bộ thẻ.', 'nihongo-flashcard' ) . '</p>';
        }

        // Get deck
        $deck = get_post( $deck_id );
        if ( ! $deck || 'nihongo_deck' !== $deck->post_type ) {
            return '<p class="nihongo-error">' . esc_html__( 'Bộ thẻ không tồn tại.', 'nihongo-flashcard' ) . '</p>';
        }

        // Get cards and audio
        $cards     = Nihongo_Flashcard_Post_Type::get_cards( $deck_id );
        $audio_url = Nihongo_Flashcard_Post_Type::get_audio_url( $deck_id );

        if ( empty( $cards ) ) {
            return '<p class="nihongo-error">' . esc_html__( 'Bộ thẻ chưa có thẻ nào.', 'nihongo-flashcard' ) . '</p>';
        }

        // Start output buffering
        ob_start();

        // Include template
        include NIHONGO_FLASHCARD_PLUGIN_DIR . 'templates/flashcard-display.php';

        return ob_get_clean();
    }

    /**
     * Render course shortcode
     *
     * @param array $atts Shortcode attributes.
     * @return string
     */
    public function render_course( $atts ) {
        $atts = shortcode_atts(
            array(
                'id' => 0,
            ),
            $atts,
            'nihongo_course'
        );

        $course_id = intval( $atts['id'] );

        if ( ! $course_id ) {
            return '<p class="nihongo-error">' . esc_html__( 'Vui lòng cung cấp ID khóa học.', 'nihongo-flashcard' ) . '</p>';
        }

        // Get course
        $course = get_post( $course_id );
        if ( ! $course || 'nihongo_course' !== $course->post_type ) {
            return '<p class="nihongo-error">' . esc_html__( 'Khóa học không tồn tại.', 'nihongo-flashcard' ) . '</p>';
        }

        // Get decks
        $deck_ids = Nihongo_Course_Post_Type::get_decks( $course_id );

        if ( empty( $deck_ids ) ) {
            return '<p class="nihongo-error">' . esc_html__( 'Khóa học chưa có bộ thẻ nào.', 'nihongo-flashcard' ) . '</p>';
        }

        // Get deck data
        $decks = array();
        foreach ( $deck_ids as $deck_id ) {
            $deck = get_post( $deck_id );
            if ( $deck && 'nihongo_deck' === $deck->post_type ) {
                $card_count = Nihongo_Flashcard_Post_Type::get_card_count( $deck_id );
                $progress   = Nihongo_Progress_Tracker::get_deck_progress( get_current_user_id(), $deck_id );

                $decks[] = array(
                    'id'          => $deck_id,
                    'title'       => $deck->post_title,
                    'thumbnail'   => get_the_post_thumbnail_url( $deck_id, 'medium' ),
                    'card_count'  => $card_count,
                    'learned'     => $progress['learned'],
                    'percentage'  => $progress['percentage'],
                );
            }
        }

        // Get course progress
        $course_progress = Nihongo_Course_Post_Type::get_progress( $course_id );

        // Start output buffering
        ob_start();

        // Include template
        include NIHONGO_FLASHCARD_PLUGIN_DIR . 'templates/course-display.php';

        return ob_get_clean();
    }
}

// Initialize the class
new Nihongo_Shortcodes();
