# zabbix-nagios-view
# Nagios View for Zabbix

![Zabbix](https://img.shields.io/badge/Zabbix-7.2+-green.svg)
![License](https://img.shields.io/badge/License-GPLv2-blue.svg)

A lightweight Zabbix module that displays active problems in a Nagios-style interface with auto-refresh capabilities.

## ✨ Features

✔ **Real-time problem dashboard** with severity coloring  
✔ **Auto-refreshing view** (configurable interval)  
✔ **Nagios-inspired UI** for quick status assessment  
✔ **Group filtering** via `{$GROUPIDS}` global macro  
✔ **Direct links** to host dashboards and problem details  
✔ **Mobile-friendly** responsive design  

## 🖥 Screenshot
<img width="266" height="280" alt="image" src="https://github.com/user-attachments/assets/afff2947-cf20-4b91-beae-60086b09741e" />

<img width="1574" height="434" alt="image" src="https://github.com/user-attachments/assets/71e690da-ef3e-4038-a927-9c2ae7da2064" />


## 🚀 Installation

1. Clone to Zabbix modules directory:
```bash
git clone https://github.com/maavcrusoe/zabbix-nagios-view /usr/share/zabbix/modules/NagiosView
```

2. Set permissions:
```bash
chown -R zabbix:zabbix /usr/share/zabbix/modules/NagiosView
chmod -R 755 /usr/share/zabbix/modules/NagiosView
```
3. Enable in Zabbix:
→ Go to Administration → Modules
→ Find Nagios View and click Enable

## ⚙ Configuration

Edit ```modules/NagiosView/config.json:```

```
{
  "serverUrl": "http://your-zabbix-frontend",
  "apiUrl": "http://your-zabbix-frontend/api_jsonrpc.php",
  "refreshIntervalSeconds": 60,
  "username": "Admin",
  "password": "zabbix"
}
```
## 📂 File Structure
```bash
NagiosView/
├── Actions/
│   └── NagiosAction.php
├── assets/
│   ├── scripts.js
│   └── styles.css
├── config.json
├── Module.php
├── view.php
└── manifest.json
```

## 🌟 Use Case Example
<img width="714" height="35" alt="image" src="https://github.com/user-attachments/assets/e0854d91-92ac-4ee1-ae0a-22b1cb571919" />

```bash
# Monitor specific host groups by setting global macro:
# {$GROUPIDS} = "123,456" (groupids from Zabbix)
# Module will automatically filter and display only these groups
```

