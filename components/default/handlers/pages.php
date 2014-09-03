<?php
	$resp = json_response::load();
	$page = pages::load();
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
		'meta[name=description], meta[itemprop=description]',
		'content',
		$page->description
	)->attributes(
		'meta[name=keywords], meta[itemprop=keywords]',
		'content',
		$page->keywords
	)->attributes(
		'link[rel=canonical]',
		'href',
		$_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'] . $_SERVER['REDIRECT_URL']
	)->text(
		'head > title',
		"{$page->title} | {$head->value}"
	);
?>
