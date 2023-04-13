(function() {
	'use strict';

	if (!!window.JCSaleProductSetsComponent)
		return;

	window.JCSaleProductSetsComponent = function(params) {
		this.formPosting = false;
		this.siteId = params.siteId || '';
		this.template = params.template || '';
		this.componentPath = params.componentPath || '';
		this.parameters = params.parameters || '';
		this.langMessages = params.langMessages || '';
		this.activator = params.activator;
		this.container = params.container;

		let obj = this;

		BX.bindDelegate(
			document.body,
			'click',
			{ class: this.activator },
			function() {
				obj.showSet(this.getAttribute('data-id'));
			}
		);

		BX.bindDelegate(
			document.body,
			'click',
			{ class: "el_set_order" },
			function() {
				obj.sendRequest('add2basket' ,this);
			}
		);

		let sets = document.querySelectorAll('.ipl-sps-set-products');
		for (let set of sets) {
			BX.bindDelegate(
				set,
				'click',
				{class: 'ipl-sps-product'},
				function () {
					obj.changeSet(this, obj.parameters.LOGIC);
				}
			);
		}
	};

	window.JCSaleProductSetsComponent.prototype.sendRequest = function(action, el) {

		let data = {
			action: action,
			siteId: this.siteId,
			template: this.template,
			parameters: this.parameters,
			langMessages: this.langMessages,
			product_ids: el.getAttribute('data-ids'),
			product_qty: el.getAttribute('data-qty'),
		};

		BX.ajax.loadJSON(
			this.componentPath + '/ajax.php' + (document.location.href.indexOf('clear_cache=Y') !== -1 ? '?clear_cache=Y' : ''),
			data,
			function(res) {
				if (res.errors !== undefined) {
					console.log(res);
				}
				else {
					el.querySelector(".set_buyre").textContent = data.langMessages.in_cart;
					el.setAttribute('href', data.parameters.CART_REF);
					this.removeClass(el, "el_set_order");
				}
			}
		);
	}

	window.JCSaleProductSetsComponent.prototype.showSet = function(id) {
		let divs = document.querySelectorAll(this.container + ' > div');
		for (let div of divs) {
			div.style.display = 'none';
		}
		document.querySelector(this.container + ' > div[data-product-id="' + id + '"]').style.display = 'block';
	}

	window.JCSaleProductSetsComponent.prototype.changeSet = function(el, logic) {
		let parent = el.parentNode;
		let elements = parent.children;
		if (logic === "OR") {
			for (let element of elements) {
				element.classList.remove("ipl-sps-product-checked");
			}
			el.classList.add("ipl-sps-product-checked");
		}
		else {
			if (el.classList.contains('ipl-sps-product-checked')) {
				el.classList.remove("ipl-sps-product-checked");
			}
			else {
				el.classList.add("ipl-sps-product-checked");
			}
		}
		let wrapper = el.parentNode.parentNode.parentNode;
		let available = true;
		if(el.parentNode.getAttribute('data-product-quantity') <= 0) {
			available = false;
		}
		let price = +el.parentNode.getAttribute("data-price");
		let ids = el.parentNode.getAttribute("data-product-id");
		let qty = '1';

		for (let element of elements) {
			if(element.classList.contains('ipl-sps-product-checked')) {
				price = +price + +(element.getAttribute("data-price") * element.getAttribute("data-quantity"));
				ids = ids + "," + element.getAttribute("data-product-id");
				qty = qty + "," + element.getAttribute("data-quantity");
				if(element.getAttribute("data-product-quantity") <= 0) {
					available = false;
				}
			}
		}

		let price_el = wrapper.querySelector('.set_price');
		price_el.setAttribute('data-price', price);
		price_el.querySelector('span').textContent = price;
		let buy_el = wrapper.querySelector('.ipl-sps-buy').querySelector('a');
		buy_el.setAttribute('data-ids', ids);
		buy_el.setAttribute('data-qty', qty);
		wrapper.querySelector('.ipl-sps-available').style.display = 'none';
		if (available) {
			wrapper.querySelector('.ipl-sps-available.yes').style.display = 'block';
		}
		else {
			wrapper.querySelector('.ipl-sps-available.no').style.display = 'block';
		}
	}

	// TODO: Use next functions in all actions of proj

	window.JCSaleProductSetsComponent.prototype.hasClass = function(el, className)
	{
		if (el.classList)
			return el.classList.contains(className);
		return !!el.className.match(new RegExp('(\\s|^)' + className + '(\\s|$)'));
	}

	window.JCSaleProductSetsComponent.prototype.addClass = function(el, className)
	{
		if (el.classList)
			el.classList.add(className)
		else if (!hasClass(el, className))
			el.className += " " + className;
	}

	window.JCSaleProductSetsComponent.prototype.removeClass = function(el, className)
	{
		if (el.classList)
			el.classList.remove(className)
		else if (hasClass(el, className))
		{
			var reg = new RegExp('(\\s|^)' + className + '(\\s|$)');
			el.className = el.className.replace(reg, ' ');
		}
	}


})();