<?php
/*
    /usr/local/www/status_gateways_json.php
    by Alexander Morris
    v0.13 20210325, added 'gatewayip' for each interface
    v0.12 20170312, fetch rates by using friendlyiface name and added more info on gateway names, interface, etc.
    v0.11 20160421, only 'clean' interface name if starts with 'gw_'
    v0.1 20150630, for pfSense 2.x

    Module retrieves pfSense 2.x Dashboard Gateway Status and Traffic
    Graphs data in JSON format.  'status' can be 'force_down', 
    'down', 'loss' (for packet-loss warning), 'delay' (for latency
    warning), or 'online'.

    WARNING: This module bypasses the normal pfSense dashboard 
    login, so it should be set with a different 'key' of your 
    choosing in the PHP code.  The default is shown below:

    http://pfSenseIP/status_gateways_json.php?key=pfsense 

    Since there is additional overhead to calculate data rates for 
    each connection ('inKbps', 'outKbps'), it can be optionally 
    requested via an additional parameter as indicated below:

    http://pfSenseIP/status_gateways_json.php?key=pfsense&rates=1 

    To install, simply change the 'SECURITY_KEY' constant in the code below, then drop 
    the status_gateways_json.php file into your pfSense '/usr/local/www' 
    directory and you are set to go! 

    THIS SOFTWARE IS PROVIDED ``AS IS'' AND ANY EXPRESS OR IMPLIED WARRANTIES,
    INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY
    AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
    AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY,
    OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
    SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
    INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
    CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
    ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
    POSSIBILITY OF SUCH DAMAGE.
*/

##|+PRIV
##|*IDENT=page-status-gateways-json
##|*NAME=Status: Gateways json
##|*DESCR=Allow access to the 'Status: Gateways' data via json.
##|*MATCH=status_gateways_json.php*
##|-PRIV

//change key, used for quick and dirty security outside of GUI login
define('SECURITY_KEY', 'pfsense');

if ($_GET['key'] !== SECURITY_KEY) {
    http_response_code(401);
    echo '401 Unauthorized';
    exit;
}

require('interfaces.inc');

$a_gateways = return_gateways_array();
$gateways_status = return_gateways_status(true);
$pfgateways = [];

function get_interface_rates($iface, &$inKbps, &$outKbps)
{
    $realif = get_real_interface($iface);
    $ifinfo1 = pfSense_get_interface_stats($realif);
    $tmrStart = microtime(true);
    usleep(100000);
    $ifinfo2 = pfSense_get_interface_stats($realif);
    $totTime = microtime(true) - $tmrStart;
    $inKbps = round(abs($ifinfo2['inbytes'] - $ifinfo1['inbytes']) * (1 / $totTime) / 1000 * 8, 2);
    $outKbps = round(abs($ifinfo2['outbytes'] - $ifinfo1['outbytes']) * (1 / $totTime) / 1000 * 8, 2);
}

foreach ($gateways_status as $a_gateway) {
    $realif = $a_gateways[$a_gateway['name']]['interface'];
    $iface = $a_gateways[$a_gateway['name']]['friendlyiface'];
    $gateway = [];
    $gateway['interface'] = $realif;
    $gateway['friendlyiface'] = $iface;
    $gateway['name'] = $a_gateway['name'];
    $gateway['status'] = $a_gateway['status'];
    $gateway['monitorip'] = $a_gateway['monitorip'];
    $gateway['sourceip'] = $a_gateway['srcip'];
    $gateway['gatewayip'] = $a_gateways[$a_gateway['name']]['gateway'];
    $gateway['delay'] = sprintf("%02.1f", preg_replace("/[^0-9.]/", '', $a_gateway['delay'])) . 'ms';
    $gateway['loss'] = sprintf("%02.1f", preg_replace("/[^0-9.]/", '', $a_gateway['loss'])) . '%';
    if ($_GET['rates'] === '1') {
        get_interface_rates($iface, $inKbps, $outKbps);
        $gateway['inKbps'] = $inKbps;
        $gateway['outKbps'] = $outKbps;
    }
    $pfgateways[] = $gateway;
}

header('Content-Type: application/json');
echo json_encode($pfgateways);
