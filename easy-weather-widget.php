<?php

/**
 * Plugin Name: Easy Weather Widget
 * Description: A WordPress widget that fetches and displays weather data from OpenWeatherMap API.
 * Version: 1.0
 * Author: Tom Kimberlin
 * Author URI: https://kimberlin.net
 */

class Easy_Weather_Widget extends WP_Widget
{

  function __construct()
  {
    parent::__construct(
      'easy_weather_widget',
      'Easy Weather Widget',
      array('description' => 'A WordPress widget that fetches and displays weather data from OpenWeatherMap API.')
    );
  }

  public function widget($args, $instance)
  {
    if (!empty($instance['selected_css'])) {
      $css_url = plugin_dir_url(__FILE__) . 'css/' . $instance['selected_css'];
      wp_enqueue_style($this->get_field_id('selected_css'), $css_url);
    }

    $units = isset($instance['units']) ? $instance['units'] : 'metric';
    $weather_data = $this->get_weather_data($instance['zip'], $instance['country'], $instance['api_key'], $units);

    $temp_unit = $units == 'metric' ? '°C' : '°F';
    $speed_unit = $units == 'metric' ? 'm/s' : 'mph';
    $rain_unit = $units == 'metric' ? 'mm' : 'inches';
    $snow_unit = $units == 'metric' ? 'mm' : 'inches';

    echo $args['before_widget'];
    if (!empty($instance['title'])) {
      echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
    }

    if (isset($weather_data['error'])) {
      echo '<p>Error: ' . $weather_data['error'] . '</p>';
    } else {
      echo '<div class="weather-info">';

      if (isset($instance['weather_icon']) && $instance['weather_icon'] == 'on') {
        $icon_id = $weather_data['weather_icon'];
        $icon_url = "http://openweathermap.org/img/w/{$icon_id}.png";
        echo "<img class='weather-icon' src='{$icon_url}' alt='Weather icon' />";
      }

      $temperature = $weather_data['temp'];
      $feels_like = $weather_data['feels_like'];
      if (isset($instance['round_weather_data']) && $instance['round_weather_data'] == 'on') {
        $temperature = round($temperature);
        $feels_like = round($feels_like);
      }

      if (isset($instance['name']) && $instance['name'] == 'on') {
        echo '<p><strong>City</strong>: ' . $weather_data['name'] . '</p>';
      }

      if (isset($instance['weather_main']) && $instance['weather_main'] == 'on') {
        echo '<p><strong>Summary</strong>: ' . $weather_data['weather_main'] . '</p>';
      }

      if (isset($instance['description']) && $instance['description'] == 'on') {
        echo '<p><strong>Description</strong>: ' . $weather_data['description'] . '</p>';
      }

      if (isset($instance['temperature']) && $instance['temperature'] == 'on') {
        echo '<p><strong>Temperature</strong>: ' . $temperature . $temp_unit . '</p>';
      }

      if (isset($instance['feels_like']) && $instance['feels_like'] == 'on') {
        echo '<p><strong>Feels Like</strong>: ' . $feels_like . $temp_unit . '</p>';
      }

      if (isset($instance['pressure']) && $instance['pressure'] == 'on' && isset($weather_data['pressure'])) {
        $pressure = $weather_data['pressure'];
        if (isset($instance['round_weather_data']) && $instance['round_weather_data'] == 'on') {
          $pressure = round($pressure);
        }
        $pressure = number_format($pressure);
        echo '<p><strong>Pressure</strong>: ' . $pressure . ' ' . 'hPa' . '</p>';
      }

      if (isset($instance['humidity']) && $instance['humidity'] == 'on' && isset($weather_data['humidity'])) {
        $humidity = $weather_data['humidity'];
        if (isset($instance['round_weather_data']) && $instance['round_weather_data'] == 'on') {
          $humidity = round($humidity);
        }
        echo '<p><strong>Humidity</strong>: ' . $humidity . '%' . '</p>';
      }

      if (isset($instance['temp_min']) && $instance['temp_min'] == 'on') {
        $temp_min = $weather_data['temp_min'];
        if (isset($instance['round_weather_data']) && $instance['round_weather_data'] == 'on') {
          $temp_min = round($temp_min);
        }
        echo '<p><strong>Min Temperature</strong>: ' . $temp_min . $temp_unit . '</p>';
      }

      if (isset($instance['temp_max']) && $instance['temp_max'] == 'on') {
        $temp_max = $weather_data['temp_max'];
        if (isset($instance['round_weather_data']) && $instance['round_weather_data'] == 'on') {
          $temp_max = round($temp_max);
        }
        echo '<p><strong>Max Temperature</strong>: ' . $temp_max . $temp_unit . '</p>';
      }

      if (isset($instance['visibility']) && $instance['visibility'] == 'on') {
        $visibility = $weather_data['visibility'];
        if (isset($instance['round_weather_data']) && $instance['round_weather_data'] == 'on') {
          $visibility = round($visibility);
        }
        $visibility = number_format($visibility);
        echo '<p><strong>Visibility</strong>: ' . $visibility . ' meters' . '</p>';
      }

      if (isset($instance['wind_speed']) && $instance['wind_speed'] == 'on' && isset($weather_data['wind_speed'])) {
        $wind_speed = $weather_data['wind_speed'];
        if (isset($instance['round_weather_data']) && $instance['round_weather_data'] == 'on') {
          $wind_speed = round($wind_speed);
        }
        echo '<p><strong>Wind Speed</strong>: ' . $wind_speed . ' ' . $speed_unit . '</p>';
      }

      if (isset($instance['wind_deg']) && $instance['wind_deg'] == 'on' && isset($weather_data['wind_deg'])) {
        $wind_deg = $weather_data['wind_deg'];
        if (isset($instance['round_weather_data']) && $instance['round_weather_data'] == 'on') {
          $wind_deg = round($wind_deg);
        }
        echo '<p><strong>Wind Direction</strong>: ' . $wind_deg . '°</p>';
      }

      if (isset($instance['wind_gust']) && $instance['wind_gust'] == 'on' && isset($weather_data['wind_gust'])) {
        $wind_gust = $weather_data['wind_gust'];
        if (isset($instance['round_weather_data']) && $instance['round_weather_data'] == 'on') {
          $wind_gust = round($wind_gust);
        }
        echo '<p><strong>Wind Gust</strong>: ' . $wind_gust . ' ' . $speed_unit . '</p>';
      }

      if (isset($instance['clouds_all']) && $instance['clouds_all'] == 'on') {
        $clouds_all = $weather_data['clouds_all'];
        if (isset($instance['round_weather_data']) && $instance['round_weather_data'] == 'on') {
          $clouds_all = round($clouds_all);
        }
        echo '<p><strong>Cloudiness</strong>: ' . $clouds_all . '%' . '</p>';
      }

      if (isset($instance['rain_1h']) && $instance['rain_1h'] == 'on' && isset($weather_data['rain_1h'])) {
        $rain_1h = $weather_data['rain_1h'];
        if (isset($instance['round_weather_data']) && $instance['round_weather_data'] == 'on') {
          $rain_1h = round($rain_1h);
        }
        echo '<p><strong>Rain Volume (1h)</strong>: ' . $rain_1h . ' ' . $rain_unit . '</p>'; // Assuming rain unit, adjust as needed
      }

      if (isset($instance['rain_3h']) && $instance['rain_3h'] == 'on' && isset($weather_data['rain_3h'])) {
        $rain_3h = $weather_data['rain_3h'];
        if (isset($instance['round_weather_data']) && $instance['round_weather_data'] == 'on') {
          $rain_3h = round($rain_3h);
        }
        echo '<p><strong>Rain Volume (3h)</strong>: ' . $rain_3h . ' ' . $rain_unit . '</p>';
      }

      if (isset($instance['snow_1h']) && $instance['snow_1h'] == 'on' && isset($weather_data['snow_1h'])) {
        $snow_1h = $weather_data['snow_1h'];
        if (isset($instance['round_weather_data']) && $instance['round_weather_data'] == 'on') {
          $snow_1h = round($snow_1h);
        }
        echo '<p><strong>Snow Volume (1h)</strong>: ' . $snow_1h . ' ' . $snow_unit . '</p>';
      }

      if (isset($instance['snow_3h']) && $instance['snow_3h'] == 'on' && isset($weather_data['snow_3h'])) {
        $snow_3h = $weather_data['snow_3h'];
        if (isset($instance['round_weather_data']) && $instance['round_weather_data'] == 'on') {
          $snow_3h = round($snow_3h);
        }
        echo '<p><strong>Snow Volume (3h)</strong>: ' . $snow_3h . ' ' . $snow_unit . '</p>';
      }

      echo '</div>';
    }

    echo $args['after_widget'];
  }


