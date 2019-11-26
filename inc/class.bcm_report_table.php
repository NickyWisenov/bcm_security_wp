<?php

/**
 * PART 1. Defining Custom Table List
 *
 */
if( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class BCM_REPORT_TABLE extends WP_List_Table
{
    function __construct()
    {
        parent::__construct();
    }

    function column_default($item, $column_name)
    {
        return $item[$column_name];
    }

    function column_evt_id($item)
    {
        // links going to /admin.php?page=[your_plugin_page][&other_params]
        // notice how we used $_REQUEST['page'], so action will be done on curren page
        // also notice how we use $this->_args['singular'] so in this example it will
        // be something like &bcm_report=2
        $actions = array(
            'edit' => sprintf('<a href="?page=bcm_reports_form&id=%s">%s</a>', $item['id'], __('Edit', 'bcm')),
            'delete' => sprintf('<a href="?page=%s&action=delete&id=%s">%s</a>', $_REQUEST['page'], $item['id'], __('Delete', 'bcm')),
        );
        return sprintf('%s %s',
            get_the_title($item['evt_id']),
            $this->row_actions($actions)
        );
    }

    function column_user_id($item)
    {
    	$user = get_userdata($item['user_id']);
        return $user->first_name . ' ' . $user->last_name . '<br>' . $user->user_email;
    }

    function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="id[]" value="%s" />',
            $item['id']
        );
    }
    /**
     * [REQUIRED] This method return columns to display in table
     * you can skip columns that you do not want to show
     * like content, or description
     *
     * @return array
     */
    function get_columns()
    {
        $columns = array(
            'cb' => '<input type="checkbox" />', //Render a checkbox instead of text
            'evt_id' => __('Event', 'bcm'),
            'user_id' => __('Employee', 'bcm'),
            'start_time' => __('Start Time', 'bcm'),
            'end_time' => __('End Time', 'bcm'),
        );
        return $columns;
    }

    function get_sortable_columns()
    {
        $sortable_columns = array(
            'evt_id' => array('evt_id', true),
            'user_id' => array('user_id', false),
            'start_time' => array('start_time', false),
            'end_time' => array('end_time', false),
        );
        return $sortable_columns;
    }

    function get_bulk_actions()
    {
        $actions = array(
            'delete' => 'Delete'
        );
        return $actions;
    }

    function process_bulk_action()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'evt_employee'; // do not forget about tables prefix
        if ('delete' === $this->current_action()) {
            $ids = isset($_REQUEST['id']) ? $_REQUEST['id'] : array();
            if (is_array($ids)) $ids = implode(',', $ids);
            if (!empty($ids)) {
                $wpdb->query("DELETE FROM $table_name WHERE id IN($ids)");
            }
        }
    }

    function prepare_items()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'evt_employee'; // do not forget about tables prefix
        $per_page = 5; // constant, how much records will be shown per page
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        // here we configure table headers, defined in our methods
        $this->_column_headers = array($columns, $hidden, $sortable);
        // [OPTIONAL] process bulk action if any
        $this->process_bulk_action();

        $where = '1 = 1';
        if ( ! empty( $_REQUEST['event_id'] ) ) {
        	$where .= ' AND event_id= ' . intval($_REQUEST['event_id']);
        }

        if ( ! empty( $_REQUEST['user_id'] ) ) {
        	$where .= ' AND user_id= ' . intval($_REQUEST['user_id']);
        }

        // will be used in pagination settings
        $total_items = $wpdb->get_var("SELECT COUNT(id) FROM $table_name WHERE $where");
        // prepare query params, as usual current page, order by and order direction
        $paged = isset($_REQUEST['paged']) ? max(0, intval($_REQUEST['paged'] - 1) * $per_page) : 0;
        $orderby = (isset($_REQUEST['orderby']) && in_array($_REQUEST['orderby'], array_keys($this->get_sortable_columns()))) ? $_REQUEST['orderby'] : 'id';
        $order = (isset($_REQUEST['order']) && in_array($_REQUEST['order'], array('asc', 'desc'))) ? $_REQUEST['order'] : 'asc';
        // [REQUIRED] define $items array
        // notice that last argument is ARRAY_A, so we will retrieve array
        $this->items = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE $where ORDER BY $orderby $order LIMIT %d OFFSET %d", $per_page, $paged), ARRAY_A);
        // [REQUIRED] configure pagination
        $this->set_pagination_args(array(
            'total_items' => $total_items, // total items defined above
            'per_page' => $per_page, // per page constant defined at top of method
            'total_pages' => ceil($total_items / $per_page) // calculate pages count
        ));
    }
}

