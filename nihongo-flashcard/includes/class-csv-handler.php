<?php
/**
 * CSV Handler - Import/Export functionality
 *
 * @package NihongoFlashcard
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Nihongo_CSV_Handler
 */
class Nihongo_CSV_Handler {

    /**
     * Constructor
     */
    public function __construct() {
        add_action( 'admin_post_nihongo_export_csv', array( $this, 'export_csv' ) );
        add_action( 'admin_post_nihongo_import_csv', array( $this, 'import_csv' ) );
        add_action( 'admin_post_nihongo_download_template', array( $this, 'download_template' ) );
    }

    /**
     * Export deck to CSV
     */
    public function export_csv() {
        // Verify nonce
        if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'nihongo_export_csv' ) ) {
            wp_die( esc_html__( 'Xác thực bảo mật thất bại.', 'nihongo-flashcard' ) );
        }

        // Check permissions
        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_die( esc_html__( 'Bạn không có quyền thực hiện hành động này.', 'nihongo-flashcard' ) );
        }

        // Get deck ID
        $deck_id = isset( $_GET['deck_id'] ) ? intval( $_GET['deck_id'] ) : 0;

        if ( ! $deck_id ) {
            wp_die( esc_html__( 'ID bộ thẻ không hợp lệ.', 'nihongo-flashcard' ) );
        }

        // Get deck data
        $deck = get_post( $deck_id );
        if ( ! $deck || 'nihongo_deck' !== $deck->post_type ) {
            wp_die( esc_html__( 'Bộ thẻ không tồn tại.', 'nihongo-flashcard' ) );
        }

        $cards = Nihongo_Flashcard_Post_Type::get_cards( $deck_id );

        // Create filename
        $filename = sanitize_file_name( $deck->post_title ) . '_' . gmdate( 'Y-m-d' ) . '.csv';

        // Set headers for download
        header( 'Content-Type: text/csv; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
        header( 'Pragma: no-cache' );
        header( 'Expires: 0' );

        // Open output stream
        $output = fopen( 'php://output', 'w' );

        // Add UTF-8 BOM for Excel compatibility
        fprintf( $output, chr( 0xEF ) . chr( 0xBB ) . chr( 0xBF ) );

        // Write header row
        fputcsv( $output, array( 'front_top', 'front_bottom', 'audio_url' ) );

        // Write data rows
        foreach ( $cards as $card ) {
            fputcsv(
                $output,
                array(
                    isset( $card['front_top'] ) ? $card['front_top'] : '',
                    isset( $card['front_bottom'] ) ? $card['front_bottom'] : '',
                    isset( $card['audio_url'] ) ? $card['audio_url'] : '',
                )
            );
        }

        fclose( $output );
        exit;
    }

    /**
     * Import CSV to deck
     */
    public function import_csv() {
        // Verify nonce
        if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'nihongo_import_csv' ) ) {
            wp_die( esc_html__( 'Xác thực bảo mật thất bại.', 'nihongo-flashcard' ) );
        }

        // Check permissions
        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_die( esc_html__( 'Bạn không có quyền thực hiện hành động này.', 'nihongo-flashcard' ) );
        }

        // Check if file was uploaded
        if ( ! isset( $_FILES['csv_file'] ) || empty( $_FILES['csv_file']['tmp_name'] ) ) {
            wp_safe_redirect( admin_url( 'admin.php?page=nihongo-flashcard-import-export&error=no_file' ) );
            exit;
        }

        // Check file type - manual validation since wp_check_filetype needs the actual filename
        $file_extension = strtolower( pathinfo( sanitize_file_name( wp_unslash( $_FILES['csv_file']['name'] ) ), PATHINFO_EXTENSION ) );
        if ( 'csv' !== $file_extension ) {
            wp_safe_redirect( admin_url( 'admin.php?page=nihongo-flashcard-import-export&error=invalid_type' ) );
            exit;
        }

        // Get import mode
        $import_mode = isset( $_POST['import_mode'] ) ? sanitize_text_field( wp_unslash( $_POST['import_mode'] ) ) : 'new';
        $deck_id     = isset( $_POST['deck_id'] ) ? intval( $_POST['deck_id'] ) : 0;
        $deck_title  = isset( $_POST['deck_title'] ) ? sanitize_text_field( wp_unslash( $_POST['deck_title'] ) ) : '';

        // Read CSV file
        $csv_file = sanitize_text_field( wp_unslash( $_FILES['csv_file']['tmp_name'] ) );
        $cards    = $this->parse_csv( $csv_file );

        if ( empty( $cards ) ) {
            wp_safe_redirect( admin_url( 'admin.php?page=nihongo-flashcard-import-export&error=empty_file' ) );
            exit;
        }

        // Create or update deck
        if ( 'new' === $import_mode ) {
            // Create new deck
            $deck_id = wp_insert_post(
                array(
                    'post_title'  => $deck_title ? $deck_title : __( 'Bộ thẻ nhập từ CSV', 'nihongo-flashcard' ),
                    'post_type'   => 'nihongo_deck',
                    'post_status' => 'publish',
                )
            );

            if ( is_wp_error( $deck_id ) ) {
                wp_safe_redirect( admin_url( 'admin.php?page=nihongo-flashcard-import-export&error=create_failed' ) );
                exit;
            }
        } elseif ( 'append' === $import_mode && $deck_id ) {
            // Append to existing deck
            $existing_cards = Nihongo_Flashcard_Post_Type::get_cards( $deck_id );
            $cards          = array_merge( $existing_cards, $cards );
        } elseif ( 'replace' === $import_mode && $deck_id ) {
            // Replace existing deck cards (keep deck_id, just update cards)
        } else {
            wp_safe_redirect( admin_url( 'admin.php?page=nihongo-flashcard-import-export&error=invalid_mode' ) );
            exit;
        }

        // Save cards
        Nihongo_Flashcard_Post_Type::save_cards( $deck_id, $cards );

        // Redirect with success message
        wp_safe_redirect( admin_url( 'admin.php?page=nihongo-flashcard-import-export&success=imported&count=' . count( $cards ) ) );
        exit;
    }

    /**
     * Parse CSV file
     *
     * @param string $file_path Path to CSV file.
     * @return array
     */
    private function parse_csv( $file_path ) {
        $cards = array();

        // Open file
        $handle = fopen( $file_path, 'r' );
        if ( false === $handle ) {
            return $cards;
        }

        // Detect and handle BOM
        $bom = fread( $handle, 3 );
        if ( "\xEF\xBB\xBF" !== $bom ) {
            rewind( $handle );
        }

        // Read header row
        $header = fgetcsv( $handle );
        if ( ! $header ) {
            fclose( $handle );
            return $cards;
        }

        // Normalize header
        $header = array_map( 'strtolower', array_map( 'trim', $header ) );

        // Find column indices
        $front_top_idx    = array_search( 'front_top', $header, true );
        $front_bottom_idx = array_search( 'front_bottom', $header, true );
        $audio_url_idx    = array_search( 'audio_url', $header, true );

        // Read data rows
        while ( ( $row = fgetcsv( $handle ) ) !== false ) {
            if ( empty( $row ) || ( count( $row ) === 1 && empty( $row[0] ) ) ) {
                continue;
            }

            $card = array(
                'front_top'    => '',
                'front_bottom' => '',
                'audio_url'    => '',
            );

            if ( false !== $front_top_idx && isset( $row[ $front_top_idx ] ) ) {
                $card['front_top'] = sanitize_textarea_field( $row[ $front_top_idx ] );
            }

            if ( false !== $front_bottom_idx && isset( $row[ $front_bottom_idx ] ) ) {
                $card['front_bottom'] = sanitize_textarea_field( $row[ $front_bottom_idx ] );
            }

            if ( false !== $audio_url_idx && isset( $row[ $audio_url_idx ] ) ) {
                $card['audio_url'] = sanitize_url( $row[ $audio_url_idx ] );
            }

            // Only add if at least one field has content
            if ( ! empty( $card['front_top'] ) || ! empty( $card['front_bottom'] ) ) {
                $cards[] = $card;
            }
        }

        fclose( $handle );
        return $cards;
    }

    /**
     * Download CSV template
     */
    public function download_template() {
        // Verify nonce
        if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'nihongo_download_template' ) ) {
            wp_die( esc_html__( 'Xác thực bảo mật thất bại.', 'nihongo-flashcard' ) );
        }

        // Set headers for download
        header( 'Content-Type: text/csv; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename="nihongo-flashcard-template.csv"' );
        header( 'Pragma: no-cache' );
        header( 'Expires: 0' );

        // Open output stream
        $output = fopen( 'php://output', 'w' );

        // Add UTF-8 BOM
        fprintf( $output, chr( 0xEF ) . chr( 0xBB ) . chr( 0xBF ) );

        // Write header row
        fputcsv( $output, array( 'front_top', 'front_bottom', 'audio_url' ) );

        // Write sample data
        fputcsv( $output, array( '日本語', 'Tiếng Nhật / にほんご / Nihongo', '' ) );
        fputcsv( $output, array( '勉強', 'Học tập / べんきょう / Benkyou', 'https://example.com/audio.mp3' ) );
        fputcsv( $output, array( 'こんにちは', 'Xin chào / Konnichiwa', '' ) );

        fclose( $output );
        exit;
    }
}

// Initialize the class
new Nihongo_CSV_Handler();
