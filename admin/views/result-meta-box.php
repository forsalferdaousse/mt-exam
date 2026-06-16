<?php

if (! defined('ABSPATH')) {
    exit;
}

$selected_exam       = isset($selected_exam) ? (int) $selected_exam : 0;
$selected_student_id = isset($selected_student_id) ? (int) $selected_student_id : 0;
$saved_marks         = (isset($saved_marks) && $saved_marks !== '') ? (int) $saved_marks : '';
$exams               = isset($exams) && is_array($exams) ? $exams : array();
$students            = isset($students) && is_array($students) ? $students : array();
?>

<table class="form-table">
    <tr>
        <th><label for="result_exam_id"><?php esc_html_e('Select Exam', 'mt-exam'); ?></label></th>
        <td>
            <select name="result_exam_id" id="result_exam_id" class="regular-text">
                <option value=""><?php esc_html_e('-- Select Exam --', 'mt-exam'); ?></option>
                <?php foreach ($exams as $exam) : ?>
                    <option value="<?php echo esc_attr($exam->ID); ?>" <?php selected($selected_exam, $exam->ID); ?>>
                        <?php echo esc_html($exam->post_title); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </td>
    </tr>

    <tr>
        <th><label for="result_student_id"><?php esc_html_e('Select Student', 'mt-exam'); ?></label></th>
        <td>
            <select name="result_student_id" id="result_student_id" class="regular-text">
                <option value=""><?php esc_html_e('-- Select Student --', 'mt-exam'); ?></option>
                <?php foreach ($students as $student) : ?>
                    <option value="<?php echo esc_attr($student->ID); ?>" <?php selected($selected_student_id, $student->ID); ?>>
                        <?php echo esc_html($student->post_title); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </td>
    </tr>

    <tr>
        <th><label for="result_marks"><?php esc_html_e('Marks (out of 100)', 'mt-exam'); ?></label></th>
        <td>
            <input
                type="number"
                name="result_marks"
                id="result_marks"
                value="<?php echo esc_attr($saved_marks); ?>"
                min="0"
                max="100"
                class="small-text"
                style="width:80px;" />
        </td>
    </tr>
</table>