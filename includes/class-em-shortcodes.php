<?php

if (! defined('ABSPATH')) {
    exit;
}

class EM_Shortcodes
{
    const CACHE_KEY = 'em_top_students';
    const CACHE_TTL = HOUR_IN_SECONDS;

    public static function init()
    {
        add_shortcode('em_top_students', array(__CLASS__, 'render'));
        
        add_action('save_post_em_result', array(__CLASS__, 'clear_cache'));
    }

    public static function clear_cache()
    {
        delete_transient(self::CACHE_KEY);
    }

    public static function render()
    {
        $cached = get_transient(self::CACHE_KEY);
        if (false !== $cached) {
            return $cached;
        }

        $terms = get_terms(array(
            'taxonomy'   => 'em_term',
            'hide_empty' => false,
            'meta_key'   => 'term_start_date',
            'orderby'    => 'meta_value',
            'order'      => 'DESC',
        ));

        if (empty($terms) || is_wp_error($terms)) {
            return '<p>' . esc_html__('No terms found.', 'mt-exam') . '</p>';
        }

        global $wpdb;

        $query = "
            SELECT 
                m_term.meta_value AS term_id,
                m_student.meta_value AS student_id,
                SUM(CAST(m_marks.meta_value AS UNSIGNED)) AS total_marks
            FROM {$wpdb->posts} r
            INNER JOIN {$wpdb->postmeta} m_student ON r.ID = m_student.post_id AND m_student.meta_key = 'result_student_id'
            INNER JOIN {$wpdb->postmeta} m_marks ON r.ID = m_marks.post_id AND m_marks.meta_key = 'result_marks'
            INNER JOIN {$wpdb->postmeta} m_exam ON r.ID = m_exam.post_id AND m_exam.meta_key = 'result_exam_id'
            INNER JOIN {$wpdb->postmeta} m_term ON m_exam.meta_value = m_term.post_id AND m_term.meta_key = 'exam_term_id'
            WHERE r.post_type = 'em_result' AND r.post_status = 'publish'
            GROUP BY term_id, student_id
        ";

        $db_results = $wpdb->get_results($query, ARRAY_A);

        $aggregated = array();
        foreach ($db_results as $row) {
            $t_id  = (int) $row['term_id'];
            $s_id  = (int) $row['student_id'];
            $marks = (int) $row['total_marks'];
            $aggregated[$t_id][$s_id] = $marks;
        }

        ob_start();
        echo '<div class="em-top-students">';

        foreach ($terms as $term) {
            $term_id = (int) $term->term_id;
            if (empty($aggregated[$term_id])) {
                continue;
            }

            $student_totals = $aggregated[$term_id];
            arsort($student_totals);
            $top_three = array_slice($student_totals, 0, 3, true);

            echo '<div class="em-term-block" style="margin-bottom: 20px;">';
            echo '<h3>' . esc_html($term->name) . '</h3>';
            echo '<ol>';

            foreach ($top_three as $student_id => $total) {
                $name = get_the_title($student_id);
                if (empty($name)) {
                    $name = sprintf(__('Student ID: %d', 'mt-exam'), $student_id);
                }
                echo '<li>' . esc_html($name) . ' &mdash; <strong>' . esc_html($total) . '</strong> ' . esc_html__('marks', 'mt-exam') . '</li>';
            }

            echo '</ol>';
            echo '</div>';
        }

        echo '</div>';

        $output = ob_get_clean();
        
        if (! empty($output)) {
            set_transient(self::CACHE_KEY, $output, self::CACHE_TTL);
        }

        return $output;
    }
}