/**
 * PART 2. Defining Custom Database Table
 * ============================================================================
 *
 */
global $bcm_db_version;
$bcm_db_version = '1.0';

/**
 * database setun
 */
function bcm_install()
{
    global $wpdb;
    global $bcm_db_version;
    $table_name = $wpdb->prefix . 'evt_employee'; // do not forget about tables prefix

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
		  id int(10) unsigned NOT NULL AUTO_INCREMENT,
		  evt_id int(10) unsigned NOT NULL,
		  user_id int(10) unsigned NOT NULL,
		  start_time datetime DEFAULT NULL,
		  start_ip_address varchar(30) DEFAULT NULL,
		  end_time datetime DEFAULT NULL,
		  end_ip_address varchar(30) DEFAULT NULL,
          activity text DEFAULT NULL,
          track_status varchar(30) DEFAULT NULL,
		  PRIMARY KEY  (id)
		);";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
    // save current database version for later use (on upgrade)
    add_option('bcm_db_version', $bcm_db_version);
}
register_activation_hook(__FILE__, 'bcm_install');

/**
 * PART 3. Admin page
 * ============================================================================
 */
/**
 * admin_menu hook implementation, will add pages to list bcm_reports and to add new one
 */
function bcm_admin_menu()
{
    add_menu_page(__('BCM Event Reports', 'bcm'), __('BCM Event Reports', 'bcm'), 'activate_plugins', 'bcm_reports', 'bcm_bcm_reports_page_handler', '', 35);
    add_submenu_page('bcm_reports', __('BCM Event Reports', 'bcm'), __('BCM Event Reports', 'bcm'), 'activate_plugins', 'bcm_reports', 'bcm_bcm_reports_page_handler');
    // add new will be described in next part
    add_submenu_page('bcm_reports', __('Add new', 'bcm'), __('Add new', 'bcm'), 'activate_plugins', 'bcm_reports_form', 'bcm_bcm_reports_form_page_handler');
}
add_action('admin_menu', 'bcm_admin_menu');

function bcm_bcm_reports_page_handler()
{
    global $wpdb;
    $table = new BCM_REPORT_TABLE();
    $table->prepare_items();
    $message = '';
    if ('delete' === $table->current_action()) {
        $message = '<div class="updated below-h2" id="message"><p>' . sprintf(__('Items deleted: %d', 'bcm'), count($_REQUEST['id'])) . '</p></div>';
    }
    ?>
<div class="bcm-report-page-wrapper wrap">

    <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
    <h2><?php _e('BCM Event Reports', 'bcm')?>
    	<a class="add-new-h2" href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=bcm_reports_form');?>"><?php _e('Add new', 'bcm')?></a>
    </h2>
    <?php echo $message; ?>
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
        <form id="bcm_reports-table" method="GET">
            <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>
            <?php $table->display() ?>
        </form>
    </div>
    <div class="row report-result">
        <button type="button" class="button button-primary export-btn">Export CSV</button>
    </div>
</div>
<?php
}

/**
 * PART 4. Form for adding andor editing row
 * ============================================================================
 */

function bcm_bcm_reports_form_page_handler()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'evt_employee'; // do not forget about tables prefix
    $message = '';
    $notice = '';
    // this is default $item which will be used for new records
    $default = array(
        'id' => 0,
        'evt_id' => '',
        'user_id' => '',
        'start_time' => null,
        'end_time' => null,
    );
    // here we are verifying does this request is post back and have correct nonce
    if ( isset($_REQUEST['nonce']) && wp_verify_nonce($_REQUEST['nonce'], basename(__FILE__))) {
        // combine our default item with request params
        $item = shortcode_atts($default, $_REQUEST);
        // validate data, and if all ok save item to database
        // if id is zero insert otherwise update
        $item_valid = bcm_validate_bcm_report($item);
        if ($item_valid === true) {
            if ($item['id'] == 0) {
                $result = $wpdb->insert($table_name, $item);
                $item['id'] = $wpdb->insert_id;
                if ($result) {
                    $message = __('Item was successfully saved', 'bcm');
                } else {
                    $notice = __('There was an error while saving item', 'bcm');
                }
            } else {
                $result = $wpdb->update($table_name, $item, array('id' => $item['id']));
                if ($result) {
                    $message = __('Item was successfully updated', 'bcm');
                } else {
                    $notice = __('There was an error while updating item', 'bcm');
                }
            }
        } else {
            // if $item_valid not true it contains error message(s)
            $notice = $item_valid;
        }
    }
    else {
        // if this is not post back we load item to edit or give new one to create
        $item = $default;
        if (isset($_REQUEST['id'])) {
            $item = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $_REQUEST['id']), ARRAY_A);
            if (!$item) {
                $item = $default;
                $notice = __('Item not found', 'bcm');
            }
        }
    }
    // here we adding our custom meta box
    add_meta_box('bcm_reports_form_meta_box', 'BCM Event Report data', 'bcm_bcm_reports_form_meta_box_handler', 'bcm_report', 'normal', 'default');
    ?>
