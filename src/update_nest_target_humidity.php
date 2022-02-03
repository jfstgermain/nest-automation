<?php

require_once dirname(__FILE__) . '/../lib/nest.class.php';

// Your Nest username and password.
//define('USERNAME', $_SERVER["NEST_USERNAME"]);
//define('PASSWORD', $_SERVER["NEST_PASSWORD"]);
define('POSTAL_CODE', $_SERVER["POSTAL_CODE"]);
define('COUNTRY', $_SERVER["COUNTRY"]);
define("MAX_HUMIDITY", 45);
define("MIN_HUMIDITY", 15);
define("LOG_FILENAME", "/tmp/humidity.log");
define("ISSUE_TOKEN", $_SERVER["ISSUE_TOKEN"]);
define("COOKIES", $_SERVER["COOKIES"]);

$nest = new Nest();

// Get the device information:
$nest_info = $nest->getDeviceInfo();
$current_target_humidity = $nest_info->target->humidity;
$inside_hum = $nest_info->current_state->humidity;
$is_heating = $nest_info->current_state->heat;

$weather_data = $nest->getWeather(POSTAL_CODE, COUNTRY);
// $outside_temp = ($weather_data->outside_temperature * 1.8) + 32; // convert to celcius
$outside_temp = $weather_data->outside_temperature;
$outside_hum = $weather_data->outside_humidity;

// set target humidity based on outside temperature (in Fahrenheit)
if ($outside_temp >= 4) {
  $target_hum = 40;
} elseif ($outside_temp >= -1) {
  $target_hum = 37;
} elseif ($outside_temp >= -7) {
  $target_hum = 35;
} elseif ($outside_temp >= -12) {
  $target_hum = 30;
} elseif ($outside_temp >= -18) {
  $target_hum = 25;
} elseif ($outside_temp >= -23) {
  $target_hum = 20;
} elseif ($outside_temp >= -29) {
  $target_hum = 15;
}

$log_string = '';
$humidity_difference = $inside_hum - $target_hum;
//debug_log("humidity difference = $humidity_difference");

if ($is_heating && $humidity_difference < 4) {
  // Nest likes to get the humidity up to 5% more than what you set it to.  So,
  // if you set it to 30%, it will humidify up to 35%.  It tends to run the heat for a
  // while, then after the heat shuts off start humidifying.  It's more efficient to humidify
  // while the heat is running since it doesn't consume any extra electricity because the fan is
  // already running.  So, if the heat is currently running, we'll bump up the target humidity
  // temporarily to preemptively start humidifying, so that once the heat shuts off, we hopefully
  // won't need to run the humidifier on its own
  $log_string .= "Bumping target humidity up by 5% because heat is on. ";
  $target_hum += 5;
}

// safety check
$target_hum = max($target_hum, MIN_HUMIDITY);
$target_hum = min($target_hum, MAX_HUMIDITY);

// only make the call to Nest if we are changing the target humidity from what it is set to now
$should_change = $target_hum != $current_target_humidity;

if ($should_change) {
  $log_string .= "Setting Nest target humidity to $target_hum% for outside temp {$outside_temp}°c (current humidity inside = $inside_hum%, outside = $outside_hum%)";
} else {
  $log_string .= "Maintaining Nest target humidity of $target_hum% for outside temp {$outside_temp}°c (current humidity inside = $inside_hum%, outside = $outside_hum%)";
}

debug_log($log_string);

if ($should_change) {
  $return = $nest->setHumidity($target_hum);
  if ($return != 1) {
    debug_log("ERROR: Nest returned: $return\n");
  }
}

function debug_log($message)
{
  $date = date('m-d-Y H:i:s');

  $message .= "\n";
  file_put_contents(LOG_FILENAME, "$date $message", FILE_APPEND);
  print "({$date}) $message";
}
