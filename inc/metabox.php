<?php
add_filter( 'rwmb_meta_boxes', 'your_prefix_register_meta_boxes' );
function your_prefix_register_meta_boxes( $meta_boxes ) {
	$meta_boxes[] = array (
		'title' => 'Untitled Meta Box',
		'id' => 'untitled-meta-box',
		'post_types' => array(
			0 => 'bcm_event',
		),
		'context' => 'normal',
		'priority' => 'high',
		'fields' => array(
			array (
				'id' => 'venue',
				'type' => 'text',
				'name' => 'Venue',
				'required' => 1,
			),
			array (
				'id' => 'event_status',
				'name' => 'Event Status',
				'type' => 'radio',
				'std' => 'Upcoming',
				'options' => array(
					'Active' => 'Active',
					'Past' => 'Past',
					'Upcoming' => 'Upcoming',
					'Canceled' => 'Canceled',
				),
				'required' => 1,
			),
			array (
				'id' => 'start_time',
				'type' => 'datetime',
				'name' => 'Start Time',
				'required' => 1,
			),
			array (
				'id' => 'end_time',
				'type' => 'datetime',
				'name' => 'End Time',
			),
			array (
				'id' => 'supervisors',
				'type' => 'user',
				'name' => 'Supervisors',
				'field_type' => 'select_advanced',
				'required' => 1,
				'multiple' => true,
			),
			array (
				'id' => 'employees',
				'type' => 'user',
				'name' => 'Employees',
				'field_type' => 'select_advanced',
				'multiple' => true,
				'required' => 1,
			),
		),
	);
	return $meta_boxes;
}