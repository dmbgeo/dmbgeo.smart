<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/**
 * @var string $componentPath
 * @var string $componentName
 * @var array $arCurrentValues
 * @global CUserTypeManager $USER_FIELD_MANAGER
 */

use Bitrix\Main\Loader,
    Bitrix\Main\Web\Json,
    Bitrix\Iblock,
    Bitrix\Catalog,
    Bitrix\Currency;

global $USER_FIELD_MANAGER;

if (!Loader::includeModule('iblock'))
    return;

$catalogIncluded = Loader::includeModule('catalog');
CBitrixComponent::includeComponentClass($componentName);

$usePropertyFeatures = Iblock\Model\PropertyFeature::isEnabledFeatures();

$iblockExists = (!empty($arCurrentValues['IBLOCK_ID']) && (int) $arCurrentValues['IBLOCK_ID'] > 0);

$arIBlockType = CIBlockParameters::GetIBlockTypes();

$offersIblock = array();
if ($catalogIncluded) {
    $iterator = Catalog\CatalogIblockTable::getList(array(
        'select' => array('IBLOCK_ID'),
        'filter' => array('!=PRODUCT_IBLOCK_ID' => 0)
    ));
    while ($row = $iterator->fetch())
        $offersIblock[$row['IBLOCK_ID']] = true;
    unset($row, $iterator);
}

$arIBlock = array();
$iblockFilter = !empty($arCurrentValues['IBLOCK_TYPE'])
    ? array('TYPE' => $arCurrentValues['IBLOCK_TYPE'], 'ACTIVE' => 'Y')
    : array('ACTIVE' => 'Y');

$rsIBlock = CIBlock::GetList(array('SORT' => 'ASC'), $iblockFilter);
while ($arr = $rsIBlock->Fetch()) {
    $id = (int) $arr['ID'];
    if (isset($offersIblock[$id]))
        continue;
    $arIBlock[$id] = '[' . $id . '] ' . $arr['NAME'];
}
unset($id, $arr, $rsIBlock, $iblockFilter);
unset($offersIblock);

$defaultValue = array('-' => GetMessage('CP_BCS_EMPTY'));

$arProperty = array();
$arProperty_N = array();
$arProperty_X = array();
$listProperties = array();


if ($iblockExists) {
    $propertyIterator = Iblock\PropertyTable::getList(array(
        'select' => array('ID', 'IBLOCK_ID', 'NAME', 'CODE', 'PROPERTY_TYPE', 'MULTIPLE', 'LINK_IBLOCK_ID', 'USER_TYPE', 'SORT'),
        'filter' => array('=IBLOCK_ID' => $arCurrentValues['IBLOCK_ID'], '=ACTIVE' => 'Y'),
        'order' => array('SORT' => 'ASC', 'NAME' => 'ASC')
    ));
    while ($property = $propertyIterator->fetch()) {
        
        $propertyCode = (string) $property['CODE'];

        if ($propertyCode === '') {
            $propertyCode = $property['ID'];
        }

        $propertyName = '[' . $propertyCode . '] ' . $property['NAME'];

        $arProperty[$propertyCode] = $propertyName;

        if ($property['MULTIPLE'] === 'Y') {
            $arProperty_X[$propertyCode] = $propertyName;
        } elseif ($property['PROPERTY_TYPE'] == Iblock\PropertyTable::TYPE_LIST) {
            $arProperty_X[$propertyCode] = $propertyName;
        } elseif ($property['PROPERTY_TYPE'] == Iblock\PropertyTable::TYPE_ELEMENT && (int) $property['LINK_IBLOCK_ID'] > 0) {
            $arProperty_X[$propertyCode] = $propertyName;
        }


        if ($property['PROPERTY_TYPE'] == Iblock\PropertyTable::TYPE_NUMBER) {
            $arProperty_N[$propertyCode] = $propertyName;
        }
    }
    unset($propertyCode, $propertyName, $property, $propertyIterator);
}

$arProperty_UF = array();
$arSProperty_LNS = array();
$arSProperty_F = array();
if ($iblockExists) {
    $arUserFields = $USER_FIELD_MANAGER->GetUserFields('IBLOCK_' . $arCurrentValues['IBLOCK_ID'] . '_SECTION', 0, LANGUAGE_ID);

    foreach ($arUserFields as $FIELD_NAME => $arUserField) {
        $arUserField['LIST_COLUMN_LABEL'] = (string) $arUserField['LIST_COLUMN_LABEL'];
        $arProperty_UF[$FIELD_NAME] = $arUserField['LIST_COLUMN_LABEL'] ? '[' . $FIELD_NAME . ']' . $arUserField['LIST_COLUMN_LABEL'] : $FIELD_NAME;

        if ($arUserField['USER_TYPE']['BASE_TYPE'] === 'string') {
            $arSProperty_LNS[$FIELD_NAME] = $arProperty_UF[$FIELD_NAME];
        }

        if ($arUserField['USER_TYPE']['BASE_TYPE'] === 'file' && $arUserField['MULTIPLE'] === 'N') {
            $arSProperty_F[$FIELD_NAME] = $arProperty_UF[$FIELD_NAME];
        }
    }
    unset($arUserFields);
}


