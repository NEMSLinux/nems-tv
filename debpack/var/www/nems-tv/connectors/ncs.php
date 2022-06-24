<?php

  // check authentication
  include('../../auth.php');
  if (!auth()) {
      header('location:/dashboard/logout.php');
      exit();
  }

  include_once('../../inc/ncs_session.php');

  include_once('../../inc/functions.php');

  // Refresh the current stats
  refreshStats();

  $thisserver = $_SESSION['servers']['parent'];
  // Note we're storing this in a variable, not the session. Session only holds the encrypted data.
  $thisserver['state']['decrypted'] = decryptStats($thisserver['state']['raw']);
  $livestatus = decryptStats($thisserver['livestatus']);
function _print_duration($start_time, $end_time)
{
                $duration = $end_time - $start_time;
                $days = $duration / 86400;
                $hours = ($duration % 86400) / 3600;
                $minutes = ($duration % 3600) / 60;
                $seconds = ($duration % 60);
                $retval = sprintf("%dd %dh %dm %ds", $days, $hours, $minutes, $seconds);
		return($retval);
}

function sort_by_state($a, $b) {
   if ( $a[2] == $b[2] ) {
      if ( $a[0] > $b[0] ) {
         return 1;
      }
      else if ( $a[0] < $b[0] ) {
         return -1;
      }
      else {
         return 0;
      }
   }
   else if ( $a[2] > $b[2] ) {
      return -1;
   }
   else {
      return 1;
   }
}
?>

<div class="dash_unhandled_hosts hosts dash">
    <h2>Unhandled host problems</h2>
    <div class="dash_wrapper">
        <table class="dash_table">

            <?php
            $save = "";
            $output = "";
            while ( list(, $row) = each($livestatus->unhandled->hosts) ) {
                $output .=  "<tr class=\"critical\"><td>" . $thisserver['state']['decrypted']->alias . "</td><td>".$row[0]."</td><td>".$row[1]."</td></tr>";
                $save .= $row[0];
            }
            if($save):
            ?>
            <tr class="dash_table_head">
                <th>Server Alias</th>
                <th>Host Name</th>
                <th>Host Alias</th>
            </tr>
            <?php print $output; ?>
            <?php
            else:
                print "<tr class=\"ok\"><td>No hosts down or unacknowledged</td></tr>";
            endif;
            ?>
        </table>
    </div>
</div>

<div class="dash_tactical_overview tactical_overview hosts dash">
    <h2>Tactical overview</h2>
    <div class="dash_wrapper">
        <table class="dash_table">
            <tr class="dash_table_head">
                <th>Type</th>
                <th>Totals</th>
                <th>%</th>
            </tr>

            <tr class="ok total_hosts_up">
                <td>Hosts up</td>
                <td><?php print $livestatus->hosts->up ?>/<?php print $livestatus->hosts->total ?></td>
                <td><?php print $livestatus->hosts->up_pct ?></td>
            </tr>
	    <?php if ($livestatus->hosts->down > 0) {
		print "<tr class=\"critical total_hosts_down\">";
	       } else {
		print "<tr class=\"ok total_hosts_down\">";
	       };
	    ?>
                <td>Hosts down</td>
                <td><?php print $livestatus->hosts->down ?>/<?php print $livestatus->hosts->total ?></td>
                <td><?php print $livestatus->hosts->down_pct ?></td>
            </tr>
	    <?php if ($livestatus->hosts->unreach > 0) {
		print "<tr class=\"critical total_hosts_unreach\">";
	       } else {
		print "<tr class=\"ok total_hosts_unreach\">";
	       };
	    ?>
                <td>Hosts unreachable</td>
                <td><?php print $livestatus->hosts->unreach ?>/<?php print $livestatus->hosts->total ?></td>
                <td><?php print $livestatus->hosts->unreach_pct ?></td>
            </tr>
            <tr class="ok total_services_ok">
                <td>Services OK</td>
                <td><?php print $livestatus->services->ok ?>/<?php print $livestatus->services->total ?></td>
                <td><?php print $livestatus->services->ok_pct ?></td>
            </tr>
	    <?php if ($livestatus->services->critical > 0) {
		print "<tr class=\"critical total_services_critical\">";
	       } else {
		print "<tr class=\"ok total_services_critical\">";
	       };
	    ?>
                <td>Services critical</td>
                <td><?php print $livestatus->services->critical ?>/<?php print $livestatus->services->total ?></td>
                <td><?php print $livestatus->services->critical_pct ?></td>
            </tr>
	    <?php if ($livestatus->services->warning > 0) {
		print "<tr class=\"warning total_services_warning\">";
	       } else {
		print "<tr class=\"ok total_services_warning\">";
	       };
	    ?>
                <td>Services warning</td>
                <td><?php print $livestatus->services->warning ?>/<?php print $livestatus->services->total ?></td>
                <td><?php print $livestatus->services->warning_pct ?></td>
            </tr>
	    <?php if ($livestatus->services->unknown > 0) {
		print "<tr class=\"unknown total_services_unknown\">";
	       } else {
		print "<tr class=\"ok total_services_unknown\">";
	       };
	    ?>
                <td>Services unknown</td>
                <td><?php print $livestatus->services->unknown ?>/<?php print $livestatus->services->total ?></td>
                <td><?php print $livestatus->services->unknown_pct ?></td>
            </tr>
        </table>
    </div>
</div>
<div class="clear"></div>
<div class="dash_unhandled_service_problems hosts dash">
    <h2>Unhandled service problems</h2>
    <div class="dash_wrapper">
        <table class="dash_table">
            <?php
            $save = "";
            $output = "";

            while ( list(, $row) = each($livestatus->unhandled->services) ) {
                if ($row[2] == 2) {
                    $class = "critical";
                } elseif ($row[2] == 1) {
                    $class = "warning";
                } elseif ($row[2] == 3) {
                    $class = "unknown";
                }

		$duration = _print_duration($row[4], time());
		$date = date("Y-m-d H:i:s", $row[5]);
		$output .= "<tr class=\"".$class."\"><td>" . $thisserver['state']['decrypted']->alias . "</td><td>".$row[0]."</td><td>".$row[1]."</td>";
		$output .= "<td>".$row[3]."</td>";
		$output .= "<td class=\"date date_statechange\">".$duration."</td>";
		$output .= "<td class=\"date date_lastcheck\">".$date."</td></tr>\n";
		$save .= $row[0];
	    };

            if ($save):
            ?>
            <tr class="dash_table_head">
                <th>Server</th>
                <th>
                    Host
                </th>
                <th>
                    Service
                </th>
                <th>
                    Output
                </th>
                <th>
                    Duration
                </th>
                <th>
                    Last check
                </th>
            </tr>
            <?php print $output; ?>
            <?php
            else:
                print "<tr class=\"ok\"><td>No services in a problem state or unacknowledged</td></tr>";
            endif;
            ?>
        </table>
    </div>
</div>
