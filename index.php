<?php
	require_once('./functions.php');
	config();
	$DB = _pdo::load();
	$login = login::load();
	if(array_keys_exist('user', 'password', $_SESSION)) $login->login_with($_SESSION);
	if(is_ajax()) {
		require_once('./ajax.php');
	}
?>
<!DOCTYPE HTML>
<!--[if lt IE 7]>      <html class="lt-ie9 lt-ie8 lt-ie7 no-js"> <![endif]-->
<!--[if IE 7]>         <html class="lt-ie9 lt-ie8 no-js"> <![endif]-->
<!--[if IE 8]>         <html class="lt-ie9 no-js"> <![endif]-->
<!--[if gt IE 8]><!--> <html itemscope itemtype="http://schema.org/WebPage" class="no-js" <?php if(!localhost()):?> manifest="manifest.appcache"<?php endif?>> <!--<![endif]-->
<!--<?=date('Y-m-d H:i:s')?>-->
<?php load('head');?>
<body lang="en" data-menu="main">
	<?php load('header', 'main', 'footer');?>
</body>
</html>
