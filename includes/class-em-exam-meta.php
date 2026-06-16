<?php

if (! defined('ABSPATH')) {
    exit;
}

class EM_Exam_Meta
{

    public static function init()
    {
        add_action('add_meta_boxes',      array(__CLASS__, 'register_meta_box'));
        add_action('save_post_em_exam',   array(__CLASS__, 'save'));
    }

    public static function register_meta_box()
    {
        add_meta_box(
            'em_exam_details',
            __('Exam Details', 'mt-exam'),
            array(__CLASS__, 'render'),
            'em_exam',
            'normal',
            'high'
        );
    }

    public static function render($post)
    {
        wp_nonce_field('em_save_exam_meta', 'em_exam_nonce');

        $start      = get_post_meta($post->ID, 'exam_start_datetime', true);
        $end        = get_post_meta($post->ID, 'exam_end_datetime',   true);
        $subject_id = get_post_meta($post->ID, 'exam_subject_id',     true);
        $term_id    = get_post_meta($post->ID, 'exam_term_id',        true);

        $subjects = get_posts(array(
            'post_type'      => 'em_subject',
            'posts_per_page' => -1,
            'orderby'        => 'title',
            'order'          => 'ASC',
            'post_status'    => 'publish',
        ));

        $terms = get_terms(array(
            'taxonomy'   => 'em_term',
            'hide_empty' => false,
        ));

        include EM_PLUGIN_DIR . 'admin/views/exam-meta-box.php';
    }

    public static function save($post_id)
    {
        if (! isset($_POST['em_exam_nonce']) || ! wp_verify_nonce($_POST['em_exam_nonce'], 'em_save_exam_meta')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (! current_user_can('edit_post', $post_id)) {
            return;
        }

        $fields = array(
            'exam_start_datetime' => 'sanitize_text_field',
            'exam_end_datetime'   => 'sanitize_text_field',
        );

        foreach ($fields as $key => $sanitize_cb) {
            if (isset($_POST[$key])) {
                update_post_meta($post_id, $key, $sanitize_cb(wp_unslash($_POST[$key])));
            }
        }

        if (isset($_POST['exam_subject_id'])) {
            update_post_meta($post_id, 'exam_subject_id', absint($_POST['exam_subject_id']));
        }

        if (isset($_POST['exam_term_id'])) {
            $term_id = absint($_POST['exam_term_id']);
            update_post_meta($post_id, 'exam_term_id', $term_id);

            if ($term_id > 0) {
                wp_set_object_terms($post_id, array($term_id), 'em_term', false);
            }
        }
    }
}
