<?php

if (! defined('ABSPATH')) {
	exit;
}

$stats   = isset($stats) && is_array($stats) ? $stats : array();
$pdf_url = isset($pdf_url) ? $pdf_url : '';
?>

<div class="wrap">
	<h1><?php esc_html_e('Student Statistics Report', 'mt-exam'); ?></h1>

	<p>
		<a href="<?php echo esc_url($pdf_url); ?>" class="button button-primary">
			<?php esc_html_e('Export PDF', 'mt-exam'); ?>
		</a>
	</p>

	<table class="widefat striped">
		<thead>
			<tr>
				<th><?php esc_html_e('Student',     'mt-exam'); ?></th>
				<th><?php esc_html_e('Term Totals', 'mt-exam'); ?></th>
				<th><?php esc_html_e('Average',     'mt-exam'); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($stats as $student) : ?>
				<tr>
					<td><?php echo esc_html($student['name']); ?></td>
					<td>
						<?php foreach ($student['terms'] as $term => $total) : ?>
							<?php echo esc_html($term . ': ' . $total); ?><br>
						<?php endforeach; ?>
					</td>
					<td><?php echo esc_html((string) $student['avg']); ?></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>