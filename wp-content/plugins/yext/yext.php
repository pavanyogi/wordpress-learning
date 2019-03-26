<?php
/**
 * @package Yext Pages
 * @version 1.0.8
 */
/*
Plugin Name: Yext Pages
Plugin URI: http://www.wordpress.org/plugins/yext/
Description: Publish your Yext posts, products, bios, menus, events, or reviews.
Author: Yext Engineering
Version: 1.0.8
*/

register_activation_hook(__FILE__,'yext_install');
register_deactivation_hook( __FILE__, 'yext_remove' );
function yext_install() {
    add_option('yext_token', '', '', 'yes');
    add_option('yext_logging', false, '', 'yes');
}
function yext_remove() {
    delete_option('yext_token');
    delete_option('yext_logging');
}

function yext_shortcode($atts, $content, $tag) {
    $val = shortcode_atts(array(
      'id' => 0,
    ), $atts );
    $widgetType = substr($tag, strlen('yext-'));
    return _yext_content('/Serving/WordpressHtml?'.
      'widgetType='.$widgetType.'&id='.$val['id'],
      false);
}
add_shortcode('yext-posts', 'yext_shortcode');
add_shortcode('yext-productlist', 'yext_shortcode');
add_shortcode('yext-calendar', 'yext_shortcode');
add_shortcode('yext-bios', 'yext_shortcode');
add_shortcode('yext-menu', 'yext_shortcode');
add_shortcode('yext-reviews', 'yext_shortcode');

function widgets_serving_host() {
  $host = @$_GET['host'];
  if (!$host) {
    $host = @$_ENV['YEXT_WIDGETS_SERVING_HOST'];
  }
  if (!$host) {
    return array('sites.yext.com');
  }
  return array($host, '/sites');
}

function widgets_storm_host() {
  $host = @$_GET['host'];
  if (!$host) {
    $host = @$_ENV['YEXT_WIDGETS_STORM_HOST'];
  }
  if (!$host) {
    return "www.yext.com/w";
  }
  return $host.'/w';
}

function _yext_content($path, $internalGetPage) {
  $serving_info = widgets_serving_host();
  if (!$serving_info) {
    return 'Host not provided.';
  }
  if (count($serving_info) == 1) {
    return _get_page($serving_info[0], $path, $internalGetPage);
  } else {
    return _get_page($serving_info[0], $serving_info[1].$path, $internalGetPage);
  }
}

function _get_page($host, $path, $internalGetPage) {
  $shouldLog = get_option('yext_logging') && !$internalGetPage;
  if ($shouldLog) {
    sendLogMessage('opening connection path: '.$path);
  }
  ob_start();
  $sock = @fsockopen($host, 80, $errno, $errstr, 1);
  if(!$sock) {
    ob_end_clean();
    if ($shouldLog) {
      sendLogMessage('failed to connect, errno: '.$errno.', errstr: '.$errstr);
    }
    return '<!--Whoops! There was an error loading your widget!-->';
  }

  $payload = "GET " . $path . " HTTP/1.0\r\n";
  $payload .= "Host: " . $host . "\r\n";
  $payload .= "Accept: text/html\r\n\r\n";

  $response = "";
  fwrite($sock, $payload);
  stream_set_timeout($sock, 10);
  $info = stream_get_meta_data($sock);

  if ($shouldLog) {
    sendLogMessage('reading message from path: '.$path);
  }

  while ((!feof($sock)) && (!$info["timed_out"])) {
    $response .= fread($sock, 8192);
    $info = stream_get_meta_data($sock);
  }

  fclose($sock);
  ob_end_clean();

  list($headers, $response) = explode("\r\n\r\n", $response, 2);
  $headers = explode('\r\n', $headers);
  $status = explode(' ', $headers[0]);

  if ((int)$status[1] == 200) {
    if ($shouldLog) {
      sendLogMessage('successfully received message from path: '.$path);
    }
    return $response;
  }
  if ($shouldLog) {
    sendLogMessage('did not receive expected response, actual response: '.$response);
  }
  return '<!--Whoops!! There was an error loading your widget!-->';
}

function sendLogMessage($message) {
  $token = get_option('yext_token');
  $path = '/Serving/ReceiveLogMessage?token='.$token.'&message='.urlencode($message);
  _yext_content($path, true);
}

// If there is a host specified in the url, set that in the cookie.
add_action('init', 'yext_init_function');
function yext_init_function() {
  ob_start();
  if (isset($_GET['host'])) {
    setcookie('yext_widgets_host', $_GET['host']);
  }
}

