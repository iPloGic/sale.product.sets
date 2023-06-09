# README #

Компонент **sale.product.sets** - блок выбора состава набора для товаров или отдельных торговых предложений. Товар или предложения должны быть типа "набор".

### Подключение ###

Создайте на сайте директорию `\local\components\iplogic`. Скопируйте в нее скачанную директорию компонента sale.product.sets.

В нужном месте сайта добавьте вызов компонента. Код для подключения:

```
<?$APPLICATION->IncludeComponent(
	'iplogic:sale.product.sets',
	'',
	array(
		'PRODUCT_ID' => $arResult["ID"],
		'OFFERS' => $arOffersIds,
		"TITLE" => "Выберите комплект",
		"CART_REF" => "/personal/cart/",
		"LOGIC" => "AND",
		"CACHE_TIME" => 36000,
		"CACHE_TYPE" => "N",
	)
);?>
```

### Параметры ###

Параметры компонента описаны в таблице. Стандартные параметры для компонента опущены, о них можно узнать в документации Битрикс.

| Параметр | Описание                    |
| ------------- | ------------------------------ |
| PRODUCT_ID      | ID товара.  |
| OFFERS   |  Массив содержащий ID торговых предложений товара.    |
| TITLE   |  Заголовок блока.    |
| CART_REF   |  Ссылка на страницу корзины.    |
| LOGIC   |  Логика добавления товаров в набор. OR - можно добавить только один из предложенных товаров на выбор. AND - можно добавить любое количество товаров из предложенных.    |
