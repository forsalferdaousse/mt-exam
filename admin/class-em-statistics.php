<?php
if (! defined('ABSPATH')) {
    exit;
}

class EM_Statistics
{
    public static function init()
    {
        add_action('admin_menu',               array(__CLASS__, 'register_page'));
        add_action('admin_post_em_export_pdf', array(__CLASS__, 'export_pdf'));
    }

    public static function register_page()
    {
        add_submenu_page(
            'edit.php?post_type=em_result',
            __('Student Statistics', 'mt-exam'),
            __('Student Statistics', 'mt-exam'),
            'manage_options',
            'em-student-statistics',
            array(__CLASS__, 'render_page')
        );
    }

    public static function render_page()
    {
        if (! current_user_can('manage_options')) {
            wp_die(esc_html__('Unauthorized.', 'mt-exam'));
        }

        $stats   = self::get_statistics();
        $pdf_url = wp_nonce_url(
            admin_url('admin-post.php?action=em_export_pdf'),
            'em_export_pdf'
        );

        include EM_PLUGIN_DIR . 'admin/views/statistics-page.php';
    }

    public static function export_pdf()
    {
        if (! current_user_can('manage_options')) {
            wp_die(esc_html__('Unauthorized.', 'mt-exam'));
        }

        check_admin_referer('em_export_pdf');

        if (! class_exists('Dompdf\Dompdf')) {
            wp_die(esc_html__('PDF export requires Dompdf. Run composer install in the plugin directory.', 'mt-exam'));
        }

        $stats = self::get_statistics();
        $html  = self::build_pdf_html($stats);

        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream('student-statistics-report.pdf', array('Attachment' => true));
        exit;
    }

    public static function get_statistics()
    {
        global $wpdb;

        // Step 1: Extract all student names indexed securely by ID
        $student_rows = $wpdb->get_results(
            "SELECT ID, post_title FROM {$wpdb->posts} WHERE post_type = 'em_student' AND post_status = 'publish'",
            ARRAY_A
        );

        if (empty($student_rows)) {
            return array();
        }

        $stats = array();
        foreach ($student_rows as $row) {
            $stats[(int)$row['ID']] = array(
                'name'  => $row['post_title'],
                'terms' => array(),
                'avg'   => 0
            );
        }

        $query = "
            SELECT 
                m_student.meta_value AS student_id,
                m_marks.meta_value AS marks,
                m_term.meta_value AS term_id
            FROM {$wpdb->posts} r
            INNER JOIN {$wpdb->postmeta} m_student ON r.ID = m_student.post_id AND m_student.meta_key = 'result_student_id'
            INNER JOIN {$wpdb->postmeta} m_marks ON r.ID = m_marks.post_id AND m_marks.meta_key = 'result_marks'
            INNER JOIN {$wpdb->postmeta} m_exam ON r.ID = m_exam.post_id AND m_exam.meta_key = 'result_exam_id'
            INNER JOIN {$wpdb->postmeta} m_term ON m_exam.meta_value = m_term.post_id AND m_term.meta_key = 'exam_term_id'
            WHERE r.post_type = 'em_result' AND r.post_status = 'publish'
        ";

        $results = $wpdb->get_results($query, ARRAY_A);

        $terms = get_terms(array('taxonomy' => 'em_term', 'hide_empty' => false));
        $term_map = wp_list_pluck($terms, 'name', 'term_id');

        foreach ($results as $row) {
            $student_id = (int) $row['student_id'];
            $term_id    = (int) $row['term_id'];
            $marks      = (int) $row['marks'];

            if (! isset($stats[$student_id]) || ! isset($term_map[$term_id])) {
                continue;
            }

            $term_name = $term_map[$term_id];

            if (! isset($stats[$student_id]['terms'][$term_name])) {
                $stats[$student_id]['terms'][$term_name] = 0;
            }
            $stats[$student_id]['terms'][$term_name] += $marks;
        }

        foreach ($stats as $id => $data) {
            $totals = array_values($data['terms']);
            $stats[$id]['avg'] = ! empty($totals)
                ? round(array_sum($totals) / count($totals), 2)
                : 0;
        }

        return $stats;
    }

    private static function build_pdf_html($stats)
    {
        $rows = '';
        foreach ($stats as $student) {
            $term_lines = '';
            foreach ($student['terms'] as $term => $total) {
                $term_lines .= esc_html($term) . ': ' . (int) $total . '<br>';
            }

            $rows .= '<tr>
                <td>' . esc_html($student['name']) . '</td>
                <td>' . $term_lines . '</td>
                <td>' . esc_html((string) $student['avg']) . '</td>
            </tr>';
        }

        return '<!DOCTYPE html>
        <html>
        <head><meta charset="UTF-8"><title>Student Statistics</title></head>
        <body>
        <h1>Student Statistics Report</h1>
        <table border="1" cellspacing="0" cellpadding="6" width="100%">
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Term Totals</th>
                    <th>Average</th>
                </tr>
            </thead>
            <tbody>' . $rows . '</tbody>
        </table>
        </body>
        </html>';
    }
}
