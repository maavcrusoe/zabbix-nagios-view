<?php
namespace Modules\NagiosView\Actions;

use CController;
use CControllerResponseData;

class NagiosAction extends CController {

    public function init(): void {
        $this->disableCsrfValidation();
    }

    protected function checkInput(): bool {
        return true;
    }

    protected function checkPermissions(): bool {
        return true;
    }

    protected function doAction(): void {
        // Load config
        $configPath = dirname(__DIR__) . '/config.json';
        $config = json_decode(file_get_contents($configPath), true);

        $apiUrl = $config['apiUrl'];
        $username = $config['username'];
        $password = $config['password'];
        $serverUrl = $config['serverUrl'];
        $refreshInterval = $config['refreshIntervalSeconds'] ?? 60;

        $apiToken = $this->getAuthToken($apiUrl, $username, $password);

        if (!$apiToken) {
            echo 'No se pudo obtener token de autenticación.';
            return;
        }

        // Get macro {$GROUPIDS}
        $macro = $this->zabbixApiRequest($apiUrl, $apiToken, 'usermacro.get', [
            'globalmacro' => true,
            'output' => ['macro', 'value'],
            'filter' => ['macro' => '{$GROUPIDS}']
        ]);

        $groupids = [];
        if (!empty($macro) && !empty($macro[0]['value'])) {
            $groupids = explode(',', str_replace(' ', '', $macro[0]['value']));
        }

        if (empty($groupids)) {
            echo 'No se encontraron groupids.';
            return;
        }

        // Get host groups
        $groups = $this->zabbixApiRequest($apiUrl, $apiToken, 'hostgroup.get', [
            'output' => ['groupid', 'name'],
            'groupids' => $groupids
        ]);
        $groupNames = [];
        foreach ($groups as $g) {
            $groupNames[$g['groupid']] = $g['name'];
        }

        // Get hosts
        $hosts = $this->zabbixApiRequest($apiUrl, $apiToken, 'host.get', [
            'output' => ['hostid', 'name'],
            'selectHostGroups' => ['groupid', 'name'],
            'groupids' => $groupids,
            'filter' => ['status' => 0]   // solo activos
        ]);

        $result = [];

        foreach ($hosts as $host) {
            // Get triggers only ACTIVE
            $triggers = $this->zabbixApiRequest($apiUrl, $apiToken, 'trigger.get', [
                'hostids' => $host['hostid'],
                'output' => ['triggerid', 'description', 'priority', 'value', 'lastchange'],
                'expandDescription' => true,
                'selectItems' => ['itemid', 'name', 'lastclock', 'lastvalue', 'units'],
                'filter' => [
                    'status' => 0,
                    'value' => 1 // only problems
                ]
            ]);

            if (empty($triggers)) {
                // skip hosts without problems
                continue;
            }

            // Ordenar triggers por lastchange descendente
            usort($triggers, function ($a, $b) {
                return $b['lastchange'] <=> $a['lastchange'];
            });

            // Check graphs
            $graphs = $this->zabbixApiRequest($apiUrl, $apiToken, 'graph.get', [
                'output' => ['graphid', 'name'],
                'hostids' => $host['hostid']
            ]);

            $result[] = [
                'hostid' => $host['hostid'],
                'name' => $host['name'],
                'groups' => $host['hostgroups'],
                'triggers' => $triggers,
                'hasGraphs' => !empty($graphs)
            ];
        }

        $response = new CControllerResponseData([
            'hosts' => $result,
            'groupNames' => $groupNames,
            'serverUrl' => $serverUrl,
            'refreshInterval' => $refreshInterval
        ]);

        $this->setResponse($response);
    }

    private function getAuthToken($apiUrl, $username, $password) {
        $request = [
            'jsonrpc' => '2.0',
            'method' => 'user.login',
            'params' => [
                'username' => $username,
                'password' => $password
            ],
            'id' => 1
        ];

        $response = $this->makeApiRequest($apiUrl, $request);
        return $response['result'] ?? null;
    }

    private function zabbixApiRequest($apiUrl, $apiToken, $method, $params) {
        $request = [
            'jsonrpc' => '2.0',
            'method' => $method,
            'params' => $params,
            'id' => 1
        ];

        $result = $this->makeApiRequest($apiUrl, $request, $apiToken);
        return $result['result'] ?? [];
    }

    private function makeApiRequest($apiUrl, $request, $apiToken = null) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $headers = ['Content-Type: application/json'];
        if ($apiToken) {
            $headers[] = 'Authorization: Bearer ' . $apiToken;
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request));
        $result = curl_exec($ch);
        curl_close($ch);
        return json_decode($result, true);
    }
}
