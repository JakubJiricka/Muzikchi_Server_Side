8
9
<?php
$link = mysql_connect('cheapauto.dealerclick.info', 'cheapauto_db', 'Dealerclick1000');
if (!$link) 
{
    die('Could not connect: ' . mysql_error());
}
echo 'Connected successfully';
mysql_close($link);
?>