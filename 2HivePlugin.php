<?php

/**
 * @package 2Hive
 * @version 1.0
 */

/**
 * Plugin Name: 2Hive
 * Plugin URI: http://2hive.org
 * Description: 2Hive
 * Author: 2Hive Team
 * Description: To get started: 1) Click the "Activate" link to the left of this description, 2) <a href="http://2hive.org/project/account" target="_blank">Get 2Hive API Key in your Account Page</a>, and 3) Go to your 2Hive configuration page, and save your API key.
 * Version: 1.0
 */

const Plugin2HiveURL = 'http://2hive.org/api';

function hive_send($contentId, $content, $type)
{
    $apiKey = get_option('hive_api_key');

    if (!trim($apiKey)) {
        /**
         * Add Error Handling here...
         */
        return false;
    }

    $url = Plugin2HiveURL . "/?apikey={$apiKey}";

    $data = array(
        array(
            'id'      => $contentId,
            'type'    => $type,
            'content' => array(array('text' => $content)),
            'lang'    => ''
        )
    );

    $data = json_encode($data);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_TIMEOUT, 3);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);

    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "data=" . $data);

    curl_setopt($ch, CURLOPT_URL, $url);

    $result = curl_exec($ch);

    curl_close($ch);

    $json = json_decode($result, true);

    if ($json['status']['code'] == 200) {

        foreach ($json['response'] as $response) {

            if ($response['status'] == 'disallow') {

                if ($response['type'] == 'new_post') {

                    wp_delete_post($response['id']);

                } elseif ($response['type'] == 'new_comment') {

                    wp_delete_comment($response['id']);

                }

            }

        }

    } else {
        /**
         * Add Error Handling here...
         */
    }
}

function hive_admin()
{
    add_options_page('2Hive', '2Hive', 8, 'hive', 'hive_options_page');
}

function hive_save_options()
{
    if (isset($_POST['hive_send_posts'])) {
        $hive_send_posts = 1;
    } else {
        $hive_send_posts = 0;
    }

    if (isset($_POST['hive_send_comments'])) {
        $hive_send_comments = 1;
    } else {
        $hive_send_comments = 0;
    }

    if (isset($_POST['hive_api_key'])) {
        $hive_api_key = $_POST['hive_api_key'];
    } else {
        $hive_api_key = '';
    }

    update_option('hive_send_posts', $hive_send_posts);
    update_option('hive_send_comments', $hive_send_comments);
    update_option('hive_api_key', $hive_api_key);
}

function hive_options_page()
{
    add_option('hive_send_posts', 1);
    add_option('hive_send_comments', 1);
    add_option('hive_api_key', '');

    if (get_option('hive_send_posts') == 1) {
        $checked_1 = ' checked="checked"';
    }
    if (get_option('hive_send_comments') == 1) {
        $checked_2 = ' checked="checked"';
    }

    $apiKey = get_option('hive_api_key');

    echo <<<PluginSettingsHTML

    <br/>
    <img src="http://2hive.org/images/2hive_logo_slogan_1024.png" style="height:85px" />
    <br/><br/><br/>
    <form method="post" name="hive_save_options" action="{$_SERVER["PHP_SELF"]}?page=hive&amp;updated=true">
        <table class="form-table">
        <tbody>
        <tr>
            <th scope="row"><label for="hive_api_key_input_id">API Key</label><br/></th>
            <td>
                <input style="width:19em" name="hive_api_key" type="text" value="{$apiKey}" id="hive_api_key_input_id" />
            </td>
        </tr>
        <tr>
            <th scope="row">General Settings</th>
            <td>
                <input name="hive_send_posts" type="checkbox"{$checked_1} id="hive_send_posts_input_id" />
                <label for="hive_send_posts_input_id">Send posts</label>
                <br/>
                <input name="hive_send_comments" type="checkbox"{$checked_2} id="hive_send_comments_input_id" />
                <label for="hive_send_comments_input_id">Send comments</label>
            </td>
        </tr>
        </tbody>
        </table>
        <br/>
        <p class="submit">
            <input type="submit" name="hive_save" class="button button-primary" value="Save Changes" />
        </p>
    </form>
PluginSettingsHTML;
}

/**
 * Settings
 */

if (isset($_POST['hive_save'])) {

    hive_save_options();

}

function hive_send_data_post($post_ID)
{
    $post = get_post($post_ID);

    if (get_option('hive_send_posts') == 1) {
        $contentId = "post_id_{$post_ID}";
        hive_send($contentId, $post->post_content, 'new_post');
    }
}
function hive_send_data_comment($comment_ID)
{
    $comment = get_comment($comment_ID);

    if (get_option('hive_send_comments') == 1) {
        $contentId = "comment_id_{$comment_ID}";
        hive_send($contentId, $comment->comment_content, 'new_comment');
    }
}

/**
 * CRON JOB
 */

add_filter('cron_schedules', 'cron_add_minute');

function cron_add_minute($schedules)
{
    $schedules['minute'] = array(
        'interval' => 60,
        'display' => __('Once minute')
    );
    return $schedules;
}

if (!wp_next_scheduled('hive_task_hook')) {

    wp_schedule_event(time(), 'minute', 'hive_task_hook');

}

function hive_task_function()
{
    $apiKey = get_option('hive_api_key');

    if (!trim($apiKey)) {
        /**
         * Add Error Handling here...
         */
        return false;
    }

    $url = Plugin2HiveURL . "/?apikey={$apiKey}";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_TIMEOUT, 3);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);

    curl_setopt($ch, CURLOPT_URL, $url);

    $result = curl_exec($ch);
    curl_close($ch);

    $json = json_decode($result, true);

    if ($json['status']['code'] == 200) {

        foreach ($json['response'] as $response) {

            if ($response['status'] == 'disallow') {

                if ($response['type'] == 'new_post') {

                    wp_delete_post($response['id']);

                } elseif ($response['type'] == 'new_comment') {

                    wp_delete_comment($response['id']);

                }

            }

        }

    } else {
        /**
         * Add Error Handling here...
         */
    }
}

add_action('admin_menu', 'hive_admin');
add_action('publish_post', 'hive_send_data_post');
add_action('comment_post', 'hive_send_data_comment');
add_action('hive_task_hook', 'hive_task_function');

?>