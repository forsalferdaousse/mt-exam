<?php

if (! defined('ABSPATH')) {
    exit;
}

class EM_Result_Meta
{
    public static function init()
    {
        add_action('add_meta_boxes',     array(__CLASS__, 'register_meta_box'));
        add_action('save_post_em_result', array(__CLASS__, 'save'));
    }

    public static function register_meta_box()
    {
        add_meta_box(
            'em_result_details',
            __('Result Details', 'mt-exam'),
            array(__CLASS__, 'render'),
            'em_result',
            'normal',
            'high'
        );
    }

    public static function render($post)
    {
        wp_nonce_field('em_save_result_meta', 'em_result_nonce');

        $selected_exam       = (int) get_post_meta($post->ID, 'result_exam_id', true);
        $selected_student_id = (int) get_post_meta($post->ID, 'result_student_id', true);

        $saved_marks_raw = get_post_meta($post->ID, 'result_marks', true);

        if (is_array($saved_marks_raw)) {
            $saved_marks = isset($saved_marks_raw[$selected_student_id]) ? $saved_marks_raw[$selected_student_id] : '';
        } else {
            $saved_marks = $saved_marks_raw;
        }

        $exams = get_posts(array(
            'post_type'      => 'em_exam',
            'posts_per_page' => -1,
            'orderby'        => 'title',
            'order'          => 'ASC',
            'post_status'    => 'publish',
        ));

        $students = get_posts(array(
            'post_type'      => 'em_student',
            'posts_per_page' => -1,
            'orderby'        => 'title',
            'order'          => 'ASC',
            'post_status'    => 'publish',
        ));

        include EM_PLUGIN_DIR . 'admin/views/result-meta-box.php';
    }

    public static function save($post_id)
    {
        if (
            ! isset($_POST['em_result_nonce']) ||
            ! wp_verify_nonce($_POST['em_result_nonce'], 'em_save_result_meta')
        ) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (! current_user_can('edit_post', $post_id)) {
            return;
        }

        if (isset($_POST['result_exam_id'])) {
            update_post_meta(
                $post_id,
                'result_exam_id',
                absint($_POST['result_exam_id'])
            );
        }

        if (isset($_POST['result_student_id'])) {
            update_post_meta(
                $post_id,
                'result_student_id',
                absint($_POST['result_student_id'])
            );
        }

        if (isset($_POST['result_marks']) && $_POST['result_marks'] !== '') {
            $sanitized_mark = max(0, min(100, absint($_POST['result_marks'])));
            update_post_meta($post_id, 'result_marks', $sanitized_mark);
        } else {
            delete_post_meta($post_id, 'result_marks');
        }
    }
}
