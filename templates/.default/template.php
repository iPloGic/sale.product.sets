<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var customComponent $component */

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Application;

//echo "<pre>"; print_r($arParams); echo "</pre>";
//echo "<pre>"; print_r($arResult); echo "</pre>";

foreach($arParams as $key => $val) {
	if(substr($key,0,1) == "~")
		continue;
	if($key == "ACTION")
		continue;
	if($key == "PRODUCT_IDS")
		continue;
	if($key == "PRODUCT_QTY")
		continue;
	$arParameters[$key] = $val;
}
$arJsParams = [
	"parameters" => $arParameters,
	"siteId" => SITE_ID,
	"template" => $templateName,
	"componentPath" => $componentPath,
	"activator" => "el_offers_el",  // class
	"container" => ".ipl-sps-component-wrappper",  // selector
	"langMessages" => [
		"add_to_cart" => Loc::getMessage('IPL_SPS_ADD_TO_CART'),
		"in_cart" => Loc::getMessage('IPL_SPS_IN_CART'),
	]
];
$firstBlock = true;
?>
<div class="ipl-sps-component-wrappper">
	<? if(count($arResult["SETS"])) {
		foreach($arResult['SETS'] as $arSet) {
			$first = reset($arSet["ITEMS"]);
			$ids = $arSet["ITEM_ID"].",".$first["ITEM_ID"];
			$qty = "1".",".$first["QUANTITY"];
			$price = $arSet["PRICE"]["DISCOUNT_PRICE"] + $first["PRICE"]["DISCOUNT_PRICE"];
			$old_price = 0;
			if ($arSet["PRODUCT"]["QUANTITY"] > 0 && $first["PRODUCT"]["QUANTITY"]) {
				$avail = true;
			}
			else {
				$avail = false;
			}
			?>
			<div class="" data-product-id="<?=$arSet["ITEM_ID"]?>"<?=($firstBlock ? "" : 'style="display:none;"')?>>
				<? $firstBlock = false; ?>
				<div class="ipl-sps-outer">
					<div class="ipl-sps-title"><?=$arParams["TITLE"]?></div>
					<div class="ipl-sps-wrapper">
						<div class="ipl-sps-products">
							<div class="">
								<div class="ipl-sps-product ipl-sps-product-checked">
									<div class="ipl-sps-product-img" style="background-image: url('<?=$arSet["DETAIL_PICTURE"]["SRC"]?>')"></div>
								</div>
							</div>
							<div class="ipl-sps-plus">
								<i class="fa fa-plus-circle"></i>
							</div>
							<div class="ipl-sps-set-products"
							     data-product-id="<?=$arSet["ITEM_ID"]?>"
							     data-product-quantity="<?=$arSet["PRODUCT"]["QUANTITY"]?>"
							     data-price="<?=$arSet["PRICE"]["DISCOUNT_PRICE"]?>"
							>
								<? $firstRrod = true;
								foreach($arSet["ITEMS"] as $arItem) {
									?>
									<div class="ipl-sps-product<?=($firstRrod ? " ipl-sps-product-checked" : "")?>"
									     data-product-id="<?=$arItem["ITEM_ID"]?>"
									     data-quantity="<?=$arItem["QUANTITY"]?>"
									     data-product-quantity="<?=$arItem["PRODUCT"]["QUANTITY"]?>"
									     data-price="<?=$arItem["PRICE"]["DISCOUNT_PRICE"]?>"
									>
										<div class="ipl-sps-product-img" style="background-image: url('<?=$arItem["DETAIL_PICTURE"]["SRC"]?>')" title="<?=$arItem["NAME"]?>"></div>
										<div>+ <?=$arItem["PRICE"]["DISCOUNT_PRICE"]?> ₽</div>
										<a href="<?=$arItem["DETAIL_PAGE_URL"]?>" target="_blank">
											<?=Loc::getMessage('IPL_SPS_ABOUT')?>
										</a>
									</div>
									<?
									$firstRrod = false;
								} ?>
							</div>
						</div>
						<div class="ipl-sps-price">
								<div class="ipl-sps-price-val">
									<!--<div style="color: #777; text-decoration: line-through;" class="set_old_price">
										<? if($old_price > 0){?>
											<?=$old_price?> ₽
										<?}?>
									</div>-->
									<div data-price="<?=$price?>" class="set_price">
										<span><?=$price?></span> ₽
									</div>
								</div>
								<div class="ipl-sps-available yes"<?=($avail ? "" : " style='display:none'")?>>
									<i class="fa fa-check-circle"></i> <?=Loc::getMessage('IPL_SPS_IN_STOCK')?>
								</div>
								<div class="ipl-sps-available no"<?=($avail ? " style='display:none'" : "")?>>
									<i class="fa fa-times-circle"></i> <?=Loc::getMessage('IPL_SPS_NOT_IN_STOCK')?>
								</div>

						</div>
						<div class="ipl-sps-buy">
							<div>
								<a href="javascript:void(0)"
								   type="submit"
								   data-ids="<?=$ids; ?>"
								   data-qty="<?=$qty; ?>"
								   class="el_set_order ipl-sps-buy-button"
								>
									<span class="set_buyre"><?=Loc::getMessage('IPL_SPS_ADD_TO_CART')?></span>
								</a>
							</div>
						</div>
					</div>
				</div>
			</div>
		<? }
	} ?>
	<br><br>
</div>
<script>
	window.obJCSaleProductSetsComponent = new JCSaleProductSetsComponent(<?=CUtil::PhpToJSObject($arJsParams)?>)
</script>