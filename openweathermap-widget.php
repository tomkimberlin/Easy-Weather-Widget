<?php
/*
Plugin Name: OpenWeatherMap Widget
Description: Displays real-time weather for any ZIP code. Customizable details include temperature, wind, pressure, and more.
Version: 1.0
Author: Tom Kimberlin
Author URI: https://kimberlin.net
*/

/**
 * Registers the settings for the OpenWeatherMap widget, 
 * creating a new option for each setting if it does not already exist.
 */
function weather_widget_register_settings()
{
  $options = [
    'api_key' => '',
    'zipcode' => '',
    'show_city' => 'on',
    'temp' => 'on',
    'feels_like' => 'on',
    'summary' => 'on',
    'desc' => '',
    'humidity' => '',
    'wind_speed' => '',
    'pressure' => '',
    'visibility' => '',
    'style' => 'default',
    'rounded_corners' => 'off'
  ];

  foreach ($options as $name => $default) {
    $option_name = "weather_widget_option_$name";
    add_option($option_name, sanitize_text_field($default));
    register_setting('weather_widget_options_group', $option_name);
  }
}


add_action('admin_init', 'weather_widget_register_settings');

/**
 * Adds the OpenWeatherMap Widget settings page to the WordPress admin menu.
 */
function weather_widget_register_options_page()
{
  add_options_page('OpenWeatherMap Widget', 'OpenWeatherMap Widget', 'manage_options', 'weatherwidget', 'weather_widget_options_page');
}

add_action('admin_menu', 'weather_widget_register_options_page');

/**
 * Renders the OpenWeatherMap Widget settings page,
 * providing form fields for all the settings and allowing the user to update them.
 */
function weather_widget_options_page()
{
  $options = [
    'general' => [
      'title' => 'General Settings',
      'api_key' => ['API Key', 'text', '', 'You can register for a free API key on <a href="http://openweathermap.org/register" target="_blank">OpenWeatherMap\'s website</a>.'],
      'zipcode' => ['Default ZIP Code', 'text', ''],
    ],
    'weather' => [
      'title' => 'Weather Options',
      'show_city' => ['Show City', 'checkbox', 'on'],
      'temp' => ['Temperature', 'checkbox', 'on'],
      'feels_like' => ['Feels Like', 'checkbox', 'on'],
      'summary' => ['Summary', 'checkbox', 'on'],
      'desc' => ['Description', 'checkbox', 'off'],
      'humidity' => ['Humidity', 'checkbox', 'off'],
      'wind_speed' => ['Wind Speed', 'checkbox', 'off'],
      'pressure' => ['Pressure', 'checkbox', 'off'],
      'visibility' => ['Visibility', 'checkbox', 'off'],
    ],
    'style' => [
      'title' => 'Style Settings',
      'style' => ['Style', 'select', array_combine(fetch_css_files(), fetch_css_files())],
      'rounded_corners' => ['Rounded Corners', 'checkbox', 'off'],
    ]
  ];

?>
  <div>
    <h2>OpenWeatherMap Widget Settings</h2>
    <form method="post" action="options.php">
      <?php settings_fields('weather_widget_options_group'); ?>
      <?php do_settings_sections('weather_widget_options_group'); ?>

      <?php foreach ($options as $group => $groupData) : ?>
        <h3><?php echo $groupData['title']; ?></h3>
        <?php foreach ($groupData as $option => $value) : ?>
          <?php if ($option === 'title') continue; ?>
          <?php
          $label = $value[0];
          $type = $value[1];
          $default = $value[2];
          $note = isset($value[3]) ? $value[3] : '';
          ?>
          <p>
            <label for="weather_widget_option_<?php echo $option; ?>"><?php echo $label; ?></label>
            <?php if ($type === 'text') : ?>
              <input type="text" id="weather_widget_option_<?php echo $option; ?>" name="weather_widget_option_<?php echo $option; ?>" value="<?php echo get_option("weather_widget_option_$option", $default); ?>" />
            <?php elseif ($type === 'checkbox') : ?>
              <input type="checkbox" id="weather_widget_option_<?php echo $option; ?>" name="weather_widget_option_<?php echo $option; ?>" <?php checked(get_option("weather_widget_option_$option", $default), 'on'); ?> />
            <?php elseif ($type === 'select') : ?>
              <select id="weather_widget_option_<?php echo $option; ?>" name="weather_widget_option_<?php echo $option; ?>">
                <?php foreach ($value[2] as $optValue => $optLabel) : ?>
                  <option value="<?php echo $optValue; ?>" <?php selected(get_option("weather_widget_option_$option", $default), $optValue); ?>><?php echo $optLabel; ?></option>
                <?php endforeach; ?>
              </select>
            <?php endif; ?>
            <?php if ($note) : ?>
              <small><?php echo $note; ?></small>
            <?php endif; ?>
          </p>
        <?php endforeach; ?>
      <?php endforeach; ?>

      <?php submit_button(); ?>
    </form>
  </div>
  <?php
}

class OpenWeatherMap_Widget extends WP_Widget
{
  function __construct()
  {
    parent::__construct(
      'OpenWeatherMap_Widget',
      esc_html__('OpenWeatherMap Widget', 'text_domain'),
      array('description' => esc_html__('A widget to display the weather of a specified zip code', 'text_domain'),)
    );
  }

