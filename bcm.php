<?php
/*
Plugin Name: BCM Security Web Application Plugin
Description: BCM Security APP
Version: 1.0.0
Text Domain: bcm
*/

if ( ! defined ( 'ABSPATH' ) ) exit; // Exit if accessed directly


// Include WP_List_Table
if( ! class_exists( 'WP_List_Table' ) ) {
  require_once( ABSPATH . 'wp-admin/includes/class-wp-screen.php' );
  require_once( ABSPATH . 'wp-admin/includes/screen.php' );
  require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
  require_once( ABSPATH . 'wp-admin/includes/template.php' );
}

if ( ! class_exists('BCM_REPORT_TABLE') ):

class BCM_REPORT_TABLE extends WP_List_Table
{
  private static $instance;

  var $example_data = array(
    array('ID' => 1,'booktitle' => 'Quarter Share', 'author' => 'Nathan Lowell',
          'isbn' => '978-0982514542'),
    array('ID' => 2, 'booktitle' => '7th Son: Descent','author' => 'J. C. Hutchins',
          'isbn' => '0312384378'),
    array('ID' => 3, 'booktitle' => 'Shadowmagic', 'author' => 'John Lenahan',
          'isbn' => '978-1905548927'),
    array('ID' => 4, 'booktitle' => 'The Crown Conspiracy', 'author' => 'Michael J. Sullivan',
          'isbn' => '978-0979621130'),
    array('ID' => 5, 'booktitle'     => 'Max Quick: The Pocket and the Pendant', 'author'    => 'Mark Jeffrey',
          'isbn' => '978-0061988929'),
    array('ID' => 6, 'booktitle' => 'Jack Wakes Up: A Novel', 'author' => 'Seth Harwood',
          'isbn' => '978-0307454355')
  );

  var $paginated_date = null;
  /**
   * __construct
   *
   * A dummy constructor to ensure BCM is only setup once
   * @since 1.0.0
   * @param void
   * @return void
   */
  function __construct() {
    parent::__construct();
  }


  /**
   * usort_reorder
   *
   * Sort data with ordering conference
   *
   * @param void
   * @return array
   */
  function usort_reorder( $a, $b ) {
    // If no sort, default to title
    $orderby = ( ! empty( $_GET['orderby'] ) ) ? $_GET['orderby'] : 'booktitle';
    // If no order, default to asc
    $order = ( ! empty($_GET['order'] ) ) ? $_GET['order'] : 'asc';
    // Determine sort order
    $result = strcmp( $a[$orderby], $b[$orderby] );
    // Send final sort direction to usort
    return ( $order === 'asc' ) ? $result : -$result;
  }
  /**
   * get_columns
   *
   * Get columns for WP List
   *
   * @param void
   * @return array
   */
  function get_columns(){
    $columns = array(
      'cb'        => '<input type="checkbox" />',
      'booktitle' => 'Title',
      'author'    => 'Author',
      'isbn'      => 'ISBN'
    );
    return $columns;
  }

  /**
   * prepare_items
   *
   * Prepare Items for WP List
   *
   * @param void
   * @return array
   */
  function prepare_items() {
    $columns = $this->get_columns();
    $hidden = array('id');
    $sortable = $this->get_sortable_columns();

    $per_page = 5;
    $current_page = $this->get_pagenum();
    $total_items = count($this->example_data);

    $this->paginated_date = array_slice($this->example_data, (($current_page - 1) * $per_page), $per_page);

    $this->set_pagination_args(array(
        'total_items' => $total_items,
        'per_page' => $per_page
    ));

    $this->_column_headers = array($columns, $hidden, $sortable);
    usort( $this->paginated_date, array( $this, 'usort_reorder' ));
    $this->items = $this->paginated_date;
  }

  /**
   * column_default
   *
   * Prepare Items for WP List
   *
   * @param void
   * @return array
   */
  function column_default( $item, $column_name ) {
    switch( $column_name ) {
      case 'booktitle':
      case 'author':
      case 'isbn':
        return $item[ $column_name ];
      default:
        return print_r( $item, true ) ; //Show the whole array for troubleshooting purposes
    }
  }

