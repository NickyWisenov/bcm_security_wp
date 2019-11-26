<?php
if ( ! class_exists('BCM') ):

require_once( 'class.bcm_report_table.php' );

class BCM {

	function __construct() {
		// Do Nothing
	}

	/* init class */
	function initialize() {
		// Define Constants

		// Add init hook
		add_action('init', array($this, 'init_hook'));

		// Add CSS and JS for Front End (Short Code)
		add_action('wp_enqueue_scripts', array($this, 'bcm_front_scripts_add'));

		// Add Short Code for BCM Pages
		add_shortcode('bcm_page', array($this, 'bcm_page_shortcode'));

		if ( is_admin() ) {
			$this->admin_init();
		}

		if ( wp_doing_ajax() ) {
			$this->ajax_init();
		}

		// Add Redirect Action after Login
		// add_action('wp_login', array($this, 'bcm_redirect_after_login'));
	}

	/* admin handler */
	function admin_init() {
		// Add CSS and JS scripts
		add_action('admin_enqueue_scripts', array($this, 'bcm_add_custom_scripts'));
	}

	/* ajax request handler */
	function ajax_init() {
		// Hook Ajax Select Event
		add_action('wp_ajax_select_event', array($this, 'bcm_ajax_event_select'));

		// Hook Ajax Start Tracking
		add_action('wp_ajax_start_tracking', array($this, 'bcm_ajax_event_start_tracking'));

		// Hook Ajax End Tracking
		add_action('wp_ajax_end_tracking', array($this, 'bcm_ajax_event_end_tracking'));

		// Hook Ajax Pause Tracking
		add_action('wp_ajax_pause_tracking', array($this, 'bcm_ajax_event_pause_tracking'));
	}

	/* init hook handler */
	function init_hook() {
		$labels = array(
				'name'                  => _x( 'BCM Events', 'Post type general name', 'textdomain' ),
				'singular_name'         => _x( 'BCM Event', 'Post type singular name', 'textdomain' ),
				'menu_name'             => _x( 'BCM Events', 'Admin Menu text', 'textdomain' ),
				'name_admin_bar'        => _x( 'BCM Event', 'Add New on Toolbar', 'textdomain' ),
				'add_new'               => __( 'Add New', 'textdomain' ),
				'add_new_item'          => __( 'Add New Event', 'textdomain' ),
				'new_item'              => __( 'New Event', 'textdomain' ),
				'edit_item'             => __( 'Edit Event', 'textdomain' ),
				'view_item'             => __( 'View Event', 'textdomain' ),
				'all_items'             => __( 'All Events', 'textdomain' ),
				'search_items'          => __( 'Search Events', 'textdomain' ),
				'parent_item_colon'     => __( 'Parent Events:', 'textdomain' ),
				'not_found'             => __( 'No Events found.', 'textdomain' ),
				'not_found_in_trash'    => __( 'No Events found in Trash.', 'textdomain' ),
		);

		$args = array(
				'labels'             => $labels,
				'public'             => true,
				'publicly_queryable' => true,
				'show_ui'            => true,
				'show_in_menu'       => true,
				'query_var'          => true,
				'rewrite'            => array( 'slug' => 'bcm_event' ),
				'capability_type'    => 'post',
				'has_archive'        => true,
				'hierarchical'       => false,
				'menu_position'      => null,
				'supports'           => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments' ),
		);

		register_post_type( 'bcm_event', $args );

		// $this->bcm_redirect_to_login_page();
	}

	/* enqueue scripts */
	function bcm_front_scripts_add() {
		// Bootstrap Include
		wp_enqueue_style('bcm-bootstrap-css', 'https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css');
		wp_enqueue_script('bootstrap-js','https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js', array('jquery'), true);

		// Custom Code Include
		$version = wp_rand();
		wp_enqueue_style('bcm-front-css', BCM_URL . 'css/bcm-front.css', array(), $version);
		wp_enqueue_script('bcm-front-js', BCM_URL . 'js/bcm-front.js', array('jquery'), $version, true);

		// Localize the Script
		wp_localize_script('bcm-front-js', 'bcm_obj', array(
			'ajaxurl' => admin_url( 'admin-ajax.php' )
		));
	}

