<?
if( !defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true ) {
	die();
}

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Loader;
use \Bitrix\Main\Error;
use \Bitrix\Main\ErrorCollection;
use \Bitrix\Sale\Compatible\DiscountCompatibility;
use \Bitrix\Sale\Basket;
use \Bitrix\Sale\Discount\Gift;
use \Bitrix\Sale\Fuser;

class iplogicSaleProductGifts extends \CBitrixComponent
	implements \Bitrix\Main\Engine\Contract\Controllerable, \Bitrix\Main\Errorable
{
	/** @var ErrorCollection */
	protected $errorCollection;

	function __construct($component = null)
	{
		parent::__construct($component);

		$a = explode("/", $_SERVER['SCRIPT_NAME']);
		$a[count($a) - 1] = "";
		$_SERVER['SCRIPT_DIR_NAME'] = implode("/", $a);

		$this->errorCollection = new ErrorCollection();

		if( !Loader::includeModule('iblock') ) {
			$this->setError('No iblock module');
		};

		if( !Loader::includeModule('sale') ) {
			$this->setError('No sale module');
		};

		if( !Loader::includeModule('catalog') ) {
			$this->setError('No catalog module');
		};
	}

	public function configureActions()
	{
		//fill it, or use default
		return [];
	}

	public function onPrepareComponentParams($arParams)
	{
		if(
			isset($arParams['IS_AJAX'])
			&& ($arParams['IS_AJAX'] == 'Y' || $arParams['IS_AJAX'] == 'N')
		) {
			$arParams['IS_AJAX'] = $arParams['IS_AJAX'] == 'Y';
		}
		else {
			if(
				isset($this->request['is_ajax'])
				&& ($this->request['is_ajax'] == 'Y' || $this->request['is_ajax'] == 'N')
			) {
				$arParams['IS_AJAX'] = $this->request['is_ajax'] == 'Y';
			}
			else {
				$arParams['IS_AJAX'] = false;
			}
		}

		$arParams['ACTION'] = $this->getParam('ACTION', $arParams);

		if( isset($this->request['product_ids']) ) {
			$arParams['PRODUCT_IDS'] = $this->request['product_ids'];
		}
		if($arParams['PRODUCT_IDS'] != "") {
			$arParams['PRODUCT_IDS'] = explode(",", $arParams['PRODUCT_IDS']);
			foreach($arParams['PRODUCT_IDS'] as $key => $val) {
				$arParams['PRODUCT_IDS'][$key] = trim($val);
			}
		}
		else {
			$arParams['PRODUCT_IDS'] = [];
		}

		if( isset($this->request['product_qty']) ) {
			$arParams['PRODUCT_QTY'] = $this->request['product_qty'];
		}
		if($arParams['PRODUCT_QTY'] != "") {
			$arParams['PRODUCT_QTY'] = explode(",", $arParams['PRODUCT_QTY']);
			foreach($arParams['PRODUCT_QTY'] as $key => $val) {
				$arParams['PRODUCT_QTY'][$key] = trim($val);
			}
		}
		else {
			$arParams['PRODUCT_QTY'] = [];
		}

		if(!is_array($arParams['OFFERS'])) {
			$arParams['OFFERS'] = [];
		}

		if($arParams['LOGIC'] != "OR" && $arParams['LOGIC'] != "AND") {
			$arParams['LOGIC'] = "AND";
		}

		return $arParams;
	}

	protected function getParam($name, $arParams)
	{
		if( isset($this->request[strtolower($name)]) && strlen($this->request[strtolower($name)]) > 0 ) {
			return strval($this->request[strtolower($name)]);
		}
		else {
			if( isset($arParams[strtoupper($name)]) && strlen($arParams[strtoupper($name)]) > 0 ) {
				return strval($arParams[strtoupper($name)]);
			}
			else {
				return '';
			}
		}
	}

	function executeComponent()
	{
		global $APPLICATION;

		if( $this->arParams['IS_AJAX'] ) {
			$APPLICATION->RestartBuffer();
		}

		if( !empty($this->arParams['ACTION']) ) {
			if( is_callable([$this, $this->arParams['ACTION'] . "Action"]) ) {
				try {
					call_user_func([$this, $this->arParams['ACTION'] . "Action"]);
				} catch( \Exception $e ) {
					$this->setError($e->getMessage());
				}
			}
		}

		if( count($this->getErrors()) ) {
			$this->arResponse['errors'] = $this->getErrors();
		}

		if( $this->arParams['IS_AJAX'] ) {
			if( $this->getTemplateName() != '' ) {
				ob_start();
				$this->includeComponentTemplate();
				$this->arResponse['html'] = ob_get_contents();
				ob_end_clean();
			}
			header('Content-Type: application/json');
			echo json_encode($this->arResponse);
			$APPLICATION->FinalActions();
			die();
		}
		else {
			$this->getSets();
			$this->includeComponentTemplate();
		}
	}

	protected function getSets()
	{
		$this->arResult["SETS"] = [];
		$arAllProductsIds = [];

		if ($set = $this->getProductSet($this->arParams["PRODUCT_ID"])) {
			$this->arResult["SETS"][$this->arParams["PRODUCT_ID"]] = $set;
			$arAllProductsIds[] = $this->arParams["PRODUCT_ID"];
		}
		foreach($this->arParams["OFFERS"] as $OID) {
			if ($set = $this->getProductSet($OID)) {
				$this->arResult["SETS"][$OID] = $set;
				$arAllProductsIds[] = $OID;
			}
		}
		if(count($arAllProductsIds)) {
			foreach( $this->arResult["SETS"] as $set ) {
				foreach( $set["ITEMS"] as $item ) {
					$arAllProductsIds[] = $item["ITEM_ID"];
				}
			}
			$arProductFeatures = $this->getProductsFeatures($arAllProductsIds);
			foreach($arProductFeatures as $key => $val) {
				unset($arProductFeatures[$key]["ID"]);
				unset($arProductFeatures[$key]["SORT"]);
			}
			foreach( $this->arResult["SETS"] as $key => $set ) {
				$this->arResult["SETS"][$key] = array_merge($set, $arProductFeatures[$set["ITEM_ID"]]);
				foreach( $set["ITEMS"] as $ikey => $item ) {
					$this->arResult["SETS"][$key]["ITEMS"][$ikey] = array_merge($item, $arProductFeatures[$item["ITEM_ID"]]);
				}
			}
		}
	}

	protected function getProductSet($ID) {
		$result = CCatalogProductSet::getAllSetsByProduct( $ID, CCatalogProductSet::TYPE_GROUP );
		if (!empty($result)) {
			return reset($result);
		}
		return false;
	}

	protected function getProductsFeatures($IDs) {
		$arProds = [];
		$listres = \CIBlockElement::GetList(
			[],
			[ "ID" => $IDs, "=ACTIVE" => "Y"/*, "=AVAILABLE" => "Y"*/ ]
		);
		while ( $prEl = $listres->GetNext() ) {
			$prEl["PREVIEW_PICTURE"] = \CFile::GetFileArray($prEl["PREVIEW_PICTURE"]);
			$prEl["DETAIL_PICTURE"] = \CFile::GetFileArray($prEl["DETAIL_PICTURE"]);
			$propres = \CIBlockElement::GetProperty($prEl["IBLOCK_ID"], $prEl["ID"]);
			while($prop = $propres->Fetch()) {
				$prEl["PROPERTIES"][] = $prop;
			}
			$prEl["PRODUCT"] = \CCatalogProduct::GetByID($prEl["ID"]);
			$prEl["PRICE"] = CCatalogProduct::GetOptimalPrice($prEl["ID"])["RESULT_PRICE"];
			$prEl["PRICE"]["PRINT_BASE_PRICE"] = CurrencyFormat($prEl["PRICE"]["BASE_PRICE"], $prEl["PRICE"]["CURRENCY"]);
			$prEl["PRICE"]["PRINT_DISCOUNT_PRICE"] = CurrencyFormat($prEl["PRICE"]["DISCOUNT_PRICE"], $prEl["PRICE"]["CURRENCY"]);
			$arProds[$prEl["ID"]] = $prEl;
		}
		return $arProds;
	}

	protected function add2basketAction()
	{
		$this->setTemplateName('');
		foreach($this->arParams['PRODUCT_IDS'] as $key => $id) {
			if($this->arParams['PRODUCT_QTY'][$key] > 0) {
				if(!Add2BasketByProductID($id, $this->arParams['PRODUCT_QTY'][$key])) {
					$this->setError("Failed to add product ".$id." to basket");
				}
			}
			else {
				$this->setError("Zero quantity of product ".$id);
			}
		}
		if( !count($this->getErrors()) ) {
			$this->arResponse['state'] = "OK";
		}
		else {
			$this->arResponse['state'] = "Errors";
		}
	}

	protected function refreshAction()
	{
		$this->getSets();
		if( count($this->getErrors()) ) {
			$this->setTemplateName('');
			$this->arResponse['html'] = "";
		}
	}


	protected function setError($str, $code = 0)
	{
		$error = new \Bitrix\Main\Error($str, $code, "");
		$this->errorCollection->setError($error);
	}

	/**
	 * Getting array of errors.
	 * @return Error[]
	 */
	public function getErrors()
	{
		return $this->errorCollection->toArray();
	}

	/**
	 * Getting once error with the necessary code.
	 * @param string $code Code of error.
	 * @return Error
	 */
	public function getErrorByCode($code)
	{
		return $this->errorCollection->getErrorByCode($code);
	}

}