add_action('admin_menu', 'yext_plugin_menu');
function yext_plugin_menu() {
  ob_start();
  add_plugins_page('Widgets! by Yext', 'Yext', 'read', 'yext', 'yext_plugin_function');
  add_management_page('Widgets! by Yext', 'Yext', 'read', 'yext', 'yext_plugin_function');
  add_menu_page( 'Widgets! by Yext', 'Yext', 'read', 'yext', 'yext_plugin_function', 'div');
}

add_action('admin_enqueue_scripts', 'register_yext_plugin_styles');
function register_yext_plugin_styles() {
  wp_register_style('yext', plugins_url('yext/public/css/yext.css'));
  wp_enqueue_style('yext');
}

add_action('admin_action_yext_savetoken', 'yext_savetoken');
function yext_savetoken() {
  update_option('yext_token', $_POST['yext_token']);
  exit();
}

add_action('admin_action_yext_removetoken', 'yext_removetoken');
function yext_removetoken() {
  update_option('yext_token', '');
  exit();
}

function check_logging() {
  $token = get_option('yext_token');
  if (!$token) {
    return;
  }
  $json = json_decode(_yext_content("/Serving/ShouldLog?token=".$token, false), true);
  if ($json["needToLog"] === "true") {
    update_option('yext_logging', true);
  } else {
    update_option('yext_logging', false);
  }
}

function yext_plugin_function() {
  $storm_host = widgets_storm_host();
  $wp_host = php_uname('n');
  if (!$storm_host) {
    echo 'No host found.';
    return;
  }
  check_logging();
?>
  <iframe id="yext-widget-iframe"
          style="width:100%;height:800px;"
          src="//<?php echo $storm_host;?>/Wordpress/Dashboard?token=<?php echo urlencode(get_option('yext_token'));?>"
          onload="establishConnection()">
  </iframe>

  <form id="persist-token-form" method="post" action="<?php echo admin_url('admin.php'); ?>">
    <input id="yext-login-token" type="hidden" name="yext_token" value="">
    <input type="hidden" name="action" value="yext_savetoken">
  </form>

  <form id="remove-token-form" method="post" action="<?php echo admin_url('admin.php'); ?>">
    <input type="hidden" name="action" value="yext_removetoken" />
  </form>

  <script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.1/jquery.min.js"></script>

  <script type="text/javascript">
    // Used to invoke the save/remove token actions in wordpress-land
    var invokeFormAction = function($form, cb) {
      $.ajax({
        url: $form.attr('action'),
        method: $form.attr('method'),
        data: $form.serialize()
      }).done(function() {
        cb();
      });
    };

    // Called whenever the inner iframe loads to re-establish a connection
    window.establishConnection = function() {
      window.sendConnectionRequest();
      window.requestConnectionInterval = window.setInterval(window.sendConnectionRequest, 100);
    };

    // Send a message to the inner iframe to establish future communication
    window.sendConnectionRequest = function() {
      var o = document.getElementById('yext-widget-iframe').contentWindow;
      o.postMessage({
        'name': 'establishConnection',
        'wp-host': '<?php echo $wp_host;?>'
      },
      '*');
    };

    // Parse and process messages received from the inner iframe
    window.receiveMessage = function(event) {
      window.iFrameTarget = event.source;

      if (event.data['name'] == 'establishConnection') {
        window.clearInterval(requestConnectionInterval);

        // Set up the message passing for the dynamic height messages
        window.sendHeightRequest = function() {
          window.iFrameTarget.postMessage({'name': 'getWindowHeight'}, '*');
        };
        window.sendHeightRequest();
        window.setInterval(window.sendHeightRequest, 100);

      } else if (event.data['name'] == 'getWindowHeight') {
        document.getElementsByTagName('iframe')[0].style.height = event.data['height'] + 'px';

      } else if (event.data['name'] == 'persistYextToken') {
        $('#yext-login-token').val(event.data['token']);
        invokeFormAction($('#persist-token-form'), function() {
          window.iFrameTarget.postMessage({'name': 'persistYextToken'}, '*');
        });

      } else if (event.data['name'] == 'removeYextToken') {
        invokeFormAction($('#remove-token-form'), function() {
          window.iFrameTarget.postMessage({'name': 'removeYextToken'}, '*');
        });
      }
    };

    if (window.addEventListener) {
      addEventListener("message", window.receiveMessage, false);
    } else {
      attachEvent("onmessage", window.receiveMessage);
    }
  </script>
<?php } ?>