	/* bcm_page shortcode definition */
	function bcm_page_shortcode() {
		$post_type = 'bcm_event';
		$user_id = get_current_user_id();

		$args = array(
			'post_type' => $post_type,
			'post_status' => 'publish',
			'posts_per_page' => -1,
			'meta_query' => array(
				'relation' => 'AND',
				array(
					'key' => 'event_status',
					'value' => array('Active', 'Upcoming'),
					'compare' => 'IN'
				),
				array(
					'relation' => 'OR',
					array(
						'key' => 'supervisors',
						'value' => $user_id,
					),
					array(
						'key' => 'employees',
						'value' => $user_id,
					)
				)
			)
		);
		$query = new WP_Query($args);
		?>
		<div class="bcm-page-wrapper page-wrapper__employee">
			<div class="row bcm-page-banner">
				<div class="form-group event-select">
					<label for="eventSelect">Select the Event</label>
					<select class="form-control" id="eventSelect">
						<option value="0" disabled selected>-- Please Select Event --</option>
						<?php if( $query->have_posts() ):?>
							<?php while ($query->have_posts()) : $query->the_post(); ?>
								<option value="<?php the_ID() ?>"><?php the_title(); ?></option>
							<?php endwhile; ?>
						<?php endif; ?>
						<?php wp_reset_query(); ?>
					</select>
				</div>
			</div>
			<div class="bcm-page-content"></div>
		</div>
		<div class="bcm-ajax-loader"></div>
		<?php
	}

	/* admin enqueue scripts */
	function bcm_add_custom_scripts() {
		wp_enqueue_style('bcm-admin-css', BCM_URL . 'css/bcm-admin.css');
		wp_enqueue_script('bcm-admin', BCM_URL . 'js/bcm-admin.js', array('jquery'), true);
	}

	/* event select ajax handler */
	function bcm_ajax_event_select() {
		// validation
		if ( empty( $_POST['evt_id'] ) ) {
			wp_send_json_error( array( 'msg' => 'wrong data' ) );
		}

		$evt_id = intval($_POST['evt_id']);

		// Get All Event Data
		$evt_data = get_post($evt_id);
		$evt_venue = get_post_meta($evt_id, 'venue', false);
		$evt_status = get_post_meta($evt_id, 'event_status', false);
		$evt_supervisor = get_post_meta($evt_id, 'supervisors', true);
		$evt_start_time = get_post_meta($evt_id, 'start_time', false);
		$evt_end_time = get_post_meta($evt_id, 'end_time', false);
		$evt_employees = get_post_meta($evt_id, 'employees', true);

		$evt = array(
			'id' => $evt_id,
			'title' => $evt_data->post_title,
			'venue' => $evt_venue,
			'status' => $evt_status,
			'supervisor' => $evt_supervisor,
			'start_time' => $evt_start_time,
			'end_time' => $evt_end_time,
			'employees' => $evt_employees
		);

		// get start and end time of current user for the current event
		global $wpdb;
		$evt_table_name = $wpdb->prefix . 'evt_employee';
		$user_id = get_current_user_id();

		$row = $wpdb->get_row( $wpdb->prepare("SELECT * FROM $evt_table_name WHERE evt_id = %d and user_id = %d", $evt['id'], $user_id ), ARRAY_A );
		if ( ! empty( $row ) ) {
			$start_time = strtotime($row['start_time']);
			$end_time = strtotime($row['end_time']);
			if ( empty( $end_time ) ) {
				$time_diff = time() - $start_time;
				$status = "active";
			} else {
				$time_diff = $end_time - $start_time;
				$status = "past";
			}
		} else {
			$time_diff = 0;
			$status = "upcoming";
		}

		$time_data = array( 'time_diff' => $time_diff, 'status' => $status );


		// Check Current User is Supervisor
		$isSuperVisor = in_array($user_id, $evt_supervisor) ? true : false;

		if ($isSuperVisor) {
			$response = array(
				'time_data' => $time_data,
				'response_html' => $this->bcm_get_employee_html($evt, $time_data),
				'modal_html' => $this->bcm_get_employee_modal_html()
			);
			wp_send_json_success( $response );
		} else {
			$response = array(
				'time_data' => $time_data,
				'response_html' => $this->bcm_get_employee_html($evt, $time_data),
				'modal_html' => $this->bcm_get_employee_modal_html()
			);
			wp_send_json_success( $response );
		}

		wp_die();
	}

