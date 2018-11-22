<?php

/*	



*/

if (!isset($_SERVER['PHP_AUTH_USER'])) {
    header('WWW-Authenticate: Basic realm="cYrAx DDNS-Realm"');
    header('HTTP/1.0 401 Unauthorized');
    echo '401 ERROR (No authorization informationen provided !';
    exit;
}

$dns_server = 'localhost';
$ddns_fqdn = 'ddns.cyraxnet.de';
$subdomain = $_SERVER['PHP_AUTH_USER'];
$ttl = '86400';

$zone = $subdomain . '.' . $domain;
$cur_v4 = gethostbyname($zone);
$curip_v6_rec = dns_get_record($zone, DNS_AAAA);
$curip_v6 = $curip_v6_rec[0]['ipv6'];
$newip_v4 = $_SERVER['REMOTE_ADDR'];
$newip_v6 = $_GET['v6'];
$updv4_req = false;
$updv6_req = false;

$updatecmd_v4 = <<<EOF
    server $dns_server
    zone $zone
    update delete $zone. A
    update add $zone. $ttl A $newip_v6
    send
    exit
EOF;

$updatecmd_v6 = <<<EOF
    server $dns_server
    zone $zone
    update delete $zone. AAAA
    update add $zone. $ttl AAAA $newip_v6
    send
    exit
EOF;

if($curip_v4 != $newip_v4) {
    print "Updateing A-Record (IPv4) on Zone $zone (Old-IP: $curip_v4 -> New-IP $newip_v4)";
    $updv4_req = true;
}

if($curip_v6 != $newip_v6) { 
    print "Updateing A-Record (IPv6) on Zone $zone (Old-IP: $curip_v6 -> New-IP $newip_v6)";
    $updv6_req = true;    
}

if(!$updv4_req && !$updv6_req) {

	echo '200 Status OK ! No Update required';

  exit;
}

if($updv6_req == true) {

    exec("/usr/bin/nsupdate -k /etc/named/ddns-key.ddns.cyraxnet.de.conf $updatecmd_v6", $cmdout, $retval);
    print "IPv6-Update - Return code = " . $retval . "\n";
}

exec("/usr/bin/nsupdate -k /etc/named/ddns-key.ddns.cyraxnet.de.conf $updatecmd_v4", $cmdout, $retval);
print "IPv4-Update - Return code = " . $retval . "\n";

?>