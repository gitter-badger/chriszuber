<?php
	$pages = pages::load();
?>
<main role="main" itemprop="mainContentofPage" itemscope itemtype="http://schema.org/Blog" <?=($login->logged_in) ? ' contextmenu="admin_menu"' : ''?>>
	<?php load('forms/login','sidebar')?>
	<?=$pages->content?>
</main>
