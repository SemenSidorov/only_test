<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

class Catalog extends CBitrixComponent
{
    public function executeComponent()
    {
        // кеширование в данном компоненте неприминимо, потому что для каждого сотрудника ищется свой набор машин. А так же каждую машину могут в любое время забронировать.
        global $APPLICATION;
        global $USER;
        \Bitrix\Main\Loader::IncludeModule("iblock");
        
        $order = array('sort' => 'asc');
        $tmp = 'sort';
        if(!$arUser = CUser::GetList($order, $tmp, ["ID" => $USER->GetID()], ["SELECT" => ["UF_POST"]])->Fetch()) return;
        if(!$post_id = $arUser["UF_POST"]) return;

        $post_entity = HLEntity(HL_POSTS);
        if(!$arPost = $post_entity::getList(['filter' => ['ID' => $post_id], 'select' => ['UF_COMFORT']])->Fetch()) return;
        if(!$arComfort = $arPost["UF_COMFORT"]) $arComfort = [1];

        $orders_entity = HLEntity(HL_ORDERS);
        $obOrders = $orders_entity::getList(
            'filter' => [
                'LOGIC' => 'OR',
                ['UF_COMFORT' => $arComfort, '>UF_DATETIME_START' => $_GET["datetimestart"], '<UF_DATETIME_START' => $_GET["datetimestop"]],
                ['UF_COMFORT' => $arComfort, '>UF_DATETIME_STOP' => $_GET["datetimestart"], '<UF_DATETIME_STOP' => $_GET["datetimestop"]]
            ],
            'select' => ['UF_CAR']
        );
        $arCars = [];
        while($order = $obOrders->Fetch()){
            $arCars[] = $order["UF_CAR"];
        }

        $arDrivers = [];
        // на боевом задании нужно будет ещё прикрутить пагинацию. В тз этого не было указано, поэтому не сделал
        $obCars = CIBlockElement::GetList([], ["IBLOCK_ID" => IB_CARS, "!ID" => $arCars, "PROPERTY_COMFORT" => $arComfort], false, false, ["ID", "IBLOCK_ID", "NAME", "PROPERTY_DRIVER", "PROPERTY_COMFORT"]);
        while($car = $obCars->Fetch()){
            $arResult['CARS'][] = $car;
            $arDrivers[] = $car["PROPERTY_DRIVER_VALUE"];
        }
        $arDrivers = array_unique($arDrivers);

        $obDrivers = CIBlockElement::GetList([], ["IBLOCK_ID" => IB_DRIVERS, "ID" => $arDrivers]);
        while($driver = $obDrivers->Fetch()){
            $arResult['DRIVERS'][$driver["ID"]] = $driver;
        }
        
        $this->arResult = $arResult;
        $this->includeComponentTemplate();
    }
}
?>