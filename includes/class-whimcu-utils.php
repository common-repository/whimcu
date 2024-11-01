<?php

class Whimcu_Utils
{
  public static function update_option($option_key, $option_value)
  {
    update_option($option_key, $option_value, false);
    wp_cache_delete($option_key, 'options');
  }

  public static function delete_option($option_key)
  {
    delete_option($option_key);
    wp_cache_delete($option_key);
  }

  public static function http_api_call($method, $endpoint, $data = []) {
    // Add ApiKey to every request if exist
    $api_key = get_option('whimcu_api_key');
    if ($api_key) {
      $data['apiKey'] = $api_key;
    }
    if ($method === 'GET') {
      $result = wp_remote_get(WHIMCU_MAIN_API_URL . $endpoint . '?' . http_build_query($data));
      if (is_wp_error($result) || $result['response']['code'] != 200){	// != 200
        throw new Exception;
      } else {
        return json_decode($result['body']);
      }
    } else if ($method === 'POST') {
      $args = array(
        'body' => $data
      );
      $result = wp_remote_post(WHIMCU_MAIN_API_URL . $endpoint, $args);
      if (is_wp_error($result) || $result['response']['code'] != 200){	// != 200
        throw new Exception;
      } else {
        return json_decode($result['body']);
      }
    }
    throw new Exception;
  }
}