$arSections = array('all' => GetMessage('ALL_SECTIONS'));
$rsSection = \CIBlockSection::GetList(
    array("NAME" => "ASC"),
    array("IBLOCK_ID" => $arCurrentValues['IBLOCK_ID'] ?? 0, "DEPTH_LEVEL" => 1, "ACTIVE" => 'Y')
);
while ($arSection = $rsSection->fetch()) {
    $arSections[$arSection["ID"]] = "[" . $arSection["ID"] . "] " . $arSection["NAME"];
}
$arSort = CIBlockParameters::GetElementSortFields(
    array('SHOWS', 'SORT', 'TIMESTAMP_X', 'NAME', 'ID', 'ACTIVE_FROM', 'ACTIVE_TO'),
    array('KEY_LOWERCASE' => 'Y')
);



$arAscDesc = array(
    "asc" => GetMessage("IBLOCK_SORT_ASC"),
    "desc" => GetMessage("IBLOCK_SORT_DESC"),
);
$arComponentParameters = array(
    'PARAMETERS' => array(
        'IBLOCK_TYPE' => array(
            'PARENT' => 'BASE',
            'NAME' => GetMessage('IBLOCK_TYPE'),
            'TYPE' => 'LIST',
            'VALUES' => $arIBlockType,
            'REFRESH' => 'Y',
        ),
        'IBLOCK_ID' => array(
            'PARENT' => 'BASE',
            'NAME' => GetMessage('IBLOCK_IBLOCK'),
            'TYPE' => 'LIST',
            'ADDITIONAL_VALUES' => 'Y',
            'VALUES' => $arIBlock,
            'REFRESH' => 'Y',
        ),
        "SECTION_ID" => array(
            "PARENT" => "BASE",
            "NAME" => GetMessage("SECTION_ID"),
            "TYPE" => "LIST",
            "VALUES" => $arSections,
            "DEFAULT" => '',
        ),
        'PROPERTY_CODE' => array(
            'PARENT' => 'VISUAL',
            'NAME' => GetMessage('IBLOCK_PROPERTY'),
            'TYPE' => 'LIST',
            'MULTIPLE' => 'Y',
            'VALUES' => $arProperty,
            'ADDITIONAL_VALUES' => 'Y',
        ),
        'SECTION_USER_FIELDS' => array(
            'PARENT' => 'DATA_SOURCE',
            'NAME' => GetMessage('CP_BCS_SECTION_USER_FIELDS'),
            'TYPE' => 'LIST',
            'MULTIPLE' => 'Y',
            'ADDITIONAL_VALUES' => 'Y',
            'VALUES' => $arProperty_UF,
        ),
        "ELEMENT_SORT_FIELD" => array(
            "PARENT" => "LIST_SETTINGS",
            "NAME" => GetMessage("IBLOCK_ELEMENT_SORT_FIELD"),
            "TYPE" => "LIST",
            "VALUES" => $arSort,
            "ADDITIONAL_VALUES" => "Y",
            "DEFAULT" => "id",
        ),
        "ELEMENT_SORT_ORDER" => array(
            "PARENT" => "LIST_SETTINGS",
            "NAME" => GetMessage("IBLOCK_ELEMENT_SORT_ORDER"),
            "TYPE" => "LIST",
            "VALUES" => $arAscDesc,
            "DEFAULT" => "desc",
            "ADDITIONAL_VALUES" => "Y",
        ),
        "ELEMENT_SORT_FIELD2" => array(
            "PARENT" => "LIST_SETTINGS",
            "NAME" => GetMessage("IBLOCK_ELEMENT_SORT_FIELD2"),
            "TYPE" => "LIST",
            "VALUES" => $arSort,
            "ADDITIONAL_VALUES" => "Y",
            "DEFAULT" => "id",
        ),
        "ELEMENT_SORT_ORDER2" => array(
            "PARENT" => "LIST_SETTINGS",
            "NAME" => GetMessage("IBLOCK_ELEMENT_SORT_ORDER2"),
            "TYPE" => "LIST",
            "VALUES" => $arAscDesc,
            "DEFAULT" => "desc",
            "ADDITIONAL_VALUES" => "Y",
        ),
        "IBLOCK_ELEMENT_COUNT" => array(
            'PARENT' => 'VISUAL',
            "NAME" => GetMessage("IBLOCK_ELEMENT_COUNT"),
            "TYPE" => "NUMBER",
            "DEFAULT" => "0",
        ),
        'CACHE_TIME' => array('DEFAULT' => 36000),
    ),
);
