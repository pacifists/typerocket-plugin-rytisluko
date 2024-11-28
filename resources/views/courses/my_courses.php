<div class="rytisluko-course-container">
<?php
$filtered_courses = [];
foreach ($courses as $key => $course) {
	$filtered_courses[$course->course_id] = $course;
}

foreach ($filtered_courses as $course) {
?>
	<div class="rytisluko-course">
		<div class="rytisluko-course-image">
			<a href="<?php echo $course->magic_link; ?>" target="_blank">
				<?php echo \TypeRocket\Html\Html::img(get_the_post_thumbnail_url($course->Course->ID, 'medium'), ['style' => 'border-radius: 10px;']); ?>	
			</a>
		</div>
		<div class="rytisluko-course-link">
			<h3>
				<a href="<?php echo $course->magic_link; ?>" target="_blank">
					<?php echo $course->Course->post_title; ?>
				</a>
			</h3>
		</div>
	</div>
<?php } ?>
</div>