  public function form($instance)
  {
    $title = !empty($instance['title']) ? $instance['title'] : '';
    $zip = !empty($instance['zip']) ? $instance['zip'] : '';
    $country = !empty($instance['country']) ? $instance['country'] : '';
    $api_key = !empty($instance['api_key']) ? $instance['api_key'] : '';
    $units = !empty($instance['units']) ? $instance['units'] : 'imperial';
    $selected_css = !empty($instance['selected_css']) ? $instance['selected_css'] : '';
    $css_dir = plugin_dir_path(__FILE__) . '/css/';
    $css_files = glob($css_dir . '*.css');
?>

    <strong>General Settings</strong>
    <p>
      <label for="<?php echo $this->get_field_id('title'); ?>">Title:</label>
      <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>">
    </p>
    <p>
      <label for="<?php echo $this->get_field_id('zip'); ?>">ZIP Code:</label>
      <input class="widefat" id="<?php echo $this->get_field_id('zip'); ?>" name="<?php echo $this->get_field_name('zip'); ?>" type="text" value="<?php echo esc_attr($zip); ?>">
    </p>
    <p>
      <label for="<?php echo $this->get_field_id('country'); ?>">Country Code:</label>
      <input class="widefat" id="<?php echo $this->get_field_id('country'); ?>" name="<?php echo $this->get_field_name('country'); ?>" type="text" value="<?php echo esc_attr($country); ?>">
    </p>
    <p>
      <label for="<?php echo $this->get_field_id('api_key'); ?>">API Key:</label>
      <input class="widefat" id="<?php echo $this->get_field_id('api_key'); ?>" name="<?php echo $this->get_field_name('api_key'); ?>" type="text" value="<?php echo esc_attr($api_key); ?>">
    </p>
    <p>
      <label for="<?php echo $this->get_field_id('units'); ?>">Units:</label>
      <select class="widefat" id="<?php echo $this->get_field_id('units'); ?>" name="<?php echo $this->get_field_name('units'); ?>">
        <option value="metric" <?php echo ($units == 'metric') ? 'selected' : ''; ?>>Metric</option>
        <option value="imperial" <?php echo ($units == 'imperial') ? 'selected' : ''; ?>>Imperial</option>
      </select>
    </p>
    <strong>Weather Settings</strong>
    <p>
      <input class="checkbox" type="checkbox" <?php checked($instance['round_weather_data'], 'on'); ?> id="<?php echo $this->get_field_id('round_weather_data'); ?>" name="<?php echo $this->get_field_name('round_weather_data'); ?>" />
      <label for="<?php echo $this->get_field_id('round_weather_data'); ?>">Round Weather Data</label>
    </p>
    <p>
      <input class="checkbox" type="checkbox" <?php checked($instance['weather_icon'], 'on'); ?> id="<?php echo $this->get_field_id('weather_icon'); ?>" name="<?php echo $this->get_field_name('weather_icon'); ?>" />
      <label for="<?php echo $this->get_field_id('weather_icon'); ?>">Weather Icon</label>
    </p>
    <p>
      <input class="checkbox" type="checkbox" <?php checked($instance['name'], 'on'); ?> id="<?php echo $this->get_field_id('name'); ?>" name="<?php echo $this->get_field_name('name'); ?>" />
      <label for="<?php echo $this->get_field_id('name'); ?>">City</label>
    </p>
    <p>
      <input class="checkbox" type="checkbox" <?php checked($instance['weather_main'], 'on'); ?> id="<?php echo $this->get_field_id('weather_main'); ?>" name="<?php echo $this->get_field_name('weather_main'); ?>" />
      <label for="<?php echo $this->get_field_id('weather_main'); ?>">Summary</label>
    </p>
    <p>
      <input class="checkbox" type="checkbox" <?php checked($instance['description'], 'on'); ?> id="<?php echo $this->get_field_id('description'); ?>" name="<?php echo $this->get_field_name('description'); ?>" />
      <label for="<?php echo $this->get_field_id('description'); ?>">Description</label>
    </p>
    <p>
      <input class="checkbox" type="checkbox" <?php checked($instance['temp'], 'on'); ?> id="<?php echo $this->get_field_id('temp'); ?>" name="<?php echo $this->get_field_name('temp'); ?>" />
      <label for="<?php echo $this->get_field_id('temp'); ?>">Temperature</label>
    </p>
    <p>
      <input class="checkbox" type="checkbox" <?php checked($instance['feels_like'], 'on'); ?> id="<?php echo $this->get_field_id('feels_like'); ?>" name="<?php echo $this->get_field_name('feels_like'); ?>" />
      <label for="<?php echo $this->get_field_id('feels_like'); ?>">Feels Like</label>
    </p>
    <p>
      <input class="checkbox" type="checkbox" <?php checked($instance['pressure'], 'on'); ?> id="<?php echo $this->get_field_id('pressure'); ?>" name="<?php echo $this->get_field_name('pressure'); ?>" />
      <label for="<?php echo $this->get_field_id('pressure'); ?>">Pressure</label>
    </p>
    <p>
      <input class="checkbox" type="checkbox" <?php checked($instance['humidity'], 'on'); ?> id="<?php echo $this->get_field_id('humidity'); ?>" name="<?php echo $this->get_field_name('humidity'); ?>" />
      <label for="<?php echo $this->get_field_id('humidity'); ?>">Humidity</label>
    </p>
    <p>
      <input class="checkbox" type="checkbox" <?php checked($instance['temp_min'], 'on'); ?> id="<?php echo $this->get_field_id('temp_min'); ?>" name="<?php echo $this->get_field_name('temp_min'); ?>" />
      <label for="<?php echo $this->get_field_id('temp_min'); ?>">Min Temperature</label>
    </p>
    <p>
      <input class="checkbox" type="checkbox" <?php checked($instance['temp_max'], 'on'); ?> id="<?php echo $this->get_field_id('temp_max'); ?>" name="<?php echo $this->get_field_name('temp_max'); ?>" />
      <label for="<?php echo $this->get_field_id('temp_max'); ?>">Max Temperature</label>
    </p>
    <p>
      <input class="checkbox" type="checkbox" <?php checked($instance['visibility'], 'on'); ?> id="<?php echo $this->get_field_id('visibility'); ?>" name="<?php echo $this->get_field_name('visibility'); ?>" />
      <label for="<?php echo $this->get_field_id('visibility'); ?>">Visibility</label>
    </p>
    <p>
      <input class="checkbox" type="checkbox" <?php checked($instance['wind_speed'], 'on'); ?> id="<?php echo $this->get_field_id('wind_speed'); ?>" name="<?php echo $this->get_field_name('wind_speed'); ?>" />
      <label for="<?php echo $this->get_field_id('wind_speed'); ?>">Wind Speed</label>
    </p>
    <p>
      <input class="checkbox" type="checkbox" <?php checked($instance['wind_deg'], 'on'); ?> id="<?php echo $this->get_field_id('wind_deg'); ?>" name="<?php echo $this->get_field_name('wind_deg'); ?>" />
      <label for="<?php echo $this->get_field_id('wind_deg'); ?>">Wind Direction</label>
    </p>
    <p>
      <input class="checkbox" type="checkbox" <?php checked($instance['wind_gust'], 'on'); ?> id="<?php echo $this->get_field_id('wind_gust'); ?>" name="<?php echo $this->get_field_name('wind_gust'); ?>" />
      <label for="<?php echo $this->get_field_id('wind_gust'); ?>">Wind Gust</label>
    </p>
    <p>
      <input class="checkbox" type="checkbox" <?php checked($instance['clouds_all'], 'on'); ?> id="<?php echo $this->get_field_id('clouds_all'); ?>" name="<?php echo $this->get_field_name('clouds_all'); ?>" />
      <label for="<?php echo $this->get_field_id('clouds_all'); ?>">Cloudiness</label>
    </p>
    <p>
      <input class="checkbox" type="checkbox" <?php checked($instance['rain_1h'], 'on'); ?> id="<?php echo $this->get_field_id('rain_1h'); ?>" name="<?php echo $this->get_field_name('rain_1h'); ?>" />
      <label for="<?php echo $this->get_field_id('rain_1h'); ?>">Rain Volume (1h)</label>
    </p>
    <p>
      <input class="checkbox" type="checkbox" <?php checked($instance['rain_3h'], 'on'); ?> id="<?php echo $this->get_field_id('rain_3h'); ?>" name="<?php echo $this->get_field_name('rain_3h'); ?>" />
      <label for="<?php echo $this->get_field_id('rain_3h'); ?>">Rain Volume (3h)</label>
    </p>
    <p>
      <input class="checkbox" type="checkbox" <?php checked($instance['snow_1h'], 'on'); ?> id="<?php echo $this->get_field_id('snow_1h'); ?>" name="<?php echo $this->get_field_name('snow_1h'); ?>" />
      <label for="<?php echo $this->get_field_id('snow_1h'); ?>">Snow Volume (1h)</label>
    </p>
    <p>
      <input class="checkbox" type="checkbox" <?php checked($instance['snow_3h'], 'on'); ?> id="<?php echo $this->get_field_id('snow_3h'); ?>" name="<?php echo $this->get_field_name('snow_3h'); ?>" />
      <label for="<?php echo $this->get_field_id('snow_3h'); ?>">Snow Volume (3h)</label>
    </p>
    <strong>Style Settings</strong>
    <p>
      <label for="<?php echo $this->get_field_id('selected_css'); ?>">Select CSS File:</label>
      <select id="<?php echo $this->get_field_id('selected_css'); ?>" name="<?php echo $this->get_field_name('selected_css'); ?>" class="widefat">
        <?php foreach ($css_files as $css_file) : ?>
          <?php $css_filename = basename($css_file); ?>
          <option value="<?php echo $css_filename; ?>" <?php echo $css_filename == $selected_css ? ' selected="selected"' : ''; ?>><?php echo $css_filename; ?></option>
        <?php endforeach; ?>
      </select>
    </p>

<?php
  }