	/* start tracking ajax handler */
	function bcm_ajax_event_start_tracking() {
		global $wpdb;
		if ( empty( $_POST['evt_id'] ) ) {
			wp_send_json_error( array( 'msg' => 'wrong data' ) );
		}

		$evt_id = intval($_POST['evt_id']);
		$user_id = get_current_user_id();

		$evt_table_name = $wpdb->prefix . 'evt_employee';
		$row_id = $wpdb->get_var( $wpdb->prepare(
			"SELECT id FROM $evt_table_name WHERE evt_id = %d and user_id = %d",
			$evt_id, $user_id
		));

		$start_time = current_time('mysql');
		if ( empty( $row_id ) ) {
			// insert
			$result = $wpdb->insert(
				$evt_table_name,
				array(
					'user_id' => $user_id,
					'evt_id' => $evt_id,
					'start_time' => $start_time,
					'start_ip_address' => $_SERVER['REMOTE_ADDR']
				)
			);
		} else {
			$result = $wpdb->update(
				$evt_table_name,
				array(
					'start_time' => $start_time,
					'start_ip_address' => $_SERVER['REMOTE_ADDR']
				),
				array(
					'id' => $row_id,
				)
			);
		}

		if ($result) {
			wp_send_json_success(array('msg' => 'Started correctly'));
		} else {
			wp_send_json_error(array('msg' => 'error'));
		}
	}

	/* end tracking ajax handler */
	function bcm_ajax_event_end_tracking() {
		global $wpdb;
		if ( empty( $_POST['evt_id'] ) ) {
			wp_send_json_error( array( 'msg' => 'wrong data' ) );
		}

		$evt_id = intval($_POST['evt_id']);
		$user_id = get_current_user_id();

		$evt_table_name = $wpdb->prefix . 'evt_employee';
		$row_id = $wpdb->get_var( $wpdb->prepare(
			"SELECT id FROM $evt_table_name WHERE evt_id = %d and user_id = %d",
			$evt_id, $user_id
		));

		$end_time = current_time('mysql');
		if ( empty( $row_id ) ) {
			// insert
			$result = $wpdb->insert(
				$evt_table_name,
				array(
					'user_id' => $user_id,
					'evt_id' => $evt_id,
					'end_time' => $end_time,
					'end_ip_address' => $_SERVER['REMOTE_ADDR'],
					'track_status' => 'ended'
				)
			);
		} else {
			$result = $wpdb->update(
				$evt_table_name,
				array(
					'end_time' => $end_time,
					'end_ip_address' => $_SERVER['REMOTE_ADDR']
				),
				array(
					'id' => $row_id,
				)
			);
		}

		if ($result) {
			wp_send_json_success(array('msg' => 'Ended correctly'));
		} else {
			wp_send_json_error(array('msg' => 'error'));
		}
	}

	/* pause tracking ajax handler */
	function bcm_ajax_event_pause_tracking() {
		global $wpdb;
		if ( empty( $_POST['evt_id'] ) ) {
			wp_send_json_error( array( 'msg' => 'wrong data' ) );
		}

		$evt_id = intval($_POST['evt_id']);
		$user_id = get_current_user_id();

		$evt_table_name = $wpdb->prefix . 'evt_employee';
		$row_id = $wpdb->get_var( $wpdb->prepare(
			"SELECT id FROM $evt_table_name WHERE evt_id = %d and user_id = %d",
			$evt_id, $user_id
		));

		$activity = $wpdb->get_var( $wpdb->prepare(
			"SELECT activity FROM $evt_table_name WHERE evt_id = %d and user_id = %d",
			$evt_id, $user_id
		));

		$track_status = $wpdb->get_var( $wpdb->prepare(
			"SELECT track_status FROM $evt_table_name WHERE evt_id = %d and user_id = %d",
			$evt_id, $user_id
		));

		$pause_time = current_time('mysql');

		if ( !empty( $row_id ) && $track_status != 'paused' ) {
			$result = $wpdb->update(
				$evt_table_name,
				array(
					'track_status' => 'paused',
				),
				array(
					'id' => $row_id,
				)
			);
		}

		if ($result) {
			wp_send_json_success(array('msg' => 'Paused correctly'));
		} else {
			wp_send_json_error(array('msg' => 'error'));
		}
	}