  /**
   * get_sortable_columns
   *
   * Prepare Items for WP List
   *
   * @param void
   * @return array
   */
  function get_sortable_columns() {
    $sortable_columns = array(
      'booktitle'  => array('booktitle',false),
      'author' => array('author',false),
      'isbn'   => array('isbn',false)
    );
    return $sortable_columns;
  }

  /**
   * column_booktitle
   *
   * Action Display on Hover Title
   *
   * @param void
   * @return array
   */
  function column_booktitle($item) {
    $actions = array(
              'edit'      => sprintf('<a href="?page=%s&action=%s&book=%s">Edit</a>',$_REQUEST['page'],'edit',$item['ID']),
              'delete'    => sprintf('<a href="?page=%s&action=%s&book=%s">Delete</a>',$_REQUEST['page'],'delete',$item['ID']),
          );

    return sprintf('%1$s %2$s', $item['booktitle'], $this->row_actions($actions) );
  }

  /**
   * column_cb
   *
   * Display checkbox to select item
   *
   * @param void
   * @return array
   */
  function column_cb($item) {
    return sprintf(
      '<input type="checkbox" name="book[]" value="%s" />', $item['ID']
    );
  }

  /**
   * get_bulk_actions
   *
   * Get Bulk Actions
   *
   * @param void
   * @return array
   */
  function get_bulk_actions() {
    $actions = array(
      'delete'    => 'Delete'
    );
    return $actions;
  }
}

endif;

/**
 * BCM Class
 *
 */
if ( ! class_exists('BCM') ):

class BCM {

  private static $instance;

  var $bcmReportTable = null;

  /** @var string The plugin version number. */
  var $version = '1.0.0';

  /** @var array The plugin settings array. */
  var $settings = array();

  /** @var array The plugin data array. */
  var $data = array();

  /** @var array Storage for class instances. */
  var $instances = array();

  /**
   * __construct
   *
   * A dummy constructor to ensure BCM is only setup once
   * @since 1.0.0
   * @param void
   * @return void
   */
  function __construct() {
    // Do Nothing
  }

  /**
   * initialize
   *
   * Sets up the BCM plugin
   *
   * @since 1.0.0
   *
   * @param void
   * @return void
   */
  function initialize() {
    // Define Constants
    $this->define( 'BCM', true );
    $this->define( 'BCM_PATH', plugin_dir_path(__FILE__) );
    $this->define( 'BCM_BASENAME', plugin_basename(__FILE__) );
    $this->define( 'BCM_VERSION', $this->version );

    // Define Settings
    $this->settings = array(
      'name' => __('BCM', 'bcm'),
      'slug' => dirname( BCM_BASENAME ),
      'version' => BCM_VERSION,
      'basename' => BCM_BASENAME
    );

    // Add Admin Menu
    add_action('admin_menu', array($this, 'report_admin_menu'));

    // Add CSS and JS scripts
    add_action('admin_enqueue_scripts', array($this, 'bcm_add_custom_scripts'));

    // Add Short Code for BCM Pages
    add_shortcode('bcm_page', array($this, 'bcm_short_code'));

    // Add CSS and JS for Front End (Short Code)
    add_action('wp_enqueue_scripts', array($this, 'bcm_front_scripts_add'));

    // Add redirection to the login page
    add_action('init', array($this, 'bcm_redirect_to_login_page'));

    // Add Redirect Action after Login
    add_action('wp_login', array($this, 'bcm_redirect_after_login'));

    // Hook Ajax Select Event
    add_action('wp_ajax_select_event', array($this, 'bcm_front_select_event'));

    // Hook Ajax Start Tracking
    add_action('wp_ajax_start_tracking', array($this, 'bcm_event_start_tracking'));

    // Hook Ajax End Tracking
    add_action('wp_ajax_end_tracking', array($this, 'bcm_front_end_tracking'));
  }

