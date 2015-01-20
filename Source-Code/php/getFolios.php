<?php
// The url to the fulfillment feed.
// Since it is in a different domain than the admin the feed needs to be proxied so it is in the same domain.
$feed = "http://edge.adobe-dcfs.com/ddp/issueServer/issues?targetDimension=all&accountId=" . $_GET['accountId'];

header('Content-Type: text/xml');
header('Access-Control-Allow-Origin: *');
$xml = file_get_contents($feed);
echo $xml;	
?>