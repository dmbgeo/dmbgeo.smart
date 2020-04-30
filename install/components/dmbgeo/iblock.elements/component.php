<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use \Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader,
    Bitrix\Main\Web\Json,
    Bitrix\Iblock,
    Bitrix\Catalog,
    Bitrix\Currency;

/** @var CBitrixComponent $this */
/** @var array $arParams */
/** @var array $arResult */
/** @var string $componentPath */
/** @var string $componentName */
/** @var string $componentTemplate */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */


if (!Loader::includeModule('iblock'))
    return;

global $USER_FIELD_MANAGER;

foreach ($arParams['PROPERTY_CODE'] ?? array() as $key => $PROPERTY_CODE) {
    if (!$PROPERTY_CODE) {
        unset($arParams['PROPERTY_CODE'][$key]);
    }
}
foreach ($arParams['SECTION_USER_FIELDS'] ?? array() as $key => $SECTION_USER_FIELD) {
    if (!$SECTION_USER_FIELD) {
        unset($arParams['SECTION_USER_FIELDS'][$key]);
    }
}

if (!$arParams['IBLOCK_ID'] ?? 0) {
    $arParams['IBLOCK_ID'] = 0;
}

if (!$arParams['SECTION_ID'] ?? 0) {
    $arParams['SECTION_ID'] = 0;
}

$arFilter = array(
    "ACTIVE" => "Y",
    'IBLOCK_ID' => $arParams["IBLOCK_ID"],
);

if ($arParams["SECTION_ID"] > 0) {
    $arFilter['INCLUDE_SUBSECTIONS'] = $arParams["SECTION_ID"];
}


$rsElement = \CIBlockElement::GetList(
    $arOrder = array($arParams['ELEMENT_SORT_FIELD'] => $arParams['ELEMENT_SORT_ORDER'], $arParams['ELEMENT_SORT_FIELD2'] => $arParams['ELEMENT_SORT_ORDER2']),
    $arFilter,
    false,
    $arParams['IBLOCK_ELEMENT_COUNT'] > 0 ? array('nTopCount' => $arParams['IBLOCK_ELEMENT_COUNT']) : false
);

while ($arElement = $rsElement->GetNext()) {

    $ITEM = $arElement;

    if ($ITEM['PREVIEW_PICTURE']) {
        $ITEM['PREVIEW_PICTURE'] = \CFile::GetFileArray($ITEM['PREVIEW_PICTURE']);
    }
    if ($ITEM['DETAIL_PICTURE']) {
        $ITEM['DETAIL_PICTURE'] = \CFile::GetFileArray($ITEM['DETAIL_PICTURE']);
    }
    $ITEM['PROPERTIES'] = array();
    $res = CIBlockElement::GetByID($ITEM['ID']);
    if ($ar_res = $res->GetNextElement()) {

        foreach ($arParams['PROPERTY_CODE'] ?? array() as $PROPERTY_CODE) {
            $ITEM['PROPERTIES'][$PROPERTY_CODE] = $ar_res->GetProperty($PROPERTY_CODE);
            if ($ITEM['PROPERTIES'][$PROPERTY_CODE]['PROPERTY_TYPE'] == 'F') {
                if ($ITEM['PROPERTIES'][$PROPERTY_CODE]['MULTIPLE'] == 'Y') {
                    foreach ($ITEM['PROPERTIES'][$PROPERTY_CODE]['VALUE'] as &$val) {
                        $val = \CFile::GetFileArray($val);
                    }
                } else {
                    $ITEM['PROPERTIES'][$PROPERTY_CODE]['VALUE'] = \CFile::GetFileArray($ITEM['PROPERTIES'][$PROPERTY_CODE]['VALUE']);
                }
            }
        }
    }

    $arResult['ITEMS'][] = $ITEM;
}

foreach ($arParams['SECTION_USER_FIELDS'] ?? array() as $key => $SECTION_USER_FIELD) {
    $arResult['SECTION_USER_FIELDS'][$SECTION_USER_FIELD] = $USER_FIELD_MANAGER->GetUserFieldValue('IBLOCK_' . $arParams['IBLOCK_ID'] . '_SECTION', $SECTION_USER_FIELD, $USER->GetID(), LANGUAGE_ID);
}

if (!empty($arResult['ITEMS'])) {
    $this->IncludeComponentTemplate();
}
