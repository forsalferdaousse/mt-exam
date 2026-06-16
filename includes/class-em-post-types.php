<?php

if (! defined('ABSPATH')) {
    exit;
}

class EM_Post_Types
{

    public static function init()
    {
        add_action('init', array(__CLASS__, 'register_post_types'));
        add_action('init', array(__CLASS__, 'register_taxonomy'));
    }

    public static function register_post_types()
    {
        self::register_students_cpt();
        self::register_subjects_cpt();
        self::register_exams_cpt();
        self::register_results_cpt();
    }

    private static function register_students_cpt()
    {
        register_post_type('em_student', array(
            'labels'          => array(
                'name'          => __('Students', 'mt-exam'),
                'singular_name' => __('Student',  'mt-exam'),
            ),
            'public'          => true,
            'capability_type' => 'post',
            'supports'        => array('title', 'editor'),
            'menu_icon'       => 'dashicons-groups',
            'show_in_rest'    => true,
        ));
    }

    private static function register_subjects_cpt()
    {
        register_post_type('em_subject', array(
            'labels'          => array(
                'name'          => __('Subjects', 'mt-exam'),
                'singular_name' => __('Subject',  'mt-exam'),
            ),
            'public'          => true,
            'capability_type' => 'post',
            'supports'        => array('title'),
            'menu_icon'       => 'dashicons-book-alt',
            'show_in_rest'    => true,
        ));
    }

    private static function register_exams_cpt()
    {
        register_post_type('em_exam', array(
            'labels'          => array(
                'name'          => __('Exams', 'mt-exam'),
                'singular_name' => __('Exam',   'mt-exam'),
            ),
            'public'          => true,
            'capability_type' => 'post',
            'supports'        => array('title', 'editor'),
            'menu_icon'       => 'dashicons-book',
            'show_in_rest'    => true,
        ));
    }

    private static function register_results_cpt()
    {
        register_post_type('em_result', array(
            'labels'          => array(
                'name'          => __('Results', 'mt-exam'),
                'singular_name' => __('Result',  'mt-exam'),
            ),
            'public'          => true,
            'capability_type' => 'post',
            'supports'        => array('title'),
            'menu_icon'       => 'dashicons-performance',
            'show_in_rest'    => true,
        ));
    }

    public static function register_taxonomy()
    {
        register_taxonomy('em_term', array('em_exam'), array(
            'labels'       => array(
                'name'          => __('Terms', 'mt-exam'),
                'singular_name' => __('Term',  'mt-exam'),
            ),
            'public'       => true,
            'hierarchical' => false,
            'show_ui'      => true,
            'show_in_rest' => true,
        ));
    }
}