  /**
   * Displays the OpenWeatherMap Widget on the front end of the site. 
   * Retrieves the weather data from the OpenWeatherMap API and renders it in the widget.
   */
  public function widget($args, $instance)
  {
    $zipcode = !empty($instance['zipcode']) ? $instance['zipcode'] : get_option('weather_widget_option_zipcode');
    $api_key = get_option('weather_widget_option_api_key');
    $api_url = "http://api.openweathermap.org/data/2.5/weather?zip={$zipcode}&units=imperial&appid={$api_key}";
    $response = wp_remote_get($api_url);

    if (is_wp_error($response) || wp_remote_retrieve_response_code($response) != 200) {
      error_log($response->get_error_message());
      return;
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body);

    if (!empty($data)) {
      $city_name = $data->name;
      $weather_data = [
        'temp' => ['Temperature', "{$data->main->temp}°F"],
        'feels_like' => ['Feels Like', "{$data->main->feels_like}°F"],
        'summary' => ['Summary', $data->weather[0]->main],
        'desc' => ['Description', $this->to_capitalized_case($data->weather[0]->description)],
        'humidity' => ['Humidity', "{$data->main->humidity}%"],
        'wind_speed' => ['Wind Speed', "{$data->wind->speed} m/s"],
        'pressure' => ['Pressure', number_format($data->main->pressure) . " hPa"],
        'visibility' => ['Visibility', number_format($data->visibility) . " m"]
      ];

      echo $args['before_widget'];
      echo $args['before_title'] . 'Weather' . $args['after_title'];

      $style = '';
      if (get_option('weather_widget_option_rounded_corners') === 'on') {
        $style = ' style="border-radius: 10px;"';
      }

      echo "<div class='weather-widget-content'$style>";

      $icon_id = $data->weather[0]->icon;
      $icon_url = "http://openweathermap.org/img/w/{$icon_id}.png";
      echo "<img class='weather-icon' src='{$icon_url}' alt='Weather icon' />";

      // Check the 'show_city' option and display city name
      if (get_option('weather_widget_option_show_city') === 'on') {
        echo "<p class='weather-city'><strong>City:</strong> {$city_name}</p>";
      }

      foreach ($weather_data as $key => $info) {
        if (get_option("weather_widget_option_$key") === 'on') {
          echo "<p class='weather-data'><strong>{$info[0]}:</strong> {$info[1]}</p>";
        }
      }

      echo "</div>";
      echo $args['after_widget'];
    }
  }

  /**
   * Renders the form on the widget settings page in the WordPress admin area. 
   * This allows the user to set a custom ZIP code for the widget.
   */
  public function form($instance)
  {
    $zipcode = !empty($instance['zipcode']) ? $instance['zipcode'] : esc_html__('', 'text_domain');
  ?>
    <p>
      <label style="margin:auto;" for="<?php echo esc_attr($this->get_field_id('zipcode')); ?>"><?php esc_attr_e('ZIP Code:', 'text_domain'); ?></label>
      <input class="widefat" id="<?php echo esc_attr($this->get_field_id('zipcode')); ?>" name="<?php echo esc_attr($this->get_field_name('zipcode')); ?>" type="text" value="<?php echo esc_attr(sanitize_text_field($zipcode)); ?>">
    </p>
<?php
  }

  /**
   * Updates the widget settings based on the user input from the settings form.
   */
  public function update($new_instance, $old_instance)
  {
    $instance = array();
    $instance['zipcode'] = (!empty($new_instance['zipcode'])) ? strip_tags($new_instance['zipcode']) : '';
    return $instance;
  }

  private function to_capitalized_case($string)
  {
    return ucwords(strtolower($string));
  }
}

/**
 * Registers the OpenWeatherMap Widget so it can be added to widget areas on the site.
 */
function register_openweathermap_widget()
{
  register_widget('OpenWeatherMap_Widget');
}

add_action('widgets_init', 'register_openweathermap_widget');

/**
 * Adds a settings link to the OpenWeatherMap Widget on the plugins page.
 */
function openweathermap_widget_plugin_settings_link($links)
{
  $settings_link = '<a href="options-general.php?page=weatherwidget">Settings</a>';
  array_unshift($links, $settings_link);
  return $links;
}

$plugin = plugin_basename(__FILE__);
add_filter("plugin_action_links_$plugin", 'openweathermap_widget_plugin_settings_link');

/**
 * Fetch all CSS files from the assets/css directory.
 */
function fetch_css_files()
{
  $css_dir = plugin_dir_path(__FILE__) . 'assets/css/';
  $css_files = array();

  if (is_dir($css_dir)) {
    foreach (glob($css_dir . '*.css') as $file) {
      $css_files[] = basename($file, '.css');
    }
  }

  return $css_files;
}

/**
 * Enqueues the stylesheet for the OpenWeatherMap Widget.
 */
function enqueue_openweathermap_widget_styles()
{
  $style = get_option('weather_widget_option_style', 'default');
  wp_enqueue_style('openweathermap-widget', plugin_dir_url(__FILE__) . "assets/css/{$style}.css");
}

add_action('wp_enqueue_scripts', 'enqueue_openweathermap_widget_styles');
