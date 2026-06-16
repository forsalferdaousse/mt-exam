<?php

if (! defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap">
    <h1><?php esc_html_e('Import Results', 'mt-exam'); ?></h1>

    <?php echo wp_kses_post($notice); ?>

    <form method="post" enctype="multipart/form-data">
        <?php wp_nonce_field('em_import_results'); ?>

        <table class="form-table">
            <tr>
                <th><label for="results_csv"><?php esc_html_e('CSV File', 'mt-exam'); ?></label></th>
                <td>
                    <input type="file" name="results_csv" id="results_csv" accept=".csv" required />
                    <p class="description">
                        <?php esc_html_e('Expected columns: student_id, exam_id, marks (0–100). First row is treated as a header and skipped.', 'mt-exam'); ?>
                    </p>
                </td>
            </tr>
        </table>

        <?php submit_button(__('Import CSV', 'mt-exam')); ?>
    </form>
</div>