  /**
	 * bcm_event_start_tracking
	 *
	 * Hook function when start tracking time
	 *
	 * @date	22/11/19
	 * @since	5.0.0
	 *
	 * @param	void
	 * @return	obj
	 */
  function bcm_event_start_tracking() {
    global $wpdb;

    // Get Request Data
    $start_time = $_POST['start_time'];
    $evt_id = $_POST['evt_id'];

    $current_user = wp_get_current_user();

    $exist = $wpdb->get_var('SELECT id from wp_evt_employee WHERE evt_id = $evt_id AND user_id = $current_user->ID');

    $result = false;
    if ($exist) {
      $result = $wpdb->update(
          'wp_evt_employee',
          array(
            'start_time' => $start_time
          ),
          array(
            'evt_id' => $evt_id,
            'user_id' => $current_user->ID
          ),
          array(
            '%d',
            '%d'
          ),
          array(
            '%d'
          )
        );
    } else {
      $result = $wpdb->insert(
        'wp_evt_employee',
        array(
          'evt_id' => $evt_id,
          'user_id' => $current_user->ID,
          'start_time' => $start_time,
        ),
        array(
          '%d',
          '%d',
          '%s'
        )
      );
    }

    $response = null;

    if ($result) {
      $response = array(
        'status' => 'success'
      );
    } else {
      $response = array(
        'status' => 'fail'
      );
    }

    var_dump(json_encode($response));
  }

  /**
	 * bcm_event_end_tracking
	 *
	 * Hook function when end time tracking ajax
	 *
	 * @date	22/11/19
	 * @since	5.0.0
	 *
	 * @param	void
	 * @return	obj
	 */
  function bcm_event_end_tracking() {
    global $wpdb;

    // Get Request Data
    $start_time = $_POST['start_time'];
    $evt_id = $_POST['evt_id'];

    $current_user = wp_get_current_user();

    $exist = $wpdb->get_var('SELECT id from wp_evt_employee WHERE evt_id = $evt_id AND user_id = $current_user->ID');

    $result = false;
    if ($exist) {
      $result = $wpdb->update(
          'wp_evt_employee',
          array(
            'start_time' => $start_time
          ),
          array(
            'evt_id' => $evt_id,
            'user_id' => $currentUser->ID
          ),
          array(
            '%d',
            '%d'
          ),
          array(
            '%d'
          )
        );
    } else {
      $result = $wpdb->insert(
        'wp_evt_employee',
        array(
          'evt_id' => $evt_id,
          'user_id' => $currentUser->ID,
          'start_time' => $start_time,
        ),
        array(
          '%d',
          '%d',
          '%s'
        )
      );
    }

    $response = array();

    if ($result) {
      $response = array(
        'status' => 'success'
      );
    } else {
      $response = array(
        'status' => 'fail'
      );
    }

    echo json_encode($response);
  }

  /**
	 * bcm_front_select_event
	 *
	 * Return Data after select event on Front End
	 *
	 * @date	22/11/19
	 * @since	5.0.0
	 *
	 * @param	void
	 * @return	obj
	 */
  function bcm_front_select_event() {
    $evt_id = $_POST['evt_id'];

    // Get All Event Data
    $evt_data = get_post($evt_id);
    $evt_venue = get_post_meta($evt_id, 'bcm_venue', false);
    $evt_status = get_post_meta($evt_id, 'bcm_event_status', false);
    $evt_supervisor = get_post_meta($evt_id, 'bcm_event_supervisor', false);
    $evt_start_time = get_post_meta($evt_id, 'bcm_event_start_time', false);
    $evt_end_time = get_post_meta($evt_id, 'bcm_end_time', false);
    $evt_employees = get_post_meta($evt_id, 'bcm_event_employees', false);

    $evt = array(
      'title' => $evt_data->post_title,
      'venue' => $evt_venue,
      'status' => $evt_status,
      'supervisor' => $evt_supervisor,
      'start_time' => $evt_start_time,
      'end_time' => $evt_end_time,
      'employees' => $evt_employees
    );

    // Check Current User is Supervisor
    $currentUser = wp_get_current_user();
    $isSuperVisor = $evt_supervisor[0][0] == $currentUser->ID ? true : false;

    if ($isSuperVisor) {

    } else {
      $response = array(
        'response_html' => $this->bcm_get_employee_html($evt),
        'modal_html' => $this->bcm_get_employee_modal_html()
      );
      echo json_encode($response);
    }

    wp_die();
  }

