# pfsense-status-gateways-json

Module retrieves pfSense 2.x Dashboard Gateway Status and Network Traffic Graphs data in JSON format.

"status" can be "force_down", "down", "loss" (for packet-loss warning), "delay" (for latency
warning), or "okay" for online.

**WARNING**: This module bypasses the normal pfSense dashboard login, so it should be set with a different "SECURITY_KEY" constant of your choosing in the PHP code. The default is shown below:

`
http://pfSenseIP/status_gateways_json.php?key=pfsense 
`

Since there is additional overhead to calculate data rates for
each connection ("inKbps", "outKbps"), it can be optionally
requested via an additional parameter as indicated below:

`
http://pfSenseIP/status_gateways_json.php?key=pfsense&rates=1 
`

To install, simply change the "SECURITY_KEY" constant in the code below, then drop
the status_gateways_json.php file into your pfSense "**/usr/local/www**"
directory and you are set to go! 

JSON output (&rates=1 version) should look something like this:

```
[
    {
        "interface": "em1",
        "friendlyiface": "wan",
        "name": "GW_WAN",
        "status": "online",
        "monitorip": "x.x.x.x",
        "sourceip": "x.x.x.x",
        "gatewayip": "x.x.x.x",
        "delay": "10.3ms",
        "loss": "0%",
        "inKbps": 132.32,
        "outKbps": 307.77
    },
    {
        "interface": "em2",
        "friendlyiface": "opt1",
        "name": "GW_OPT1",
        "status": "online",
        "monitorip": "x.x.x.x",
        "sourceip": "x.x.x.x",
        "gatewayip": "x.x.x.x",
        "delay": "25.5ms",
        "loss": "0%",
        "inKbps": 1690.55,
        "outKbps": 197.42
    }
]
```
