<?php
# The nagios-dashboard was written by Morten Bekkelund & Jonas G. Drange in 2010
#
# Patched, modified and added to by various people, see README
# Maintained as merlin-dashboard by Mattias Bergsten <mattias.bergsten@op5.com>
#
# Parts copyright (C) 2010 Morten Bekkelund & Jonas G. Drange
#
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU General Public License for more details.
#
# See: http://www.gnu.org/copyleft/gpl.html

if (file_exists('../inc/ncs_session.php')) {
  // NCS
  include_once('../inc/ncs_session.php');
} else {
  session_start();
}

$thisserver = $_SESSION['servers']['parent'];

// Set the hwid variable and redirect, removing it from the address bar
if (isset($_GET['hwid'])) {
  $_SESSION['tv']['hwid'] = filter_var($_GET['hwid'],FILTER_SANITIZE_STRING);
  header('location:./');
  exit();
}

if ($_SERVER['SERVER_NAME'] == 'cloud.nemslinux.com') {
  $connector = 'ncs.php';
  $cloud = 1;
  require_once('../inc/bgcolor.php');
} else {
  $connector = 'livestatus.php';
  $cloud = 0;
  require_once('/var/www/html/inc/bgcolor.php');
}

    $refreshvalue = 30; //value in seconds to refresh data
    $pagetitle = "NEMS TV Dashboard";

    /* add this when we support individual
    $nemsalias = trim(shell_exec('/usr/local/bin/nems-info alias'));
    if (isset($nemsalias) && strtoupper($nemsalias) != 'NEMS') {
       $pagetitle = 'NEMS TV: ' . $nemsalias;
    }
*/

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
    <head>
	<link type="image/ico" rel="icon" href="favicon.ico" />
        <title><?php echo($pagetitle); ?></title>
        <script src="https://cloud.nemslinux.com/assets/js/jquery.min.js"></script>
        <script src="https://cloud.nemslinux.com/assets/js/loadingoverlay.min.js"></script>

        </script>
		<link rel="stylesheet" type="text/css" href="nagios.css?b" />
		<link rel="stylesheet" type="text/css" href="/css/font-awesome.min.css" />
        <style>
          .nagios_statusbar {
            background: rgba(<?= $bgcolorDarkRGB[0] ?>,<?= $bgcolorDarkRGB[1] ?>,<?= $bgcolorDarkRGB[2] ?>,.8) !important;
          }
        </style>
    </head>
    <body>

        <script type="text/javascript">

            var placeHolder,
            refreshValue = <?php print $refreshvalue; ?>;

            $().ready(function(){
                placeHolder = $("#nagios_placeholder");
                updateNagiosData(placeHolder);
                window.setInterval(updateCountDown, 1000);
            });



            // timestamp stuff

            function createTimeStamp() {
                <?php
                  if ($_SERVER['SERVER_NAME'] == 'cloud.nemslinux.com') {
                    $tv_24h = intval(trim($_SESSION['servers']['parent']['settings']->tv_24h));
                  } else {
                    $tv_24h = intval(trim(shell_exec('/usr/local/bin/nems-info tv_24h')));
                  }
                ?>
                // create timestamp
                var ts = new Date();
		ts = ts.toLocaleTimeString(navigator.language, { hour: '2-digit', minute:'2-digit', <?php if ($tv_24h == 1) { echo 'hour12: false'; } else { echo 'hour12: true'; } ?> } ).replace(/^0(?:0:0?)?/, '')<?php if ($tv_24h != 1 && $tv_24h != 2) echo '.replace(/(:\d{2}| [ap]m)$/, "")'; ?>;
                $("#timestamp_wrap").empty().append("<div class=\"timestamp_drop\"></div><div class=\"timestamp_stamp\">" + ts +"</div>");
            }

            function updateNagiosData(block){
                $("#loading").fadeIn(200);
    		block.load("./connectors/<?= $connector ?>", function(response){
                    $(this).html(response);
                    $("#loading").fadeOut(200);
                    createTimeStamp();
                });
            }

            function updateCountDown(){
                var countdown = $("#refreshing_countdown");
                var remaining = parseInt(countdown.text());
                if(remaining == 1){
                    updateNagiosData(placeHolder);
                    countdown.text(remaining - 1);
                }
                else if(remaining == 0){
                    countdown.text(refreshValue);
                }
                else {
                    countdown.text(remaining - 1);
                }
            }

            function refreshAt(hours, minutes, seconds) {
              var now = new Date();
              var then = new Date();

              if ( now.getHours() > hours || (now.getHours() == hours && now.getMinutes() > minutes) || now.getHours() == hours && now.getMinutes() == minutes && now.getSeconds() >= seconds ) {
                then.setDate(now.getDate() + 1);
              }
              then.setHours(hours);
              then.setMinutes(minutes);
              then.setSeconds(seconds);

              var timeout = (then.getTime() - now.getTime());
              setTimeout(function() { window.location.reload(true); }, timeout);
            }

            // force a page reload at 4am every day (for those who leave the TV Dashboard open 24/7 to see the day's background)
            refreshAt(4,0,0);

        </script>
	<div id="nagios_placeholder"></div>
    <div class="nagios_statusbar">
	<div class="nagios_statusbar_logo">
            <p id="logo_holder"><span id="logo"></span></p>
	</div>
        <div class="nagios_statusbar_item">
            <div id="timestamp_wrap"></div>
        </div>
        <div class="nagios_statusbar_item">
            <div id="speed_wrap"></div>
        </div>
        <div class="nagios_statusbar_item">
            <div id="loading"></div>
            <p id="refreshing"><span id="refreshing_countdown"><?php print $refreshvalue; ?></span> seconds to next check</p>
        </div>
    </div>

    <script src="https://cloud.nemslinux.com/assets/js/jquery.backstretch.min.js"></script>

   <?php
     $backgroundElem = 'body';
      if ($_SERVER['SERVER_NAME'] == 'cloud.nemslinux.com') {
        require_once('../inc/wallpaper.php');
        echo '<style>#logo { background: transparent url(/assets/img/ncs_logo_padded.png); background-size: 170px 60px; }</style>';
      } else {
        require_once('/var/www/html/inc/wallpaper.php');
      }
   ?>

