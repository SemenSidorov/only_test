<?
if (!function_exists('HLEntity')) {
    function HLEntity($hlID = false)
    {
		if(!$hlID) return false;
		
		\Bitrix\Main\Loader::IncludeModule("highloadblock");
		$hlblock = \Bitrix\Highloadblock\HighloadBlockTable::getById($hlID)->fetch();
		$entity = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlblock);
		return $entity->getDataClass();
    }
}