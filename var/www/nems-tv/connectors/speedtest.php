<?php
  // Interpret the speedtest cache log to output on NEMS TV Dashboard
  // Will not show up until NEMS Linux 1.6 since /var/log/nems is owned by root
  // Can chown to nagios:nagios to correct that, if desired
  if (file_exists('/var/log/nems/speedtest.log')) {
    $speedtest = file('/var/log/nems/speedtest.log');
  }
  if (is_array($speedtest)) {
    if ($speedtest[0] == 0) { // OK
      $color = '#8bff6e';
    } elseif ($speedtest[0] == 1) { // WARN
      $color = '#ffa100';
    } elseif ($speedtest[0] == 2) { // CRIT
      $color = '#ff686b';
    }
    echo '<span style="color:#777;">Ping: <span style="color:' . $color . '">' . $speedtest[1] . $speedtest[2] . '</span> <i class="fa fa-arrow-down"></i> <span style="color:' . $color . '">' . $speedtest[3] . $speedtest[4] . '</span> <i class="fa fa-arrow-up"></i> <span style="color:' . $color . '">' . $speedtest[5] . $speedtest[6] . '</span></span>';
  }
?>