	/* main content for employee  */
	function bcm_get_employee_html($evt, $time_data) {
		ob_start();
		?>
			<div class="row employee-role">
				Employee
			</div>
			<div class="row event-details">
				<div class="col-12 event-details__status event-details__status--active">
					<?php echo $evt['status'][0] ?>
				</div>
				<div class="col-6">
					<div class="event-details__item">
						<div class="event-details__item-title">
							Event Name
						</div>
						<div class="event-details__item-value">
							<?php echo $evt['title'] ?>
						</div>
					</div>
					<div class="event-details__item">
						<div class="event-details__item-title">
							Event Location
						</div>
						<div class="event-details__item-value">
							<?php echo $evt['venue'][0] ?>
						</div>
					</div>
				</div>
				<div class="col-6">
					<div class="event-details__item">
						<div class="event-details__item-title">
							Start Time
						</div>
						<div class="event-details__item-value">
							<?php echo $evt['start_time'][0] ?>
						</div>
					</div>
					<div class="event-details__item">
						<div class="event-details__item-title">
							End Time
						</div>
						<div class="event-details__item-value">
							<?php echo $evt['end_time'][0] ?>
						</div>
					</div>
				</div>
			</div>
			<!-- BEGIN EMPLOYEE -->
			<div class="row track-hours">
				<div class="col-6 track-hours__item">
					<div class="track-hours__item-title">
						Date
					</div>
					<div class="track-hours__item-value">
						<span><?php echo date('m') ?></span>/<span><?php echo date('d') ?></span>/<span><?php echo date('Y') ?></span>
					</div>
				</div>
				<div class="col-6 track-hours__item">
					<div class="track-hours__item-title">
						Time Elapsed
					</div>
					<div class="track-hours__item-value">
						<?php echo $this->bcm_get_time_elapsed( $time_data['time_diff'], $time_data['status'] ); ?>
					</div>
				</div>
			</div>
			<div class="row track-buttons">
				<button type="button" class="btn btn-success btn-md start-btn" <?php if ( $time_data['status'] != 'upcoming' ) echo ' disabled="disabled"' ?>>
					START
				</button>
				<button type="button" class="btn btn-danger btn-md end-btn" <?php if ( $time_data['status'] != 'active' ) echo ' disabled="disabled"' ?>>
					END
				</button>
			</div>
			<div class="row track-buttons">
				<button type="button" class="btn btn-warning btn-md pause-btn" <?php if ( $time_data['status'] != 'active' ) echo ' disabled="disabled"' ?>>
					PAUSE
				</button>
				<button type="button" class="btn btn-warning btn-md resume-btn" <?php if ( $time_data['status'] != 'active' ) echo ' disabled="disabled"' ?>>
					RESUME
				</button>
			</div>
		<?php
		return ob_get_clean();
	}

	function bcm_get_time_elapsed( $time_diff, $status ) {
		$hour = $time_diff / 3600;
		$min = ( $time_diff % 3600 ) / 60;
		$sec = $time_diff % 60;
		return sprintf('<span id="disHour" data-second="%d" data-status="%s">%02d</span>hrs<span id="disMin">%02d</span>mins<span id="disSec">%02d</span>sec', $time_diff, $status, $hour, $min, $sec);
	}

	/* modal content for employee */
	function bcm_get_employee_modal_html() {
		ob_start();
		?>
		<!-- Modal -->
		<div class="modal fade" id="bcmConfirmModal" tabindex="-1" role="dialog" aria-labelledby="confirmModalLabel" aria-hidden="true">
			<div class="modal-dialog modal-dialog-centered" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="confirmModalLabel">Confirm</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						Are you sure to <b>END</b> track?
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary bcm-modal-no-btn" data-dismiss="modal">NO</button>
						<button type="button" class="btn btn-primary bcm-modal-yes-btn">YES</button>
					</div>
				</div>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	/* main content for supervisor */
	function bcm_get_supervisor_html($evt) {
	}

	function bcm_redirect_to_login_page() {
		$protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
		$CurPageURL = $protocol.$_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		if (!is_user_logged_in()) {
			if (!in_array('login', explode('/', $CurPageURL))) {
				wp_redirect(home_url() . '/login');
				exit;
			}
		} else {
			$user = wp_get_current_user();
			$isAjax = $_POST['action'];
			if (!in_array( 'administrator', (array) $user->roles) && $isAjax == '') {
				if ($CurPageURL != home_url() . '/') {
					wp_redirect(home_url());
					exit;
				}
			}
		}
	}

	function bcm_redirect_after_login($login) {
		if (is_user_logged_in()) {
			$user = get_userdatabylogin($login);
			if ( !in_array( 'administrator', (array) $user->roles ) ) {
				wp_redirect( home_url() );
				exit;
			}
		}
	}
}

endif;