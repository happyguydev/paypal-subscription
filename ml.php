<?php
$to = "ansarisam@gmail.com";
$subject = "My subject";
$txt = "Hello world!";
$headers = "From: ansarishamshad@gmail.com" . "\r\n" ;

mail($to,$subject,$txt,$headers);
?>