  /**
	 * bcm_get_employee_html
	 *
	 * Redirect to the login page whenever first time to load
	 *
	 * @date	22/11/19
	 * @since	5.0.0
	 *
	 * @param	void
	 * @return	void
	 */
  function bcm_get_employee_html($evt) {
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
            <span id="disHour">00</span>hrs<span id="disMin">00</span>mins
          </div>
        </div>
      </div>
      <div class="row track-buttons">
        <button type="button" class="btn btn-success btn-md start-btn">
          START
        </button>
        <button type="button" class="btn btn-danger btn-md end-btn">
          END
        </button>
      </div>
    <?php
    return ob_get_clean();
  }

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

  /**
	 * bcm_get_supervisor_html
	 *
	 * Redirect to the login page whenever first time to load
	 *
	 * @date	22/11/19
	 * @since	5.0.0
	 *
	 * @param	void
	 * @return	void
	 */
  function bcm_get_supervisor_html($evt) {

  }
  /**
	 * bcm_redirect_to_login_page
	 *
	 * Redirect to the login page whenever first time to load
	 *
	 * @date	22/11/19
	 * @since	5.0.0
	 *
	 * @param	void
	 * @return	void
	 */
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

  /**
	 * bcm_redirect_after_login
	 *
	 * Redirect the site after login according to the User Role
	 *
	 * @date	22/11/19
	 * @since	5.0.0
	 *
	 * @param	void
	 * @return	void
	 */
  function bcm_redirect_after_login($login) {
    if (is_user_logged_in()) {
      $user = get_userdatabylogin($login);
      if ( !in_array( 'administrator', (array) $user->roles ) ) {
        wp_redirect( home_url() );
        exit;
      }
    }
  }

	/**
	 * bcm_short_code
	 *
	 * Add Admin Menu
	 *
	 * @date	22/11/19
	 * @since	5.0.0
	 *
	 * @param	void
	 * @return	void
	 */
  function bcm_short_code() {
    $type = 'event';
    $args = array(
      'post_type' => $type,
      'post_status' => 'publish',
      'posts_per_page' => -1,
      'ignore_sticky_posts'=> true
    );
    $query = null;
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
          <?php
          endif;
          wp_reset_query();
          ?>
          </select>
        </div>
      </div>
      <div class="bcm-page-content"></div>
    </div>
    <?php
  }

  /**
	 * bcm_get_events
	 *
	 * Get All Events
	 *
	 * @date	22/11/19
	 * @since	5.0.0
	 *
	 * @param	void
	 * @return	void
	 */
  function bcm_get_events () {
    $type = 'event';
    $args = array(
      'post_type' => $type,
      'post_status' => 'publish',
      'posts_per_page' => -1,
      'ignore_sticky_posts'=> true
    );
    $query = null;
    $query = new WP_Query($args);
  }

	/**
	 * bcm_front_scripts_add
	 *
	 * Enque Front End CSS and JS files
	 *
	 * @date	22/11/19
	 * @since	5.0.0
	 *
	 * @param	void
	 * @return	void
	 */
  function bcm_front_scripts_add() {
    // Bootstrap Include
    wp_register_style('bootstrap-css', 'https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css');
    wp_enqueue_style('bootstrap-css');
    // Custom CSS Include
    wp_register_style('bcm-short-code-css', plugins_url('bcm/css/bcm-short-code.css'));
    wp_enqueue_style('bcm-short-code-css');

    wp_register_script('popper-js','https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js', array('jquery'), true);
    wp_enqueue_script('popper-js');

    wp_register_script('bootstrap-js','https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js', array('popper-js'), true);
    wp_enqueue_script('bootstrap-js');

    // Custom JS Include
    wp_register_script('bcm-short-code-js', plugins_url('bcm/js/bcm-short-code.js'), array('jquery'), true);
    wp_enqueue_script('bcm-short-code-js');

    // Localize the Script
    wp_localize_script('bcm-short-code-js', 'bcm_obj', array(
      'ajaxurl' => admin_url( 'admin-ajax.php' )
    ));
  }

