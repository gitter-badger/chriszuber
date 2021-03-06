/*Cannot rely on $(window).load() to work, so use this instead*/
window.addEventListener('load', function() {
	'use strict';
	var html = $('html'),
		$body = $('body'),
		$head = $('head');
		cache = new cache();
		document.documentElement.classList.swap('no-js', 'js');
	['svg', 'audio', 'video', 'picture', 'canvas', 'menuitem', 'details',
	'dialog', 'dataset', 'HTMLimports', 'classList', 'connectivity',
	'visibility', 'notifications', 'ApplicationCache', 'indexedDB',
	'localStorage', 'sessionStorage', 'CSSgradients', 'transitions',
	'animations', 'CSSvars', 'CSSsupports', 'CSSmatches', 'querySelectorAll',
	'workers', 'promises', 'ajax', 'FormData'].forEach(function(feat) {
		document.documentElement.classList.pick(feat, 'no-' + feat, supports(feat));
	});
	document.documentElement.classList.pick('offline', 'online', (supports('connectivity') && !navigator.onLine));
	setTimeout(
		function() {
			$body.results.bootstrap();
		}, 100
	);
	$body.watch({
		childList: function() {
			this.addedNodes.bootstrap();
		},
		attributes: function() {
			switch (this.attributeName) {
				case 'contextmenu':
					var menu = this.target.getAttribute('contextmenu');
					if (this.oldValue !== '') {
						$('menu#' + this.oldValue).delete();
					}
					if (menu && menu !== '') {
						if (!$('menu#' + menu).found) {
							ajax({
								url: document.baseURI,
								request: 'load_menu=' + menu.replace(/\_menu$/, '')
							}).then(
								handleJSON
							).catch(function(err) {
								$('body > progress').delete();
								console.error(err);
							});
						}
					}
					break;

				case 'open':
					if (
						this.target.hasAttribute('open')
						&& (this.target.offsetTop + this.target.offsetHeight < window.scrollY)
					) {
						this.target.scrollIntoView();
					}
					break;

				case 'data-import':
					if (this.target.dataset.has('import')) {
						this.target.HTMLimport();
					}
					break;

				case 'data-request':
					if (this.oldValue !== '') {
						this.target.addEventListener('click', function() {
							if (!this.dataset.has('confirm') || confirm(this.dataset.confirm)) {
								ajax({
									url: this.dataset.url || document.baseURI,
									request: (this.dataset.has('prompt'))
										? this.dataset.request + '&prompt_value=' + encodeURIComponent(prompt(this.dataset.prompt))
										: this.dataset.request
								}).then(
									handleJSON
								).catch(function(err) {
									$('body > progress').delete();
									console.error(err);
								});
							}
						});
					}
					break;

				case 'data-dropzone':
					document.querySelector(this.target.dataset.dropzone).DnD(this.target);
					break;

				default:
					console.error('Unhandled attribute in watch', this);
			}
		}
	}, [
		'subtree',
		'attributeOldValue'
	], [
		'contextmenu',
		'list',
		'open',
		'data-request',
		'data-dropzone',
		'data-import'
	]);
	$(window).networkChange(function() {
		$('html').toggleClass('online', navigator.onLine).toggleClass('offline', !navigator.onLine);
	}).online(function() {
		$('fieldset').each(function(fieldset) {
			fieldset.removeAttribute('disabled');
		});
		notify({
			title: 'Network:',
			body: 'online',
			icon: 'images/icons/network-server.png'
		});
	}).offline(function() {
		$('fieldset').each(function(fieldset) {
			fieldset.disabled = true;
		});
		notify({
			title: 'Network:',
			body: 'offline',
			icon: 'images/icons/network-server.png'
		});
	});
	if (!sessionStorage.hasOwnProperty('nonce')) {
		ajax({
			request: 'request=nonce'
		}).then(
			handleJSON
		).catch(function(err) {
			$('body > progress').delete();
			console.error(err);
		});
	}
});
NodeList.prototype.bootstrap = function() {
	'use strict';
	this.forEach(function(node) {
		if (node.nodeType !== 1) {
			return this;
		}
		if (!supports('details')) {
			node.query('details > summary').forEach(function(details) {
				details.addEventListener('click', function() {
					if (this.parentElement.hasAttribute('open')) {
						this.parentElement.removeAttribute('open');
					} else {
						this.parentElement.setAttribute('open', '');
					}
				});
			});
		}
		if (supports('menuitem')) {
			node.query('[contextmenu]').forEach(function(el) {
				var menu = el.getAttribute('contextmenu');
				if (menu && menu !== '') {
					if (!$('menu#' + menu).found) {
						ajax({
							url: document.baseURI,
							request: 'load_menu=' + menu.replace(/\_menu$/, '')
						}).then(
							handleJSON
						).catch(function(err) {
							$('body > progress').delete();
							console.error(err);
						});
					}
				}
			});
		}
		if (supports('datalist')) {
			node.query('[list]').forEach(function(list) {
				if (!$('#' + list.getAttribute('list')).found) {
					ajax({
						request: 'datalist=' + list.getAttribute('list'),
						type: 'POST'
					}).then(
						handleJSON
					).catch(function(err) {
						$('body > progress').delete();
						console.error(err);
					});
				}
			});
		}
		if (!supports('picture')) {
			node.query('picture').forEach(function(picture) {
				if ('matchMedia' in window) {
					var sources = picture.querySelectorAll('source[media][srcset]');
					for (var n = 0; n < sources.length; n++) {
						if (matchMedia(sources[n].getAttribute('media')).matches) {
							picture.getElementsByTagName('img')[0].src = sources[n].getAttribute('srcset');
							break;
						}
					}
				} else {
					picture.getElementsByTagName('img')[0].src = picture.querySelector('source[media][srcset]').getAttribute('srcset');
				}
			});
		}
		node.query('[autofocus]').forEach(function(input) {
			input.focus();
		});
		node.query('a[href]:not([target="_blank"]):not([download]):not([href*="\#"])').filter(
			isInternalLink
		).forEach(function(a) {
			a.addEventListener('click', function(event) {
				event.preventDefault();
				if (typeof ga === 'function') {
					ga('send', 'pageview', this.href);
				}ajax({
					url: this.href,
					type: 'GET',
					history: this.href
				}).then(
					handleJSON
				).catch(function(err) {
					$('body > progress').delete();
					console.error(err);
				});
			});
		});
		node.query('form[name]').forEach(function(form) {
			form.addEventListener('submit', function(event) {
				event.preventDefault();
				if (!this.dataset.has('confirm') || confirm(this.dataset.confirm)) {
					ajax({
						url: this.action || document.baseURI,
						type: this.method || 'POST',
						contentType: this.enctype,
						form: this
					}).then(
						handleJSON
					).catch(function(err) {
						$('body > progress').delete();
						console.error(err);
					});
				}
			});
			if (form.name === 'new_post') {
				var retain = setInterval(function() {
					ajax({
						url: document.baseURI,
						request: 'action=keep-alive'
					}).then(
						handleJSON
					).catch(function(err) {
						$('body > progress').delete();
						console.error(err);
					});
				}, 60000);
				form.addEventListener('submit', function() {
					clearInterval(retain);
				});
			}
		});
		node.query('[data-show]').forEach(function(el) {
			el.addEventListener('click', function() {
				document.querySelector(this.dataset.show).show();
			});
		});
		node.query('[data-show-modal]').forEach(function(el) {
			el.addEventListener('click', function() {
				document.querySelector(this.dataset.showModal).showModal();
			});
		});
		node.query('[data-scroll-to]').forEach(function(el) {
			el.addEventListener('click', function() {
				document.querySelector(this.dataset.scrollTo).scrollIntoView();
			});
		});
		node.query('[data-import]').forEach(function(el) {
			el.HTMLimport();
		});
		node.query('[data-close]').forEach(function(el) {
			el.addEventListener('click', function() {
				document.querySelector(this.dataset.close).close();
			});
		});
		node.query('fieldset button[type=button].toggle').forEach(function(toggle) {
			toggle.addEventListener('click', function() {
				this.nearest('fieldset').querySelectorAll('input[type="checkbox"]').forEach(function(checkbox) {
					checkbox.checked = !checkbox.checked;
				});
			});
		});
		node.query('[data-must-match]').forEach(function(match) {
			match.pattern = new RegExp(document.querySelector('[name="' + match.dataset.mustMatch + '"]').value).escape();
			document.querySelector('[name="' + match.dataset.mustMatch + '"]').addEventListener('change', function() {
				document.querySelector('[data-must-match="' + this.name + '"]').pattern = new RegExp(this.value).escape();
			});
		});
		node.query('[data-dropzone]') .forEach(function (el) {
			document.querySelector(el.dataset.dropzone).DnD(el);
		});
		node.query('input[data-equal-input]').forEach(function(input) {
			input.addEventListener('input', function() {
				document.querySelectorAll('input[data-equal-input="' + this.dataset.equalInput + '"]').forEach(function(other) {
					if (other !== input) {
						other.value = input.value;
					}
				});
			});
		});
		node.query('menu[type="context"]').forEach(WYSIWYG);
		node.query('[data-request]').forEach(function(el) {
			el.addEventListener('click', function(event) {
				event.preventDefault();
				if (!this.dataset.has('confirm') || confirm(this.dataset.confirm)) {
					ajax({
						url: this.dataset.url || document.baseURI,
						request: (this.dataset.has('prompt'))
							? this.dataset.request + '&prompt_value=' + encodeURIComponent(prompt(this.dataset.prompt))
							: this.dataset.request,
						history: this.dataset.history || null
					}).then(
						handleJSON
					).catch(function(err) {
						$('body > progress').delete();
						console.error(err);
					});
				}
			});
		});
		node.query('[data-dropzone]') .forEach(function (finput) {
			document.querySelector(finput.dataset.dropzone).DnD(finput);
		});
		node.query('[data-fullscreen]').forEach(function(el) {
			el.addEventListener('click', function(event) {
				if (fullScreen) {
					document.cancelFullScreen();
				} else {
					document.querySelector(this.dataset.fullscreen).requestFullScreen();
				}
			});
		});
		node.query('[data-delete]').forEach(function(el) {
			el.addEventListener('click', function() {
				document.querySelectorAll(this.dataset.delete).forEach(function(el) {
					el.remove();
				});
			});
		});
		node.query('menuitem[label="Edit Post"]').forEach(function(el) {
			el.addEventListener('click', function() {
				var form = document.createElement('form'),
					article = document.querySelector('article'),
					header = article.querySelector('header'),
					title = header.querySelector('[itemprop="headline"]'),
					keywords = header.querySelector('[itemprop="keywords"]'),
					content = article.querySelector('[itemprop="text"]'),
					submit = document.createElement('button'),
					fieldset = document.createElement('fieldset'),
					legend = document.createElement('legend'),
					oldTitle = document.createElement('input'),
					description = document.createElement('textarea'),
					footer = article.querySelector('footer');
				footer.remove();
				form.name = 'edit_post';
				form.method = 'POST';
				form.action = document.baseURI;
				form.atsetAttributetr('contextmenu', 'wysiwyg_menu');
				description.name = 'description';
				description.value = document.querySelector('meta[name="description"]').content;
				description.required = true;
				description.maxLength = 160;
				description.placeholder = 'Description will appear in searches. 160 character limit';
				legend.textContent = 'Update Post';
				submit.type = 'Submit';
				submit.textContent = 'Update Post';
				title.setAttribute('contenteditable', 'true');
				title.dataset.inputName = 'title';
				keywords.setAttribute('contenteditable', 'true');
				keywords.dataset.inputName = 'keywords';
				keywords.setAttribute('open', '');
				content.setAttribute('contenteditable', 'true');
				content.dataset.inputName = 'content';
				content.dataset.dropzone = 'main';
				oldTitle.name = 'old_title';
				oldTitle.type = 'hidden';
				oldTitle.readonly = true;
				oldTitle.required = true;
				oldTitle.value = title.textContent;
				fieldset.append(legend, header, content, oldTitle, description, submit);
				form.appendChild(fieldset);
				article.appendChild(form);
				var retain = setInterval(function() {
					ajax({
						url: document.baseURI,
						request: 'action=keep-alive'
					}).then(
						handleJSON
					).catch(function(err) {
						$('body > progress').delete();
						console.error(err);
					});
				}, 60000);
				form.addEventListener('submit', function() {
					clearInterval(retain);
				});
			});
		});
		node.query('[label="Clear Cache"]').forEach(function(el) {
			el.addEventListener('click', function() {
				if (!this.dataset.has('confirm') || confirm(this.dataset.confirm)) {
					cache.clear();
				}
			});
		});
	});
	return this;
};
function notifyLocation() {
	'use strict';
	getLocation({
		enableHighAccuracy: true,
		maximumAge: 0
	}).then(function(pos) {
		console.log(pos.coords);
		notify({
			title: 'Your current location:',
			body: 'Longitude: ' + pos.coords.longitude + '\nLatitude: ' + pos.coords.latitude,
			icon: document.baseURI + 'images/icons/map.png'
		});
	});
}
