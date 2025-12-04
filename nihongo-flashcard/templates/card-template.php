<?php
/**
 * Card Template
 *
 * Individual card template for AJAX rendering
 *
 * @package NihongoFlashcard
 *
 * Variables available:
 * - $card       : Card data array
 * - $index      : Card index
 * - $total      : Total cards
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="nihongo-card-single" data-index="<?php echo esc_attr( $index ); ?>">
    <div class="nihongo-card-top">
        <div class="nihongo-card-japanese" lang="ja">
            <?php echo esc_html( isset( $card['front_top'] ) ? $card['front_top'] : '' ); ?>
        </div>
        <?php if ( ! empty( $card['audio_url'] ) ) : ?>
        <button type="button" class="nihongo-card-audio-btn" data-audio-url="<?php echo esc_url( $card['audio_url'] ); ?>" aria-label="<?php esc_attr_e( 'Nghe phát âm', 'nihongo-flashcard' ); ?>">
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M3 9v6h4l5 5V4L7 9H3zm13.5 3c0-1.77-1.02-3.29-2.5-4.03v8.05c1.48-.73 2.5-2.25 2.5-4.02z"/>
            </svg>
        </button>
        <?php endif; ?>
    </div>

    <div class="nihongo-card-divider"></div>

    <div class="nihongo-card-bottom">
        <div class="nihongo-card-meaning">
            <?php echo esc_html( isset( $card['front_bottom'] ) ? $card['front_bottom'] : '' ); ?>
        </div>
    </div>
</div>
