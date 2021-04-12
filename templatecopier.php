<?php include_once 'includes/functions.php'?>
<?php

function copyTemplates($appId, $copyFromOrgId, $mysqli){
    $sql = "insert into template (userID, app, template_name, html_text) select userID,".$appId.", template_name, html_text from template where app = ".$copyFromOrgId."";
    $r = mysqli_query($mysqli, $sql);
}
?>
