<?php
	$resp = \shgysk8zer0\core\json_response::load();
	$page = \shgysk8zer0\core\pages::load();
	$head = $DB->fetch_array("
		SELECT `value` FROM `head`
		WHERE `name` = 'title'
	", 0);

	$resp->remove(
		'main > :not(aside)'
	)->prepend(
		'main',
		$page->content
	)->scrollTo(
		'main :first-child'
	);

	$resp->attributes(
		'meta[name=description], meta[itemprop=description], meta[property="og:description"]',
		'content',
		$page->description
	)->attributes(
		'meta[name=keywords], meta[itemprop=keywords]',
		'content',
		$page->keywords
	)->attributes(
		'link[rel=canonical]',
		'href',
		$_SERVER['REQUEST_SCHEME'] . '://' . preg_replace('/^www\./', null, $_SERVER['SERVER_NAME']) . $_SERVER['REDIRECT_URL']
	)->attributes(
		'meta[itemprop=url], meta[property="og:url"]',
		'content',
		$_SERVER['REQUEST_SCHEME'] . '://' . preg_replace('/^www\./', null, $_SERVER['SERVER_NAME']) . $_SERVER['REDIRECT_URL']
	)->attributes(
		'meta[itemprop=name], meta[property="og:title"]',
		'content',
		"{$page->title} | {$head->value}"
	)->text(
		'head > title',
		"{$page->title} | {$head->value}"
	);
?>
