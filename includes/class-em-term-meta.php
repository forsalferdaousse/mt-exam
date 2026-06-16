<?php

if (! defined('ABSPATH')) {
    exit;
}

class EM_Term_Meta
{
    public static function init()
    {
        add_action('em_term_add_form_fields',  array(__CLASS__, 'add_fields'));
        add_action('em_term_edit_form_fields', array(__CLASS__, 'edit_fields'));
        add_action('created_em_term',          array(__CLASS__, 'save'));
        add_action('edited_em_term',           array(__CLASS__, 'save'));
    }

    public static function add_fields()
    {
        wp_nonce_field('em_term_meta_action', 'em_term_meta_nonce');
?>
        <div class="form-field">
            <label for="term_start_date"><?php esc_html_e('Start Date', 'mt-exam'); ?></label>
            <input type="date" name="term_start_date" id="term_start_date" />
        </div>
        <div class="form-field">
            <label for="term_end_date"><?php esc_html_e('End Date', 'mt-exam'); ?></label>
            <input type="date" name="term_end_date" id="term_end_date" />
        </div>
    <?php
    }

    public static function edit_fields($term)
    {
        $start = get_term_meta($term->term_id, 'term_start_date', true);
        $end   = get_term_meta($term->term_id, 'term_end_date',   true);
    ?>
        <tr class="form-field">
            <th>
                <label for="term_start_date"><?php esc_html_e('Start Date', 'mt-exam'); ?></label>
                <?php wp_nonce_field('em_term_meta_action', 'em_term_meta_nonce'); ?>
            </th>
            <td><input type="date" name="term_start_date" id="term_start_date" value="<?php echo esc_attr($start); ?>" /></td>
        </tr>
        <tr class="form-field">
            <th><label for="term_end_date"><?php esc_html_e('End Date', 'mt-exam'); ?></label></th>
            <td><input type="date" name="term_end_date" id="term_end_date" value="<?php echo esc_attr($end); ?>" /></td>
        </tr>
<?php
    }

    public static function save($term_id)
    {
        if (! isset($_POST['em_term_meta_nonce']) || ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['em_term_meta_nonce'])), 'em_term_meta_action')) {
            return;
        }

        if (! current_user_can('manage_categories')) {
            return;
        }

        if (isset($_POST['term_start_date'])) {
            update_term_meta($term_id, 'term_start_date', sanitize_text_field(wp_unslash($_POST['term_start_date'])));
        }
        if (isset($_POST['term_end_date'])) {
            update_term_meta($term_id, 'term_end_date', sanitize_text_field(wp_unslash($_POST['term_end_date'])));
        }
    }
}