<div class="wrap">
    <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
    <h2><?php _e('BCM Event Report', 'bcm')?>
    	<a class="add-new-h2" href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=bcm_reports');?>"><?php _e('back to list', 'bcm')?></a>
    </h2>

    <?php if (!empty($notice)): ?>
    <div id="notice" class="error"><p><?php echo $notice ?></p></div>
    <?php endif;?>
    <?php if (!empty($message)): ?>
    <div id="message" class="updated"><p><?php echo $message ?></p></div>
    <?php endif;?>

    <form id="form" method="POST">
        <input type="hidden" name="nonce" value="<?php echo wp_create_nonce(basename(__FILE__))?>"/>
        <?php /* NOTICE: here we storing id to determine will be item added or updated */ ?>
        <input type="hidden" name="id" value="<?php echo $item['id'] ?>"/>

        <div class="metabox-holder" id="poststuff">
            <div id="post-body">
                <div id="post-body-content">
                    <?php /* And here we call our custom meta box */ ?>
                    <?php do_meta_boxes('bcm_report', 'normal', $item); ?>
                    <input type="submit" value="<?php _e('Save', 'bcm')?>" id="submit" class="button-primary" name="submit">
                </div>
            </div>
        </div>
    </form>
</div>
<?php
}

/**
 * This function renders our custom meta box
 * $item is row
 *
 * @param $item
 */
function bcm_bcm_reports_form_meta_box_handler($item)
{
    ?>

<table cellspacing="2" cellpadding="5" style="width: 100%;" class="form-table">
    <tbody>
    <tr class="form-field">
        <th valign="top" scope="row">
            <label for="event"><?php _e('Event', 'bcm')?></label>
        </th>
        <td>
            <input id="event" name="evt_id" type="text" style="width: 95%" value="<?php echo esc_attr($item['evt_id'])?>"
                   size="50" class="code" required>
        </td>
    </tr>
    <tr class="form-field">
        <th valign="top" scope="row">
            <label for="user"><?php _e('User', 'bcm')?></label>
        </th>
        <td>
            <input id="user" name="user" type="text" style="width: 95%" value="<?php echo esc_attr($item['user_id'])?>"
                   size="50" class="code" required>
        </td>
    </tr>
    <tr class="form-field">
        <th valign="top" scope="row">
            <label for="start_time"><?php _e('Start Time', 'bcm')?></label>
        </th>
        <td>
            <input id="start_time" name="start_time" type="text" style="width: 95%" value="<?php echo esc_attr($item['start_time'])?>"
                   size="50" class="code">
        </td>
    </tr>
    <tr class="form-field">
        <th valign="top" scope="row">
            <label for="end_time"><?php _e('End Time', 'bcm')?></label>
        </th>
        <td>
            <input id="end_time" name="end_time" type="text" style="width: 95%" value="<?php echo esc_attr($item['end_time'])?>"
                   size="50" class="code">
        </td>
    </tr>
    </tbody>
</table>
<?php
}

function bcm_validate_bcm_report($item)
{
    $messages = array();
    if (empty($item['evt_id'])) $messages[] = __('Event is required', 'bcm');
    if (empty($item['user_id'])) $messages[] = __('User is required', 'bcm');
    if (empty($messages)) return true;
    return implode('<br />', $messages);
}

function bcm_languages()
{
    load_plugin_textdomain('bcm', false, dirname(plugin_basename(__FILE__)));
}
add_action('init', 'bcm_languages');