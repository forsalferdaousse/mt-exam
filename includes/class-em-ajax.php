<?php
if (! defined('ABSPATH')) {
    exit;
}

class EM_Ajax
{
    public static function init()
    {
        add_action('wp_ajax_em_get_exams',        array(__CLASS__, 'get_exams'));
        add_action('wp_ajax_nopriv_em_get_exams', array(__CLASS__, 'get_exams'));
        add_action('wp_enqueue_scripts',           array(__CLASS__, 'enqueue_scripts'));
    }

    public static function enqueue_scripts()
    {
        wp_enqueue_script(
            'em-ajax',
            plugins_url('/js/em-ajax.js', EM_PLUGIN_URL),
            array('jquery'),
            EM_VERSION,
            true
        );

        wp_localize_script('em-ajax', 'em_ajax_obj', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('em_ajax_nonce'),
        ));
    }

    public static function get_exams()
    {
        if (! isset($_POST['nonce']) || ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'em_ajax_nonce')) {
            wp_send_json_error(array('message' => __('Invalid request.', 'mt-exam')), 403);
        }

        $page     = isset($_POST['page'])     ? max(1, absint($_POST['page']))     : 1;
        $per_page = isset($_POST['per_page']) ? max(1, absint($_POST['per_page'])) : 10;
        $now      = current_time('Y-m-d\TH:i');

        global $wpdb;

        $query = "
            SELECT p.ID, p.post_title, m_start.meta_value as start_date, m_end.meta_value as end_date
            FROM {$wpdb->posts} p
            LEFT JOIN {$wpdb->postmeta} m_start ON p.ID = m_start.post_id AND m_start.meta_key = 'exam_start_datetime'
            LEFT JOIN {$wpdb->postmeta} m_end ON p.ID = m_end.post_id AND m_end.meta_key = 'exam_end_datetime'
            WHERE p.post_type = 'em_exam' AND p.post_status = 'publish'
        ";

        $all_exams = $wpdb->get_results($query, ARRAY_A);

        $current  = array();
        $upcoming = array();
        $past     = array();

        foreach ($all_exams as $exam) {
            $start = $exam['start_date'];
            $end   = $exam['end_date'];

            $exam_data = array(
                'id'    => (int) $exam['ID'],
                'title' => $exam['post_title'],
                'start' => $start,
                'end'   => $end,
            );

            if ($start && $end) {
                if ($now >= $start && $now <= $end) {
                    $exam_data['status'] = 'current';
                    $current[]           = $exam_data;
                } elseif ($now < $start) {
                    $exam_data['status'] = 'upcoming';
                    $upcoming[]          = $exam_data;
                } else {
                    $exam_data['status'] = 'past';
                    $past[]              = $exam_data;
                }
            } else {
                $exam_data['status'] = 'unknown';
                $past[]              = $exam_data;
            }
        }

        usort($upcoming, function ($a, $b) {
            return strcmp($a['start'], $b['start']);
        });
        usort($past,     function ($a, $b) {
            return strcmp($b['end'], $a['end']);
        });

        $all_sorted  = array_merge($current, $upcoming, $past);
        $total       = count($all_sorted);
        $total_pages = (int) ceil($total / $per_page);

        $paged       = array_slice($all_sorted, ($page - 1) * $per_page, $per_page);

        wp_send_json_success(array(
            'exams'       => $paged,
            'total'       => $total,
            'page'        => $page,
            'per_page'    => $per_page,
            'total_pages' => $total_pages,
        ));
    }
}
