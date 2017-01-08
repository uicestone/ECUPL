<?php
require_once './source/class/class_core.php';
require_once './source/function/function_forum.php';
require_once './source/function/function_core.php';
$discuz = & discuz_core::instance();
$discuz->init();
loadforum();
loadcache('plugin');

include template('renren:rr_auth_feed');
?>
<script>
setTimeout("oauth()",1000);
function oauth()
{
window.opener=null;      
window.open('','_self');      
window.close(); 
}
</script>
