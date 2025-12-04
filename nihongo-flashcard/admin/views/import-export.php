<?php
/**
 * Import/Export View
 *
 * @package NihongoFlashcard
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Get all decks for export
$decks = get_posts(
    array(
        'post_type'      => 'nihongo_deck',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'orderby'        => 'title',
        'order'          => 'ASC',
    )
);

// Check for messages
$error   = isset( $_GET['error'] ) ? sanitize_text_field( wp_unslash( $_GET['error'] ) ) : '';
$success = isset( $_GET['success'] ) ? sanitize_text_field( wp_unslash( $_GET['success'] ) ) : '';
$count   = isset( $_GET['count'] ) ? intval( $_GET['count'] ) : 0;

// Error messages
$error_messages = array(
    'no_file'      => __( 'Vui lòng chọn file CSV để tải lên.', 'nihongo-flashcard' ),
    'invalid_type' => __( 'File không hợp lệ. Vui lòng chọn file CSV.', 'nihongo-flashcard' ),
    'empty_file'   => __( 'File CSV rỗng hoặc không có dữ liệu hợp lệ.', 'nihongo-flashcard' ),
    'create_failed' => __( 'Không thể tạo bộ thẻ mới.', 'nihongo-flashcard' ),
    'invalid_mode' => __( 'Chế độ nhập không hợp lệ.', 'nihongo-flashcard' ),
);
?>
<div class="wrap nihongo-import-export">
    <h1><?php esc_html_e( 'Nhập/Xuất CSV', 'nihongo-flashcard' ); ?></h1>

    <?php if ( $error && isset( $error_messages[ $error ] ) ) : ?>
        <div class="notice notice-error is-dismissible">
            <p><?php echo esc_html( $error_messages[ $error ] ); ?></p>
        </div>
    <?php endif; ?>

    <?php if ( 'imported' === $success ) : ?>
        <div class="notice notice-success is-dismissible">
            <p>
                <?php
                printf(
                    /* translators: %d: number of cards imported */
                    esc_html__( 'Đã nhập thành công %d thẻ!', 'nihongo-flashcard' ),
                    $count
                );
                ?>
            </p>
        </div>
    <?php endif; ?>

    <div class="nihongo-import-export-grid">
        <!-- Import Section -->
        <div class="nihongo-section nihongo-import-section">
            <h2><?php esc_html_e( 'Nhập từ CSV', 'nihongo-flashcard' ); ?></h2>

            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data">
                <input type="hidden" name="action" value="nihongo_import_csv">
                <?php wp_nonce_field( 'nihongo_import_csv' ); ?>

                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="csv_file"><?php esc_html_e( 'Chọn file CSV', 'nihongo-flashcard' ); ?></label>
                        </th>
                        <td>
                            <input type="file" name="csv_file" id="csv_file" accept=".csv" required>
                            <p class="description">
                                <?php esc_html_e( 'File CSV phải có encoding UTF-8. Cột: front_top, front_bottom, audio_url (optional)', 'nihongo-flashcard' ); ?>
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="import_mode"><?php esc_html_e( 'Chế độ nhập', 'nihongo-flashcard' ); ?></label>
                        </th>
                        <td>
                            <fieldset>
                                <label>
                                    <input type="radio" name="import_mode" value="new" checked>
                                    <?php esc_html_e( 'Tạo bộ thẻ mới', 'nihongo-flashcard' ); ?>
                                </label><br>
                                <label>
                                    <input type="radio" name="import_mode" value="append">
                                    <?php esc_html_e( 'Thêm vào bộ thẻ có sẵn', 'nihongo-flashcard' ); ?>
                                </label><br>
                                <label>
                                    <input type="radio" name="import_mode" value="replace">
                                    <?php esc_html_e( 'Thay thế bộ thẻ có sẵn', 'nihongo-flashcard' ); ?>
                                </label>
                            </fieldset>
                        </td>
                    </tr>

                    <tr class="nihongo-import-deck-title-row">
                        <th scope="row">
                            <label for="deck_title"><?php esc_html_e( 'Tên bộ thẻ mới', 'nihongo-flashcard' ); ?></label>
                        </th>
                        <td>
                            <input type="text" name="deck_title" id="deck_title" class="regular-text" placeholder="<?php esc_attr_e( 'Nhập tên bộ thẻ...', 'nihongo-flashcard' ); ?>">
                        </td>
                    </tr>

                    <tr class="nihongo-import-deck-select-row" style="display: none;">
                        <th scope="row">
                            <label for="deck_id"><?php esc_html_e( 'Chọn bộ thẻ', 'nihongo-flashcard' ); ?></label>
                        </th>
                        <td>
                            <select name="deck_id" id="deck_id" class="regular-text">
                                <option value=""><?php esc_html_e( '-- Chọn bộ thẻ --', 'nihongo-flashcard' ); ?></option>
                                <?php foreach ( $decks as $deck ) : ?>
                                    <option value="<?php echo esc_attr( $deck->ID ); ?>">
                                        <?php echo esc_html( $deck->post_title ); ?>
                                        (<?php echo esc_html( Nihongo_Flashcard_Post_Type::get_card_count( $deck->ID ) ); ?> thẻ)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                </table>

                <p class="submit">
                    <button type="submit" class="button button-primary">
                        <span class="dashicons dashicons-upload"></span>
                        <?php esc_html_e( 'Nhập CSV', 'nihongo-flashcard' ); ?>
                    </button>
                </p>
            </form>
        </div>

        <!-- Export Section -->
        <div class="nihongo-section nihongo-export-section">
            <h2><?php esc_html_e( 'Xuất ra CSV', 'nihongo-flashcard' ); ?></h2>

            <?php if ( empty( $decks ) ) : ?>
                <p><?php esc_html_e( 'Chưa có bộ thẻ nào để xuất.', 'nihongo-flashcard' ); ?></p>
            <?php else : ?>
                <table class="widefat">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'Tên bộ thẻ', 'nihongo-flashcard' ); ?></th>
                            <th><?php esc_html_e( 'Số thẻ', 'nihongo-flashcard' ); ?></th>
                            <th><?php esc_html_e( 'Hành động', 'nihongo-flashcard' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $decks as $deck ) : ?>
                            <tr>
                                <td><?php echo esc_html( $deck->post_title ); ?></td>
                                <td><?php echo esc_html( Nihongo_Flashcard_Post_Type::get_card_count( $deck->ID ) ); ?></td>
                                <td>
                                    <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=nihongo_export_csv&deck_id=' . $deck->ID ), 'nihongo_export_csv' ) ); ?>" class="button">
                                        <span class="dashicons dashicons-download"></span>
                                        <?php esc_html_e( 'Xuất CSV', 'nihongo-flashcard' ); ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- Template Section -->
        <div class="nihongo-section nihongo-template-section">
            <h2><?php esc_html_e( 'Tải file CSV mẫu', 'nihongo-flashcard' ); ?></h2>
            <p><?php esc_html_e( 'Tải file CSV mẫu để xem cấu trúc và điền dữ liệu của bạn.', 'nihongo-flashcard' ); ?></p>
            <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=nihongo_download_template' ), 'nihongo_download_template' ) ); ?>" class="button button-secondary">
                <span class="dashicons dashicons-download"></span>
                <?php esc_html_e( 'Tải file mẫu', 'nihongo-flashcard' ); ?>
            </a>

            <div class="nihongo-csv-structure">
                <h3><?php esc_html_e( 'Cấu trúc file CSV', 'nihongo-flashcard' ); ?></h3>
                <table class="widefat">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'Cột', 'nihongo-flashcard' ); ?></th>
                            <th><?php esc_html_e( 'Mô tả', 'nihongo-flashcard' ); ?></th>
                            <th><?php esc_html_e( 'Bắt buộc', 'nihongo-flashcard' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>front_top</code></td>
                            <td><?php esc_html_e( 'Phần trên thẻ (từ vựng tiếng Nhật)', 'nihongo-flashcard' ); ?></td>
                            <td><?php esc_html_e( 'Có', 'nihongo-flashcard' ); ?></td>
                        </tr>
                        <tr>
                            <td><code>front_bottom</code></td>
                            <td><?php esc_html_e( 'Phần dưới thẻ (nghĩa, phiên âm, ví dụ)', 'nihongo-flashcard' ); ?></td>
                            <td><?php esc_html_e( 'Có', 'nihongo-flashcard' ); ?></td>
                        </tr>
                        <tr>
                            <td><code>audio_url</code></td>
                            <td><?php esc_html_e( 'URL audio cho từng thẻ', 'nihongo-flashcard' ); ?></td>
                            <td><?php esc_html_e( 'Không', 'nihongo-flashcard' ); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const importModeInputs = document.querySelectorAll('input[name="import_mode"]');
    const titleRow = document.querySelector('.nihongo-import-deck-title-row');
    const selectRow = document.querySelector('.nihongo-import-deck-select-row');

    importModeInputs.forEach(function(input) {
        input.addEventListener('change', function() {
            if (this.value === 'new') {
                titleRow.style.display = '';
                selectRow.style.display = 'none';
            } else {
                titleRow.style.display = 'none';
                selectRow.style.display = '';
            }
        });
    });
});
</script>
