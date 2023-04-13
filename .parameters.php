<? if( !defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true ) {
	die();
}

use \Bitrix\Main\Config\Option,
	\Bitrix\Main\Localization\Loc,
	\Bitrix\Main\Loader;


$arComponentParameters = [
	"PARAMETERS" => [

		"PRODUCT_ID" => [
			"PARENT"  => "BASE",
			"NAME"    => Loc::getMessage("PARAMETER_PRODUCT_ID"),
			"TYPE"    => "STRING",
			"DEFAULT" => '',
		],

		"OFFERS" => [
			"PARENT"  => "BASE",
			"NAME"    => Loc::getMessage("PARAMETER_OFFERS"),
			"TYPE"    => "STRING",
			"DEFAULT" => '',
		],

		"TITLE" => [
			"PARENT"  => "BASE",
			"NAME"    => Loc::getMessage("PARAMETER_TITLE"),
			"TYPE"    => "STRING",
			"DEFAULT" => Loc::getMessage("PARAMETER_TITLE_DEFAULT"),
		],

		"CART_REF" => [
			"PARENT"  => "BASE",
			"NAME"    => Loc::getMessage("PARAMETER_CART_REF"),
			"TYPE"    => "STRING",
			"DEFAULT" => "/personal/cart/",
		],

		"LOGIC" => [
			"PARENT"            => "BASE",
			"NAME"              => Loc::getMessage("PARAMETER_LOGIC"),
			"TYPE"              => "LIST",
			"VALUES"            => [
				"AND" => Loc::getMessage("PARAMETER_LOGIC_AND"),
				"OR"  => Loc::getMessage("PARAMETER_LOGIC_OR"),
			],
			"DEFAULT"           => 'AND',
		],

		"CACHE_TIME" => [
			"DEFAULT" => 36000,
			"PARENT"  => "CACHE_SETTINGS",
		],
		"CACHE_TYPE" => [
			"PARENT"            => "CACHE_SETTINGS",
			"NAME"              => Loc::getMessage("COMP_PROP_CACHE_TYPE"),
			"TYPE"              => "LIST",
			"VALUES"            => [
				"A" => Loc::getMessage("COMP_PROP_CACHE_TYPE_AUTO") . " " . Loc::getMessage("COMP_PARAM_CACHE_MAN"),
				"Y" => Loc::getMessage("COMP_PROP_CACHE_TYPE_YES"),
				"N" => Loc::getMessage("COMP_PROP_CACHE_TYPE_NO"),
			],
			"DEFAULT"           => "N",
			"ADDITIONAL_VALUES" => "N",
			"REFRESH"           => "Y"
		],
	],
];

//}

?>