  public function update($new_instance, $old_instance)
  {
    $instance = array();
    $instance['title'] = (!empty($new_instance['title'])) ? sanitize_text_field($new_instance['title']) : '';
    $instance['zip'] = (!empty($new_instance['zip'])) ? sanitize_text_field($new_instance['zip']) : '';
    $instance['country'] = (!empty($new_instance['country'])) ? sanitize_text_field($new_instance['country']) : '';
    $instance['api_key'] = (!empty($new_instance['api_key'])) ? sanitize_text_field($new_instance['api_key']) : '';
    $instance['units'] = (!empty($new_instance['units'])) ? sanitize_text_field($new_instance['units']) : 'metric';
    $instance['round_weather_data'] = (!empty($new_instance['round_weather_data'])) ? sanitize_text_field($new_instance['round_weather_data']) : '';
    $instance['weather_icon'] = (!empty($new_instance['weather_icon'])) ? sanitize_text_field($new_instance['weather_icon']) : '';
    $instance['name'] = (!empty($new_instance['name'])) ? sanitize_text_field($new_instance['name']) : '';
    $instance['weather_main'] = (!empty($new_instance['weather_main'])) ? sanitize_text_field($new_instance['weather_main']) : '';
    $instance['description'] = (!empty($new_instance['description'])) ? sanitize_text_field($new_instance['description']) : '';
    $instance['temp'] = (!empty($new_instance['temp'])) ? sanitize_text_field($new_instance['temp']) : '';
    $instance['feels_like'] = (!empty($new_instance['feels_like'])) ? sanitize_text_field($new_instance['feels_like']) : '';
    $instance['pressure'] = (!empty($new_instance['pressure'])) ? sanitize_text_field($new_instance['pressure']) : '';
    $instance['humidity'] = (!empty($new_instance['humidity'])) ? sanitize_text_field($new_instance['humidity']) : '';
    $instance['temp_min'] = (!empty($new_instance['temp_min'])) ? sanitize_text_field($new_instance['temp_min']) : '';
    $instance['temp_max'] = (!empty($new_instance['temp_max'])) ? sanitize_text_field($new_instance['temp_max']) : '';
    $instance['visibility'] = (!empty($new_instance['visibility'])) ? sanitize_text_field($new_instance['visibility']) : '';
    $instance['wind_speed'] = (!empty($new_instance['wind_speed'])) ? sanitize_text_field($new_instance['wind_speed']) : '';
    $instance['wind_deg'] = (!empty($new_instance['wind_deg'])) ? sanitize_text_field($new_instance['wind_deg']) : '';
    $instance['wind_gust'] = (!empty($new_instance['wind_gust'])) ? sanitize_text_field($new_instance['wind_gust']) : '';
    $instance['clouds_all'] = (!empty($new_instance['clouds_all'])) ? sanitize_text_field($new_instance['clouds_all']) : '';
    $instance['rain_1h'] = (!empty($new_instance['rain_1h'])) ? sanitize_text_field($new_instance['rain_1h']) : '';
    $instance['rain_3h'] = (!empty($new_instance['rain_3h'])) ? sanitize_text_field($new_instance['rain_3h']) : '';
    $instance['snow_1h'] = (!empty($new_instance['snow_1h'])) ? sanitize_text_field($new_instance['snow_1h']) : '';
    $instance['snow_3h'] = (!empty($new_instance['snow_3h'])) ? sanitize_text_field($new_instance['snow_3h']) : '';
    $instance['selected_css'] = (!empty($new_instance['selected_css'])) ? strip_tags($new_instance['selected_css']) : '';

    return $instance;
  }