<script>
  jQuery(document).ready(function() {

    $(function() {
      var timer;
      var fadeInBuffer = false;
      $(document).mousemove(function() {
        if (!fadeInBuffer) {
            if (timer) {
                clearTimeout(timer);
                timer = 0;
            }

            $('html').css({
                cursor: '',
                overflow: 'auto'
            });
        } else {
            $('body').css({
                cursor: 'default',
                overflow: 'auto'
            });
            fadeInBuffer = false;
        }


        timer = setTimeout(function() {
            $('body').css({
                cursor: 'none',
                overflow: 'hidden'
            });
            $('html').css({
                overflow: 'hidden'
            });

            fadeInBuffer = true;
        }, 5000)
      });
      $('body').css({
        cursor: 'default',
        overflow: 'auto'
      });
      $('html').css({
        overflow: 'auto'
      });
    });


    check_connect();

  });

  <?php
    if ($cloud == 0) {
      // Local Version
  ?>
    function check_connect() {
    $.ajax({
      type: 'GET',
      url: '/tv/',
      timeout: 5000,  // allow this many milisecs for network connect to succeed
      success: function(data) {
        // we have a connection
        $.LoadingOverlay("hide");
        window.setTimeout(check_connect, 15000)  // try again after 15 minutes
      },
      error: function(XMLHttpRequest, textStatus, errorThrown) {
        // no connection, refresh every 15 seconds until resolved
        $.LoadingOverlay("show");
        window.setTimeout(check_connect, 15000)
      }
      });
    };

    function speedtest(){
      $('#speed_wrap').load('connectors/speedtest.php');
    }
    speedtest(); // This will run on page load
    setInterval(function(){
      speedtest() // this will run after every 1 minute
    }, 60000);

  <?php
    } else {
  ?>
      // Cloud Version

      // need to make this check every few minutes via ajax call to external script.
      function check_connect() {
        <?php
          if (strtotime($thisserver['last_sync']) < strtotime('-10 minutes')) {
            echo '$.LoadingOverlay("show");';
          } else {
            echo '$.LoadingOverlay("hide");';
          }
        ?>
      }
  <?php
    }
  ?>
</script>

    </body>
</html>
