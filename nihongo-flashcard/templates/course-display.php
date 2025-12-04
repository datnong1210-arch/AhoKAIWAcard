<?php
/**
 * Course Display Template
 *
 * @package NihongoFlashcard
 *
 * Variables available:
 * - $course_id      : Course ID
 * - $course         : WP_Post object
 * - $decks          : Array of deck data
 * - $course_progress : Course progress array
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="nihongo-course-container" data-course-id="<?php echo esc_attr( $course_id ); ?>">

    <!-- Course Header -->
    <div class="nihongo-course-header">
        <h2 class="nihongo-course-title"><?php echo esc_html( $course->post_title ); ?></h2>

        <?php if ( $course->post_content ) : ?>
        <div class="nihongo-course-description">
            <?php echo wp_kses_post( $course->post_content ); ?>
        </div>
        <?php endif; ?>

        <!-- Overall Progress -->
        <div class="nihongo-course-progress">
            <div class="nihongo-course-progress-text">
                <?php
                printf(
                    /* translators: 1: learned cards, 2: total cards, 3: percentage */
                    esc_html__( '%1$d / %2$d thẻ (%3$d%%)', 'nihongo-flashcard' ),
                    $course_progress['learned'],
                    $course_progress['total'],
                    $course_progress['percentage']
                );
                ?>
            </div>
            <div class="nihongo-progress-bar">
                <div class="nihongo-progress-fill" style="width: <?php echo esc_attr( $course_progress['percentage'] ); ?>%"></div>
            </div>
        </div>
    </div>

    <!-- Deck Grid -->
    <div class="nihongo-deck-grid">
        <?php foreach ( $decks as $deck_data ) : ?>
            <?php
            $status_class = 'not-started';
            $status_text  = __( 'Chưa học', 'nihongo-flashcard' );

            if ( $deck_data['percentage'] >= 100 ) {
                $status_class = 'completed';
                $status_text  = __( 'Hoàn thành', 'nihongo-flashcard' );
            } elseif ( $deck_data['percentage'] > 0 ) {
                $status_class = 'in-progress';
                $status_text  = __( 'Đang học', 'nihongo-flashcard' );
            }
            ?>
            <a href="#" class="nihongo-deck-card" data-deck-id="<?php echo esc_attr( $deck_data['id'] ); ?>">
                <!-- Thumbnail -->
                <div class="nihongo-deck-thumbnail">
                    <?php if ( $deck_data['thumbnail'] ) : ?>
                        <img src="<?php echo esc_url( $deck_data['thumbnail'] ); ?>" alt="<?php echo esc_attr( $deck_data['title'] ); ?>">
                    <?php else : ?>
                        <div class="nihongo-deck-thumbnail-placeholder">
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M21 5c-1.11-.35-2.33-.5-3.5-.5-1.95 0-4.05.4-5.5 1.5-1.45-1.1-3.55-1.5-5.5-1.5S2.45 4.9 1 6v14.65c0 .25.25.5.5.5.1 0 .15-.05.25-.05C3.1 20.45 5.05 20 6.5 20c1.95 0 4.05.4 5.5 1.5 1.35-.85 3.8-1.5 5.5-1.5 1.65 0 3.35.3 4.75 1.05.1.05.15.05.25.05.25 0 .5-.25.5-.5V6c-.6-.45-1.25-.75-2-1zm0 13.5c-1.1-.35-2.3-.5-3.5-.5-1.7 0-4.15.65-5.5 1.5V8c1.35-.85 3.8-1.5 5.5-1.5 1.2 0 2.4.15 3.5.5v11.5z"/>
                            </svg>
                        </div>
                    <?php endif; ?>

                    <!-- Status Badge -->
                    <span class="nihongo-deck-status <?php echo esc_attr( $status_class ); ?>">
                        <?php echo esc_html( $status_text ); ?>
                    </span>
                </div>

                <!-- Content -->
                <div class="nihongo-deck-content">
                    <h3 class="nihongo-deck-name"><?php echo esc_html( $deck_data['title'] ); ?></h3>

                    <div class="nihongo-deck-meta">
                        <span class="nihongo-deck-card-count">
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M4 6H2v14c0 1.1.9 2 2 2h14v-2H4V6zm16-4H8c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-1 9h-4v4h-2v-4H9V9h4V5h2v4h4v2z"/>
                            </svg>
                            <?php
                            printf(
                                /* translators: %d: number of cards */
                                esc_html__( '%d thẻ', 'nihongo-flashcard' ),
                                $deck_data['card_count']
                            );
                            ?>
                        </span>

                        <?php if ( $deck_data['learned'] > 0 ) : ?>
                        <span class="nihongo-deck-learned-count">
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                            </svg>
                            <?php
                            printf(
                                /* translators: %d: number of learned cards */
                                esc_html__( '%d đã học', 'nihongo-flashcard' ),
                                $deck_data['learned']
                            );
                            ?>
                        </span>
                        <?php endif; ?>
                    </div>

                    <!-- Progress Bar -->
                    <div class="nihongo-deck-progress-bar">
                        <div class="nihongo-deck-progress-fill" style="width: <?php echo esc_attr( $deck_data['percentage'] ); ?>%"></div>
                    </div>
                </div>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Deck Detail Container (for AJAX loaded deck) -->
    <div class="nihongo-deck-detail-container" style="display: none;"></div>

</div>
