<?php

/**
 * Plugin Name: Random HTML
 * Plugin URI: https://github.com/loueteliot/random-html
 * Description: Display a random HTML from list.
 * Version: 1.0
 * Author: Louet Eliot
 * Author URI: https://github.com/loueteliot
 */

register_activation_hook(__FILE__, 'random_html_plugin_create_db');
function random_html_plugin_create_db()
{
  global $wpdb;
  $charset_collate = $wpdb->get_charset_collate();
  $table_name = $wpdb->prefix . 'random_html';

  $sql = "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		html TEXT,
		UNIQUE KEY id (id)
	) $charset_collate;";

  require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
  dbDelta($sql);
}

function random_html_shortcode($atts, $content = null)
{
  global $wpdb;
  $result = $wpdb->get_var("SELECT html FROM {$wpdb->prefix}random_html ORDER BY RAND() LIMIT 1");

  // databse error, return false
  if (!$result) {
    return '';
  }

  return '<p>' . do_shortcode(stripslashes($result)) . '</p>';
};

add_shortcode('random_html', 'random_html_shortcode');

function random_html_register_options_page()
{
  add_options_page('Ramdon HTML Settings', 'Ramdon HTML Settings', 'manage_options', 'randomhtml', 'random_html_options_page');
}

add_action('admin_menu', 'random_html_register_options_page');

function random_html_options_page()
{
  global $wpdb;

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_GET['delete'])) { // delete
      if($_REQUEST['id']) {
        $row = $wpdb->delete("{$wpdb->prefix}random_html", array('id' => $_REQUEST['id']));
      }
    } else { // add or edit
      $default = array(
        'id' => '',
        'html' => '',
      );

      $item = shortcode_atts($default, $_REQUEST);

      if ($item['id'] != '') { // edit
        $row = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}random_html");

        if ($row) {
          $where = ['id' => $default['id']];

          $result = $wpdb->update("{$wpdb->prefix}random_html", $item, $where);

          $wpdb->update(
            "{$wpdb->prefix}random_html",
            array(
              'html' => $item['html']
            ),
            array(
              'id' => $item['id']
            )
          );
        }
      } else 
     if ($item['html'] != '') {
        $wpdb->insert("{$wpdb->prefix}random_html", $item);
      }
    }
  }
?>

  <form method="POST" action="?page=randomhtml">
    <label>HTML: </label><textarea name="html"></textarea><br />
    <input type="submit" value="Add" />
  </form>
  <?php

  $result = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}random_html");
  ?>

  <table>
    <thead>
      <tr>
        <th>id</th>
        <th>HTML</th>
      </tr>
    </thead>
    <?php
    foreach ($result as $row) {
    ?>
      <tr>
        <th class="manage-column column-columnname"><?php echo $row->id ?></th>
        <th>
          <form method="POST" action="?page=randomhtml">
            <input type="text" name="id" style="display: none;" value="<?php echo $row->id ?>" />
            <textarea name="html"><?php echo $row->html ?></textarea>
            <input type="submit" value="Update" />
          </form>
        </th>
        <th>
          <form method="POST" action="?page=randomhtml&delete=true">
            <input type="text" name="id" style="display: none;" value="<?php echo $row->id ?>" />
            <input type="submit" value="DELETE" />
          </form>
        </th>
      </tr>
    <?php
    }
    ?>
  </table>
<?php }
