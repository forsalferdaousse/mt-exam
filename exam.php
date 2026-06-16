<?php

/**
 * Plugin Name: Exam Management
 * Plugin URI: https://example.com
 * Description: A WordPress plugin for screening senior developer applicants with custom post types for students, exams, results.
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://example.com
 * License: GPL-2.0+
 */

if (! defined('ABSPATH')) {
	exit;
}

define('EM_VERSION',    '1.0.0');
define('EM_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('EM_PLUGIN_URL', plugin_dir_url(__FILE__));

require_once EM_PLUGIN_DIR . 'vendor/autoload.php';

require_once EM_PLUGIN_DIR . 'includes/class-em-post-types.php';
require_once EM_PLUGIN_DIR . 'includes/class-em-term-meta.php';
require_once EM_PLUGIN_DIR . 'includes/class-em-exam-meta.php';
require_once EM_PLUGIN_DIR . 'includes/class-em-result-meta.php';
require_once EM_PLUGIN_DIR . 'includes/class-em-ajax.php';
require_once EM_PLUGIN_DIR . 'includes/class-em-shortcodes.php';
require_once EM_PLUGIN_DIR . 'admin/class-em-import.php';
require_once EM_PLUGIN_DIR . 'admin/class-em-statistics.php';

function em_boot()
{
	EM_Post_Types::init();
	EM_Term_Meta::init();
	EM_Exam_Meta::init();
	EM_Result_Meta::init();
	EM_Ajax::init();
	EM_Shortcodes::init();
	EM_Import::init();
	EM_Statistics::init();
}
add_action('plugins_loaded', 'em_boot');

// Clear cache
function em_plugin_activate()
{
	delete_transient('em_top_students');
}
register_activation_hook(__FILE__, 'em_plugin_activate');
