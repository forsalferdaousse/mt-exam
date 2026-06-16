<?php
if (! defined('ABSPATH')) {
    exit;
}

class EM_Import
{
    public static function init()
    {
        add_action('admin_menu', array(__CLASS__, 'register_page'));
    }

    public static function register_page()
    {
        add_submenu_page(
            'edit.php?post_type=em_result',
            __('Import Results',  'mt-exam'),
            __('Import Results',  'mt-exam'),
            'manage_options',
            'em-import-results',
            array(__CLASS__, 'render_page')
        );
    }

    public static function render_page()
    {
        if (! current_user_can('manage_options')) {
            wp_die(esc_html__('Unauthorized.', 'mt-exam'));
        }

        $notice = '';

        if (
            isset($_FILES['results_csv'], $_POST['_wpnonce']) &&
            wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_wpnonce'])), 'em_import_results')
        ) {
            $notice = self::process_csv($_FILES['results_csv']);
        }

        include EM_PLUGIN_DIR . 'admin/views/import-page.php';
    }

    private static function process_csv($file)
    {
        if (empty($file['tmp_name']) || ! is_uploaded_file($file['tmp_name'])) {
            return '<div class="notice notice-error"><p>' . esc_html__('Invalid file upload.', 'mt-exam') . '</p></div>';
        }

        $handle = fopen($file['tmp_name'], 'r');
        if (! $handle) {
            return '<div class="notice notice-error"><p>' . esc_html__('Could not open the uploaded file.', 'mt-exam') . '</p></div>';
        }

        fgetcsv($handle);

        $imported_count = 0;

        while (($row = fgetcsv($handle)) !== false) {
            if (! isset($row[0], $row[1], $row[2])) {
                continue;
            }

            $student_id = absint($row[0]);
            $exam_id    = absint($row[1]);
            $marks      = min(100, max(0, absint($row[2])));

            if (
                'em_student' !== get_post_type($student_id) ||
                'em_exam'    !== get_post_type($exam_id)
            ) {
                continue;
            }

            $student_name = get_the_title($student_id);

            $result_id = wp_insert_post(array(
                'post_type'   => 'em_result',
                'post_status' => 'publish',
                'post_title'  => sprintf(__('Result: %s — Exam %d', 'mt-exam'), $student_name, $exam_id),
            ));

            if (is_wp_error($result_id) || ! $result_id) {
                continue;
            }

            update_post_meta($result_id, 'result_exam_id',    $exam_id);
            update_post_meta($result_id, 'result_student_id', $student_id);
            update_post_meta($result_id, 'result_marks',      $marks);

            $imported_count++;
        }

        fclose($handle);

        if ($imported_count === 0) {
            return '<div class="notice notice-warning"><p>' . esc_html__('No valid rows were imported from the CSV.', 'mt-exam') . '</p></div>';
        }

        return '<div class="notice notice-success"><p>' . sprintf(esc_html__('Import completed successfully. Created %d separate records.', 'mt-exam'), $imported_count) . '</p></div>';
    }
}
