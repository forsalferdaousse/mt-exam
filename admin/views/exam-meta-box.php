<?php

if (! defined('ABSPATH')) {
	exit;
}

$start      = isset($start) ? $start : '';
$end        = isset($end) ? $end : '';
$subject_id = isset($subject_id) ? (int) $subject_id : 0;
$term_id    = isset($term_id) ? (int) $term_id : 0;
$subjects   = isset($subjects) && is_array($subjects) ? $subjects : array();
$terms      = isset($terms) && is_array($terms) ? $terms : array();

?>

<table class="form-table">
	<tr>
		<th><label for="exam_start_datetime"><?php esc_html_e('Start Date & Time', 'mt-exam'); ?></label></th>
		<td><input type="datetime-local" name="exam_start_datetime" id="exam_start_datetime" value="<?php echo esc_attr($start); ?>" /></td>
	</tr>
	<tr>
		<th><label for="exam_end_datetime"><?php esc_html_e('End Date & Time', 'mt-exam'); ?></label></th>
		<td><input type="datetime-local" name="exam_end_datetime" id="exam_end_datetime" value="<?php echo esc_attr($end); ?>" /></td>
	</tr>
	<tr>
		<th><label for="exam_subject_id"><?php esc_html_e('Subject', 'mt-exam'); ?></label></th>
		<td>
			<select name="exam_subject_id" id="exam_subject_id">
				<option value=""><?php esc_html_e('-- Select Subject --', 'mt-exam'); ?></option>
				<?php foreach ($subjects as $subject) : ?>
					<option value="<?php echo esc_attr($subject->ID); ?>" <?php selected($subject_id, $subject->ID); ?>>
						<?php echo esc_html($subject->post_title); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</td>
	</tr>
	<tr>
		<th><label for="exam_term_id"><?php esc_html_e('Academic Term', 'mt-exam'); ?></label></th>
		<td>
			<select name="exam_term_id" id="exam_term_id">
				<option value=""><?php esc_html_e('-- Select Term --', 'mt-exam'); ?></option>
				<?php foreach ($terms as $term) : ?>
					<option value="<?php echo esc_attr($term->term_id); ?>" <?php selected($term_id, $term->term_id); ?>>
						<?php echo esc_html($term->name); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</td>
	</tr>
</table>