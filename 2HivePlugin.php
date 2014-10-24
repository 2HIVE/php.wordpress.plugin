<?php
/**
 * @package 2HivePlugin
 * @version 1.0
 */
/*
Plugin Name: 2HivePlugin
Plugin URI: 2HivePlugin
Description: 2HivePlugin
Author: Dmitriev Sergei
Version: 1.0
Author URI: 2HivePlugin
*/

function hive_send($post_ID, $content, $type) {

  $data = array(array('id' => $post_ID,
                'type' => $type,
                'content' => array(array('text' => $content)),
                'lang' => ''));

  $data = json_encode($data);

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_TIMEOUT, 3);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_HEADER, 0);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
  curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
  $url = "http://2hive.org/api/?apikey=3f511380897e43d7b8ac6a14c59723b3";
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, "data=".$data);
  curl_setopt($ch, CURLOPT_URL, $url);
  $result = curl_exec($ch);
  curl_close($ch);

  $json = json_decode($result, true);

  if($json['status']['code'] == 200) {

    foreach($json['response'] as $response) {

      if($response['status'] == 'disallow') {

        if($response['type'] == 'new_post') {

          wp_delete_post($response['id']);

        } elseif($response['type'] == 'new_comment') {

          wp_delete_comment($response['id']);

        }

      }

    }

  }

}

function hive_admin() {

  add_options_page('2HivePlugin', '2HivePlugin', 8, 'hive', 'hive_options_page');

}

function hive_save_options() {

  if(isset($_POST['hive_send_posts'])) {

    $hive_send_posts = 1;

  } else {

    $hive_send_posts = 0;

  }

  if(isset($_POST['hive_send_comments'])) {

    $hive_send_comments = 1;

  } else {

    $hive_send_comments = 0;

  }

  update_option('hive_send_posts', $hive_send_posts);
  update_option('hive_send_comments', $hive_send_comments);

}

function hive_options_page() {

  add_option('hive_send_posts', '1');
  add_option('hive_send_comments', '1');

  if(get_option('hive_send_posts') == 1) {

    $checked_1 = ' checked="checked"';

  }

  if(get_option('hive_send_comments') == 1) {

    $checked_2 = ' checked="checked"';

  }

  echo '<h3>Настройки плагина 2HivePlugin</h3>';

  echo '<form method="post" name="hive_save_options" action="'.$_SERVER["PHP_SELF"].'?page=hive&amp;updated=true">
          <table>
            <tr>
              <td><input name="hive_send_posts" type="checkbox"'.$checked_1.'></td>
              <td>Send posts</td>
            </tr>
            <tr>
              <td><input name="hive_send_comments" type="checkbox"'.$checked_2.'></td>
              <td>Send comments</td>
            </tr>
            <tr>
              <td colspan="2"><input type="submit" name="hive_save" value="Сохранить"></td>
            </tr>
          </table>
        </form>';

}

if(isset($_POST['hive_save'])) {

  hive_save_options();

}

function hive_send_data_post($post_ID) {

  $post = get_post($post_ID);

  if(get_option('hive_send_posts') == 1) {

    hive_send($post_ID, $post->post_content, 'new_post');

  }

}

function hive_send_data_comment($comment_ID) {

  $comment = get_comment($comment_ID);

  if(get_option('hive_send_comments') == 1) {

    hive_send($comment_ID, $comment->comment_content, 'new_comment');

  }

}

add_filter('cron_schedules', 'cron_add_minute');

function cron_add_minute($schedules) {

	$schedules['minute'] = array(
		'interval' => 60,
		'display' => __('Once minute')
	);
	return $schedules;

}

if(!wp_next_scheduled('hive_task_hook')) {

  wp_schedule_event(time(), 'minute', 'hive_task_hook');

}

function hive_task_function() {

  //$data = array();

  //$data = json_encode($data);

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_TIMEOUT, 3);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_HEADER, 0);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
  curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
  $url = "http://2hive.org/api/?apikey=3f511380897e43d7b8ac6a14c59723b3";
  //curl_setopt($ch, CURLOPT_POST, 1);
  //curl_setopt($ch, CURLOPT_POSTFIELDS, "data=".$data);
  curl_setopt($ch, CURLOPT_URL, $url);
  $result = curl_exec($ch);
  curl_close($ch);

  $json = json_decode($result, true);

  if($json['status']['code'] == 200) {

    foreach($json['response'] as $response) {

      if($response['status'] == 'disallow') {

        if($response['type'] == 'new_post') {

          wp_delete_post($response['id']);

        } elseif($response['type'] == 'new_comment') {

          wp_delete_comment($response['id']);

        }

      }

    }

  }

}

add_action('admin_menu', 'hive_admin');
add_action('publish_post', 'hive_send_data_post');
add_action('comment_post', 'hive_send_data_comment');
add_action('hive_task_hook', 'hive_task_function');

?>