	/**
	 * report_admin_menu
	 *
	 * Add Admin Menu
	 *
	 * @date	22/11/19
	 * @since	5.0.0
	 *
	 * @param	void
	 * @return	void
	 */
  function report_admin_menu () {
    add_menu_page(
      __('Event Report', 'textdomain'),
      __('Event Report', 'textdomain'),
      'manage_options',
      'bcm-event-report-page',
      array($this, 'bcm_report_page_contents'),
      'dashicons-media-document',
      50
    );
  }

	/**
	 * report_admin_menu
	 *
	 * Add Admin Menu
	 *
	 * @date	22/11/19
	 * @since	5.0.0
	 *
	 * @param	void
	 * @return	void
	 */
  function bcm_report_page_contents() {?>
    <div class="bcm-report-page-wrapper">
      <div class="row filter-toolbar">
        <div class="filter-inputs">
          <div class="input-group">
            <label for="employeeField">
              Employee
            </label>
            <input type="text" id="employeeField" name="employee" placeholder="ex: Anthony" />
          </div>
          <div class="input-group">
            <label for="startTimeField">
              From
            </label>
            <input type="text" id="fromField" name="from" placeholder="2019-01-01" />
          </div>
          <div class="input-group">
            <label for="toField">
              To
            </label>
            <input type="text" id="toField" name="to" placeholder="2019-12-21" />
          </div>
          <div class="input-group">
            <label for="eventField">
              Event
            </label>
            <input type="text" id="eventField" name="event" placeholder="ex: Ceremony" />
          </div>
        </div>
        <div class="filter-button">
          <button type="button" id="bcmReportFilterButton" class="button button-primary filter-btn">Filter</button>
        </div>
      </div>

      <div class="row search-result">
        <form method="post">
          <input type="hidden" name="page" value="bcm-event-report-page" />
          <?php
          $this->bcmReportTable->prepare_items();
          $this->bcmReportTable->search_box('search', 'search_id');
          $this->bcmReportTable->display();
          ?>
        </form>
      </div>
      <div class="row report-result">
        <button type="button" class="button button-primary export-btn">Export CSV</button>
      </div>
    </div>
<?php
  }

  /**
	 * register and enque script and style
	 *
	 * Add Admin Menu
	 *
	 * @date	22/11/19
	 * @since	5.0.0
	 *
	 * @param	void
	 * @return	void
	 */
  function bcm_add_custom_scripts() {
    wp_register_style('bcm-plugin-css', plugins_url('bcm/css/bcm-plugin.css'));
    wp_enqueue_style('bcm-plugin-css');

    wp_register_script('bcm-plugin', plugins_url('bcm/js/bcm-plugin.js'), array('jquery'), true);
    wp_enqueue_script('bcm-plugin');
  }

  /**
	 * define
	 *
	 * Defines a constant if doesnt already exist.
	 *
	 * @since	5.5.13
	 *
	 * @param	string $name The constant name.
	 * @param	mixed $value The constant value.
	 * @return	void
	 */
	function define( $name, $value = true ) {
		if( !defined($name) ) {
			define( $name, $value );
		}
	}
}

/**
 * bcm
 *
 * The main function responsible for returning the one true acf Instance to functions everywhere.
 * Use this function like you would a global variable, except without needing to declare the global.
 *
 * @date	4/09/13
 * @since	4.3.0
 *
 * @param	void
 * @return	BCM
 **/
function bcm() {
	global $bcm;

	// Instantiate only once.
	if( !isset($bcm) ) {
		$bcm = new BCM();
		$bcm->initialize();
	}
	return $bcm;
}



// Instantiate.
bcm();

endif;