<?php
/**
 * Flashcard Display Template
 *
 * @package NihongoFlashcard
 *
 * Variables available:
 * - $deck_id   : Deck ID
 * - $deck      : WP_Post object
 * - $cards     : Array of cards
 * - $audio_url : Deck audio URL
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Prepare cards data for JSON
$cards_json = wp_json_encode( $cards );
?>

<div class="nihongo-flashcard-container" data-deck-id="<?php echo esc_attr( $deck_id ); ?>" data-cards='<?php echo esc_attr( $cards_json ); ?>'>

    <h2 class="nihongo-deck-title"><?php echo esc_html( $deck->post_title ); ?></h2>

    <?php if ( $audio_url ) : ?>
    <!-- Audio Player -->
    <div class="nihongo-audio-player" data-audio-url="<?php echo esc_url( $audio_url ); ?>">
        <div class="nihongo-audio-player-inner">
            <button type="button" class="nihongo-audio-play-btn" aria-label="<?php esc_attr_e( 'Phát/Dừng', 'nihongo-flashcard' ); ?>">
                <svg class="play-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M8 5v14l11-7z"/></svg>
                <svg class="pause-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/></svg>
            </button>

            <div class="nihongo-audio-progress-section">
                <div class="nihongo-audio-progress-wrapper">
                    <div class="nihongo-audio-progress-bar">
                        <div class="nihongo-audio-progress-fill"></div>
                    </div>
                    <div class="nihongo-audio-progress-handle"></div>
                </div>
                <div class="nihongo-audio-time">
                    <span class="nihongo-audio-current">0:00</span>
                    <span class="nihongo-audio-duration">0:00</span>
                </div>
            </div>

            <div class="nihongo-audio-volume">
                <button type="button" class="nihongo-audio-volume-btn" aria-label="<?php esc_attr_e( 'Âm lượng', 'nihongo-flashcard' ); ?>">
                    <svg class="volume-on" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 9v6h4l5 5V4L7 9H3zm13.5 3c0-1.77-1.02-3.29-2.5-4.03v8.05c1.48-.73 2.5-2.25 2.5-4.02zM14 3.23v2.06c2.89.86 5 3.54 5 6.71s-2.11 5.85-5 6.71v2.06c4.01-.91 7-4.49 7-8.77s-2.99-7.86-7-8.77z"/></svg>
                    <svg class="volume-low" viewBox="0 0 24 24" aria-hidden="true"><path d="M18.5 12c0-1.77-1.02-3.29-2.5-4.03v8.05c1.48-.73 2.5-2.25 2.5-4.02zM5 9v6h4l5 5V4L9 9H5z"/></svg>
                    <svg class="volume-muted" viewBox="0 0 24 24" aria-hidden="true"><path d="M16.5 12c0-1.77-1.02-3.29-2.5-4.03v2.21l2.45 2.45c.03-.2.05-.41.05-.63zm2.5 0c0 .94-.2 1.82-.54 2.64l1.51 1.51C20.63 14.91 21 13.5 21 12c0-4.28-2.99-7.86-7-8.77v2.06c2.89.86 5 3.54 5 6.71zM4.27 3L3 4.27 7.73 9H3v6h4l5 5v-6.73l4.25 4.25c-.67.52-1.42.93-2.25 1.18v2.06c1.38-.31 2.63-.95 3.69-1.81L19.73 21 21 19.73l-9-9L4.27 3zM12 4L9.91 6.09 12 8.18V4z"/></svg>
                </button>
                <div class="nihongo-audio-volume-slider-wrapper">
                    <div class="nihongo-audio-volume-slider">
                        <div class="nihongo-audio-volume-fill"></div>
                        <div class="nihongo-audio-volume-handle"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="nihongo-audio-error-message"><?php esc_html_e( 'Không thể tải audio', 'nihongo-flashcard' ); ?></div>
    </div>
    <?php endif; ?>

    <!-- Flashcard -->
    <div class="nihongo-flashcard-wrapper">
        <div class="nihongo-flashcard" role="region" aria-label="<?php esc_attr_e( 'Thẻ học', 'nihongo-flashcard' ); ?>">
            <div class="nihongo-card-top">
                <div class="nihongo-card-japanese" lang="ja">
                    <?php echo esc_html( isset( $cards[0]['front_top'] ) ? $cards[0]['front_top'] : '' ); ?>
                </div>
                <?php if ( ! empty( $cards[0]['audio_url'] ) ) : ?>
                <button type="button" class="nihongo-card-audio-btn" data-audio-url="<?php echo esc_url( $cards[0]['audio_url'] ); ?>" aria-label="<?php esc_attr_e( 'Nghe phát âm', 'nihongo-flashcard' ); ?>">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 9v6h4l5 5V4L7 9H3zm13.5 3c0-1.77-1.02-3.29-2.5-4.03v8.05c1.48-.73 2.5-2.25 2.5-4.02z"/></svg>
                </button>
                <?php endif; ?>
            </div>

            <div class="nihongo-card-divider"></div>

            <div class="nihongo-card-bottom">
                <div class="nihongo-card-meaning">
                    <?php echo esc_html( isset( $cards[0]['front_bottom'] ) ? $cards[0]['front_bottom'] : '' ); ?>
                </div>
            </div>

            <span class="nihongo-flip-hint"><?php esc_html_e( 'Nhấn để lật thẻ', 'nihongo-flashcard' ); ?></span>
        </div>
    </div>

    <!-- Controls -->
    <div class="nihongo-controls" role="toolbar" aria-label="<?php esc_attr_e( 'Điều khiển thẻ', 'nihongo-flashcard' ); ?>">
        <button type="button" class="nihongo-btn nihongo-btn-prev" title="<?php esc_attr_e( 'Thẻ trước (←)', 'nihongo-flashcard' ); ?>" aria-label="<?php esc_attr_e( 'Thẻ trước', 'nihongo-flashcard' ); ?>">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/></svg>
        </button>

        <button type="button" class="nihongo-btn nihongo-btn-shuffle" title="<?php esc_attr_e( 'Xáo trộn (S)', 'nihongo-flashcard' ); ?>" aria-label="<?php esc_attr_e( 'Xáo trộn thẻ', 'nihongo-flashcard' ); ?>">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10.59 9.17L5.41 4 4 5.41l5.17 5.17 1.42-1.41zM14.5 4l2.04 2.04L4 18.59 5.41 20 17.96 7.46 20 9.5V4h-5.5zm.33 9.41l-1.41 1.41 3.13 3.13L14.5 20H20v-5.5l-2.04 2.04-3.13-3.13z"/></svg>
        </button>

        <button type="button" class="nihongo-btn nihongo-btn-primary nihongo-btn-flip" title="<?php esc_attr_e( 'Lật thẻ (Space)', 'nihongo-flashcard' ); ?>" aria-label="<?php esc_attr_e( 'Lật thẻ', 'nihongo-flashcard' ); ?>">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 4V1L8 5l4 4V6c3.31 0 6 2.69 6 6 0 1.01-.25 1.97-.7 2.8l1.46 1.46C19.54 15.03 20 13.57 20 12c0-4.42-3.58-8-8-8zm0 14c-3.31 0-6-2.69-6-6 0-1.01.25-1.97.7-2.8L5.24 7.74C4.46 8.97 4 10.43 4 12c0 4.42 3.58 8 8 8v3l4-4-4-4v3z"/></svg>
        </button>

        <button type="button" class="nihongo-btn nihongo-btn-next" title="<?php esc_attr_e( 'Thẻ sau (→)', 'nihongo-flashcard' ); ?>" aria-label="<?php esc_attr_e( 'Thẻ sau', 'nihongo-flashcard' ); ?>">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8.59 16.59L10 18l6-6-6-6-1.41 1.41L13.17 12z"/></svg>
        </button>
    </div>

    <!-- Progress -->
    <div class="nihongo-progress-wrapper">
        <div class="nihongo-progress-bar" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
            <div class="nihongo-progress-fill" style="width: <?php echo esc_attr( 100 / count( $cards ) ); ?>%"></div>
        </div>
        <div class="nihongo-progress-text">
            <span class="nihongo-progress-count">1 / <?php echo esc_html( count( $cards ) ); ?></span>
            <span class="nihongo-learned-count">0 <?php esc_html_e( 'đã học', 'nihongo-flashcard' ); ?></span>
        </div>
    </div>

    <!-- Mark Learned -->
    <div class="nihongo-mark-learned">
        <button type="button" class="nihongo-mark-learned-btn" aria-pressed="false">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
            <span><?php esc_html_e( 'Đánh dấu đã học', 'nihongo-flashcard' ); ?></span>
        </button>
    </div>

    <!-- Swipe indicator for mobile -->
    <div class="nihongo-swipe-indicator">
        <?php esc_html_e( '← Vuốt để chuyển thẻ →', 'nihongo-flashcard' ); ?>
    </div>

    <!-- Keyboard shortcuts hint for desktop -->
    <div class="nihongo-shortcuts-hint">
        <kbd>←</kbd> <?php esc_html_e( 'Trước', 'nihongo-flashcard' ); ?>
        <kbd>→</kbd> <?php esc_html_e( 'Sau', 'nihongo-flashcard' ); ?>
        <kbd>Space</kbd> <?php esc_html_e( 'Lật', 'nihongo-flashcard' ); ?>
        <kbd>S</kbd> <?php esc_html_e( 'Xáo', 'nihongo-flashcard' ); ?>
    </div>

</div>
