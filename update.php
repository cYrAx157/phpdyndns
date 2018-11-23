<?php

/*	



*/

if (!isset($_SERVER['PHP_AUTH_USER'])) {
    header('WWW-Authenticate: Basic realm="cYrAx DDNS-Realm"');
    header('HTTP/1.0 401 Unauthorized');
    echo '401 ERROR (No authorization informationen provided !';
    exit;
}

$dns_server = '127.0.0.1';
$ddns_fqdn = 'ddns.cyraxnet.de';
$subdomain = $_SERVER['PHP_AUTH_USER'];
$ttl = '86400';

$zone = $subdomain . '.' . $ddns_fqdn;
$curip_v4 = gethostbyname($zone);
$curip_v6_rec = dns_get_record($zone, DNS_AAAA);
$curip_v6 = $curip_v6_rec[0]['ipv6'];
$newip_v4 = $_SERVER['REMOTE_ADDR'];
$newip_v6 = $_GET['v6'];
$updv4_req = false;
$updv6_req = false;

if($curip_v4 == $newip_v4 && $curip_v6 == $newip_v6) {

	echo '200 Status OK ! No Update required';

  exit;
}

$nsupdate = popen("/usr/bin/nsupdate -k /etc/named/ddns-key.ddns.cyraxnet.de.conf", "w");
fwrite($nsupdate, "server ".$dns_server."\n");
fwrite($nsupdate, "zone ".$zone."\n");  

    if($curip_v4 != $newip_v4) { 
        print "Updating A-Record (IPv4) on Zone $zone (Old-IP: $curip_v4 -> New-IP $newip_v4)";
        fwrite($nsupdate, "update delete ".$zone.".A\n");
        fwrite($nsupdate, "update add ".$zone.". ".$ttl." A ".$newip_v4."\n");   
    }
    if($curip_v6 != $newip_v6) { 
        print "Updating AAAA-Record (IPv6) on Zone $zone (Old-IP: $curip_v6 -> New-IP $newip_v6)";
        fwrite($nsupdate, "update delete ".$zone.".AAAA\n");
        fwrite($nsupdate, "update add ".$zone.". ".$ttl." AAAAA ".$newip_v6."\n");   
    }
    
fwrite($nsupdate, "send\n");
fwrite($nsupdate, "quit\n");
echo 'Update done';
pclose($nsupdate);

// print "IPv4-Update - Return code = " . $retval . "\n";

?>