  private function get_weather_data($zip, $country, $api_key, $units)
  {
    if (empty($zip) || empty($country) || empty($api_key)) {
      return array('error' => 'Invalid ZIP code, country code, or API key');
    }
    $geo_url = 'http://api.openweathermap.org/geo/1.0/zip?zip=' . $zip . ',' . $country . '&appid=' . $api_key;
    $geo_response = wp_remote_get($geo_url);

    if (is_wp_error($geo_response) || wp_remote_retrieve_response_code($geo_response) != 200) {
      return array('error' => 'Invalid ZIP code, country code, or API key');
    }

    $geo_body = wp_remote_retrieve_body($geo_response);
    $geo_data = json_decode($geo_body, true);

    $lat = $geo_data['lat'];
    $lon = $geo_data['lon'];

    $weather_url = 'http://api.openweathermap.org/data/2.5/weather?lat=' . $lat . '&lon=' . $lon . '&appid=' . $api_key . '&units=' . $units;
    $weather_response = wp_remote_get($weather_url);

    if (is_wp_error($weather_response) || wp_remote_retrieve_response_code($weather_response) != 200) {
      return array('error' => 'Invalid ZIP code, country code, or API key');
    }

    $weather_body = wp_remote_retrieve_body($weather_response);
    $weather_data = json_decode($weather_body, true);

    $result['weather_icon'] = $weather_data['weather'][0]['icon'];
    $result['name'] = $weather_data['name'];
    $result['weather_main'] = $weather_data['weather'][0]['main'];
    $result['description'] = $weather_data['weather'][0]['description'];
    $result['temp'] = $weather_data['main']['temp'];
    $result['feels_like'] = $weather_data['main']['feels_like'];
    $result['pressure'] = $weather_data['main']['pressure'];
    $result['humidity'] = $weather_data['main']['humidity'];
    $result['temp_min'] = $weather_data['main']['temp_min'];
    $result['temp_max'] = $weather_data['main']['temp_max'];
    $result['visibility'] = $weather_data['visibility'];
    $result['wind_speed'] = isset($weather_data['wind']['speed']) ? $weather_data['wind']['speed'] : null;
    $result['wind_deg'] = isset($weather_data['wind']['deg']) ? $weather_data['wind']['deg'] : null;
    $result['wind_gust'] = isset($weather_data['wind']['gust']) ? $weather_data['wind']['gust'] : null;
    $result['clouds_all'] = $weather_data['clouds']['all'];
    $result['rain_1h'] = isset($weather_data['rain']['1h']) ? $weather_data['rain']['1h'] : null;
    $result['rain_3h'] = isset($weather_data['rain']['3h']) ? $weather_data['rain']['3h'] : null;
    $result['snow_1h'] = isset($weather_data['snow']['1h']) ? $weather_data['snow']['1h'] : null;
    $result['snow_3h'] = isset($weather_data['snow']['3h']) ? $weather_data['snow']['3h'] : null;

    return $result;
  }
}

function register_weather_widget()
{
  register_widget('Easy_Weather_Widget');
}

add_action('widgets_init', 'register_weather_widget');
?>