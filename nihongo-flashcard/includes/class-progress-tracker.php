<?php
/**
 * Progress Tracker
 *
 * @package NihongoFlashcard
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Nihongo_Progress_Tracker
 */
class Nihongo_Progress_Tracker {

    /**
     * Table name
     *
     * @var string
     */
    private static $table_name;

    /**
     * Constructor
     */
    public function __construct() {
        global $wpdb;
        self::$table_name = $wpdb->prefix . 'nihongo_flashcard_progress';
    }

    /**
     * Create database table
     */
    public static function create_table() {
        global $wpdb;

        $table_name      = $wpdb->prefix . 'nihongo_flashcard_progress';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            deck_id bigint(20) NOT NULL,
            card_index int(11) NOT NULL,
            learned tinyint(1) DEFAULT 0,
            learned_at datetime DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY user_deck_card (user_id, deck_id, card_index),
            KEY user_id (user_id),
            KEY deck_id (deck_id)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );
    }

    /**
     * Get deck progress for user
     *
     * @param int $user_id User ID.
     * @param int $deck_id Deck ID.
     * @return array
     */
    public static function get_deck_progress( $user_id, $deck_id ) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'nihongo_flashcard_progress';
        $total      = Nihongo_Flashcard_Post_Type::get_card_count( $deck_id );

        if ( ! $user_id ) {
            return array(
                'total'      => $total,
                'learned'    => 0,
                'percentage' => 0,
                'cards'      => array(),
            );
        }

        // Get learned cards count
        $learned = $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}nihongo_flashcard_progress WHERE user_id = %d AND deck_id = %d AND learned = 1", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                $user_id,
                $deck_id
            )
        );

        // Get learned card indices
        $cards = $wpdb->get_col( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
            $wpdb->prepare(
                "SELECT card_index FROM {$wpdb->prefix}nihongo_flashcard_progress WHERE user_id = %d AND deck_id = %d AND learned = 1", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                $user_id,
                $deck_id
            )
        );

        return array(
            'total'      => $total,
            'learned'    => (int) $learned,
            'percentage' => $total > 0 ? round( ( $learned / $total ) * 100 ) : 0,
            'cards'      => array_map( 'intval', $cards ),
        );
    }

    /**
     * Mark card as learned/unlearned
     *
     * @param int  $user_id   User ID.
     * @param int  $deck_id   Deck ID.
     * @param int  $card_idx  Card index.
     * @param bool $learned   Learned status.
     * @return bool
     */
    public static function mark_card( $user_id, $deck_id, $card_idx, $learned = true ) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'nihongo_flashcard_progress';

        // Check if record exists
        $exists = $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
            $wpdb->prepare(
                "SELECT id FROM {$wpdb->prefix}nihongo_flashcard_progress WHERE user_id = %d AND deck_id = %d AND card_index = %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                $user_id,
                $deck_id,
                $card_idx
            )
        );

        if ( $exists ) {
            // Update existing record
            return $wpdb->update( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
                $table_name,
                array(
                    'learned'    => $learned ? 1 : 0,
                    'learned_at' => $learned ? current_time( 'mysql' ) : null,
                ),
                array(
                    'user_id'    => $user_id,
                    'deck_id'    => $deck_id,
                    'card_index' => $card_idx,
                ),
                array( '%d', '%s' ),
                array( '%d', '%d', '%d' )
            ) !== false;
        } else {
            // Insert new record
            return $wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
                $table_name,
                array(
                    'user_id'    => $user_id,
                    'deck_id'    => $deck_id,
                    'card_index' => $card_idx,
                    'learned'    => $learned ? 1 : 0,
                    'learned_at' => $learned ? current_time( 'mysql' ) : null,
                ),
                array( '%d', '%d', '%d', '%d', '%s' )
            ) !== false;
        }
    }

    /**
     * Reset deck progress for user
     *
     * @param int $user_id User ID.
     * @param int $deck_id Deck ID.
     * @return bool
     */
    public static function reset_deck_progress( $user_id, $deck_id ) {
        global $wpdb;

        return $wpdb->delete( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
            $wpdb->prefix . 'nihongo_flashcard_progress',
            array(
                'user_id' => $user_id,
                'deck_id' => $deck_id,
            ),
            array( '%d', '%d' )
        ) !== false;
    }

    /**
     * Get user's overall progress
     *
     * @param int $user_id User ID.
     * @return array
     */
    public static function get_user_stats( $user_id ) {
        global $wpdb;

        // Get total decks studied
        $decks_studied = $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
            $wpdb->prepare(
                "SELECT COUNT(DISTINCT deck_id) FROM {$wpdb->prefix}nihongo_flashcard_progress WHERE user_id = %d AND learned = 1", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                $user_id
            )
        );

        // Get total cards learned
        $cards_learned = $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}nihongo_flashcard_progress WHERE user_id = %d AND learned = 1", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                $user_id
            )
        );

        return array(
            'decks_studied' => (int) $decks_studied,
            'cards_learned' => (int) $cards_learned,
        );
    }
}

// Initialize the class
new Nihongo_Progress_Tracker();
