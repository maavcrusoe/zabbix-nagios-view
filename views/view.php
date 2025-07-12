<?php
/** @var array $data */

$hosts = $data['hosts'];
$groupNames = $data['groupNames'];
$serverUrl = $data['serverUrl'];
$refreshInterval = $data['refreshInterval'];

echo '<link rel="stylesheet" href="modules/NagiosView/assets/styles.css">';
echo '<script src="modules/NagiosView/assets/scripts.js"></script>';

// Pasamos refresh interval como data-attribute
echo '<body data-refresh-interval="' . (int)$refreshInterval . '">';

echo '<h1 class="page-title">Host Problems</h1>';
echo '<p class="refresh-info">Refresh interval: <span id="refresh-timer">' . htmlspecialchars($refreshInterval) . '</span>s</p>';

if (empty($hosts)) {
    echo '<p class="no-hosts">No se encontraron hosts.</p>';
    return;
}

$severityLabels = [
    0 => 'Not classified',
    1 => 'Information',
    2 => 'Warning',
    3 => 'Average',
    4 => 'High',
    5 => 'Disaster'
];

// Abrimos la tabla UNA SOLA VEZ
echo '<table class="dashboard-table">';
echo '<thead>';
echo '<tr>';
echo '<th class="host-header">Host</th>';
echo '<th>Service</th>';
echo '<th>Severity</th>';
echo '<th>Last Check</th>';
echo '<th>Duration</th>';
echo '<th>Item Value</th>';
echo '</tr>';
echo '</thead>';

// Para cada host, imprimimos un bloque tbody separado
foreach ($hosts as $host) {
    $groups = array_map(fn($g) => $groupNames[$g['groupid']] ?? $g['name'], $host['groups']);
    $groupStr = implode(', ', $groups);

    if ($host['hasGraphs']) {
        $link = $serverUrl . '/zabbix.php?action=host.dashboard.view&hostid=' . $host['hostid'];
    } else {
        $link = $serverUrl . '/zabbix.php?action=latest.view&filter_hostids[]=' . $host['hostid'];
    }

    echo '<tbody>';

    if (empty($host['triggers'])) {
        echo '<tr class="row-no-problem">';
        echo '<td class="host-cell" rowspan="1">';
        echo '<a href="' . htmlspecialchars($link) . '" target="_blank" class="host-link">' . htmlspecialchars($host['name']) . '</a>';
        echo '<div class="group-info">' . htmlspecialchars($groupStr) . '</div>';
        echo '</td>';
        echo '<td colspan="5" class="ok-message">No active problems</td>';
        echo '</tr>';
    } else {
        $rowspan = count($host['triggers']);
        $firstRow = true;

        foreach ($host['triggers'] as $trigger) {
            $severity = $trigger['priority'];
            $severityLabel = $severityLabels[$severity];
            $severityClass = 'severity-' . strtolower(str_replace(' ', '', $severityLabel));

            $description = $trigger['description'];
            $lastChangeTs = $trigger['lastchange'];
            $lastChange = $lastChangeTs ? date('Y-m-d H:i:s', $lastChangeTs) : '-';

            $duration = $lastChangeTs > 0 ? gmdate('H:i:s', time() - $lastChangeTs) : '-';

            $attempt = '-';
            if (!empty($trigger['items'])) {
                $item = $trigger['items'][0];
                $attempt = $item['lastvalue'] . ' ' . ($item['units'] ?? '');
            }

            $problemUrl = $serverUrl . '/zabbix.php?action=problem.view&filter_triggerids[]=' . $trigger['triggerid'];

            echo '<tr>';

            if ($firstRow) {
                echo '<td class="host-cell" rowspan="' . $rowspan . '">';
                echo '<a href="' . htmlspecialchars($link) . '" target="_blank" class="host-link">' . htmlspecialchars($host['name']) . '</a>';
                //echo '<div class="group-info">' . htmlspecialchars($groupStr) . '</div>';
                echo '</td>';
                $firstRow = false;
            }

            echo '<td><a href="' . htmlspecialchars($problemUrl) . '" target="_blank" class="service-link">' . htmlspecialchars($description) . '</a></td>';
            echo '<td class="' . $severityClass . '">' . htmlspecialchars($severityLabel) . '</td>';
            echo '<td>' . htmlspecialchars($lastChange) . '</td>';
            echo '<td>' . htmlspecialchars($duration) . '</td>';
            echo '<td>' . htmlspecialchars($attempt) . '</td>';
            echo '</tr>';
        }
    }

    echo '</tbody>';
}

echo '</table>';
