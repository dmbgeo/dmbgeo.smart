<?php

use Bitrix\Highloadblock\HighloadBlockTable as HLBT;
use Bitrix\Main\Entity;


class SmartBitrix
{
    public static $MODULE_ID = 'dmbgeo.smart';

    public static function dump(...$var)
    {

        if (!is_array($_SESSION['SESS_AUTH'])) {
            session_start();
        }

        if (isset($_SESSION['SESS_AUTH']['ADMIN']) && $_SESSION['SESS_AUTH']['ADMIN'] === true) {
            var_dump($var);
        }
    }


    public static function pre_dump(...$var)
    {

        if (!is_array($_SESSION['SESS_AUTH'])) {
            session_start();
        }

        if (isset($_SESSION['SESS_AUTH']['ADMIN']) && $_SESSION['SESS_AUTH']['ADMIN'] === true) {
            echo '<pre>';
            var_dump($var);
            echo '</pre>';
        }
    }

    public static function getEnumValueIDByValue($IBLOCK_ID,$CODE, $VALUE){
        if (!CModule::IncludeModule("iblock")) {
            throw new Exception('Не удаётся подключить модуль "Информационные блоки"');
        }
        $property_enums = CIBlockPropertyEnum::GetList(Array("DEF"=>"DESC", "SORT"=>"ASC"), Array("IBLOCK_ID"=>$IBLOCK_ID, "CODE"=>$CODE, "VALUE"=>$VALUE));
        if($enum_fields = $property_enums->GetNext())
        {
            return $enum_fields["ID"];
        }
    }

    public static function getEnum($ID){
        if (!CModule::IncludeModule("iblock")) {
            throw new Exception('Не удаётся подключить модуль "Информационные блоки"');
        }
        $property_enums = CIBlockPropertyEnum::GetByID($ID);
        if($enum_fields = $property_enums->GetNext())
        {
            return $enum_fields;
        }
    }

    public static function GetEnumValueIDByCode($enumCode, $propertyCode = null, $IBlockCode = null)
    {
        if (!CModule::IncludeModule("iblock")) {
            throw new Exception('Не удаётся подключить модуль "Информационные блоки"');
        }
        $arPropertyEnumFilter = array("EXTERNAL_ID" => "$enumCode");
        if (isset($IBlockCode)) {
            $res = CIBlock::GetList(array(), array('ACTIVE' => 'Y', "CODE" => $IBlockCode), true);
            $ar = $res->Fetch();
            $arPropertyEnumFilter['IBLOCK_ID'] = $ar['ID'];
        }
        if (isset($propertyCode)) {
            $arPropertyEnumFilter['CODE'] = "$propertyCode";
        }

        $property_enums = CIBlockPropertyEnum::GetList(array(), $arPropertyEnumFilter);
        $enum_field = $property_enums->GetNext();

        return $enum_field['ID'];
    }


    public static function getSites()
    {
        $SITES = array();
        $rsSites = \CSite::GetList($by = "sort", $order = "desc");
        while ($arSite = $rsSites->Fetch()) {
            $SITES[] = $arSite;
        }
        return $SITES;
    }

    public static function setNewUrl($newUrl)
    {
        global $APPLICATION;
        $_SERVER['REQUEST_URI'] = $newUrl;
        $application = \Bitrix\Main\Application::getInstance();
        $context = $application->getContext();
        $request = $context->getRequest();
        $Response = $context->getResponse();
        $Server = $context->getServer();
        $server_get = $Server->toArray();
        $server_get["REQUEST_URI"] = $_SERVER["REQUEST_URI"];
        $Server->set($server_get);
        $context->initialize(new Bitrix\Main\HttpRequest($Server, array(), array(), array(), $_COOKIE), $Response, $Server);
        $APPLICATION->SetCurPage($_SERVER["REQUEST_URI"]);
        $APPLICATION->reinitPath();
    }

    public static function getOptions($SITE_ID = SITE_ID)
    {
        // $params['MODULE_STATUS'] = \Bitrix\Main\Config\Option::get(static::$MODULE_ID, 'DMBGEO_PREFILTERS_OPTION_MODULE_STATUS_' . $SITE_ID, "N");
        // $params['DELETE_STATUS'] = \Bitrix\Main\Config\Option::get(static::$MODULE_ID, 'DMBGEO_PREFILTERS_OPTION_DATA_DELETE_STATUS_' . $SITE_ID, "N");
        // $params['IBLOCK_ID'] = \Bitrix\Main\Config\Option::get(static::$MODULE_ID, 'DMBGEO_PREFILTERS_OPTION_IBLOCK_ID_' . $SITE_ID, "N");
        // return $params;
    }
    public static function getBaseID($ELEMENT_ID, $IBLOCK_ID)
    {
        CModule::IncludeModule('iblock');
        CModule::IncludeModule('catalog');
        $ID = CCatalogSku::GetProductInfo($ELEMENT_ID, $IBLOCK_ID);
        if (is_array($ID)) {
            return $ID['ID'];
        }
        return 0;
    }

    public static function elementUpdate($ELEMENT_ID, $VALUES)
    {
        CModule::IncludeModule('iblock');
        $el = new CIBlockElement;
        return $el->Update($ELEMENT_ID, $VALUES);
    }


    public static function productNDS($ELEMENT_ID, $ID_NDS = 1)
    {
        CModule::IncludeModule('iblock');
        CModule::IncludeModule('catalog');
        $res = array("VAT_INCLUDED" => 'Y', 'VAT_ID' => $ID_NDS);
        CCatalogProduct::Update($ELEMENT_ID, $res);
    }


    public static function productMeasure($ELEMENT_ID, $ID_MEASURE = 1)
    {
        CModule::IncludeModule('iblock');
        CModule::IncludeModule('catalog');
        $res = array('MEASURE ' => $ID_MEASURE);
        CCatalogProduct::Update($ELEMENT_ID, $res);
    }



    public static function elementPropertyUpdate($ELEMENT_ID, $IBLOCK_ID, $PROPERTIES)
    {
        CModule::IncludeModule('iblock');
        CIBlockElement::SetPropertyValuesEx($ELEMENT_ID, $IBLOCK_ID, $PROPERTIES);
    }

    public static function updateElementIndex($ELEMENT_ID, $IBLOCK_ID)
    {
        CModule::IncludeModule('iblock');
        \Bitrix\Iblock\PropertyIndex\Manager::updateElementIndex($IBLOCK_ID, $ELEMENT_ID);
    }

    public static function addPropertyTranslit($IBLOCK_ID, $PROPERTY_NAME, $TYPE = 'L', $CODE = false, $PREFIX = "DMB_")
    {
        CModule::IncludeModule('iblock');
        $return = 0;

        $PROPERTY_CODE = self::translitCode($PROPERTY_NAME, $PREFIX);
        // check property
        $dbProperties = CIBlockProperty::GetList(array(), array("IBLOCK_ID" => $IBLOCK_ID, "CODE" => $PROPERTY_CODE));
        if (!$arFields = $dbProperties->GetNext()) {
            // add property
            $arFields = array(
                "IBLOCK_ID" => $IBLOCK_ID,
                "NAME" => $PROPERTY_NAME,
                "ACTIVE" => "Y",
                "SORT" => "500",
                "CODE" => $PROPERTY_CODE,
                "PROPERTY_TYPE" => $TYPE,
                "MULTIPLE" => "N",
            );

            $ibp = new CIBlockProperty;
            $PropID = $ibp->Add($arFields);
            if (IntVal($PropID))
                $return = $PropID;
        } else {
            $return = $arFields["ID"];
        }
        if (!$CODE) {
            return $return;
        } else {
            return $PROPERTY_CODE;
        }
    }


    public static function translitCode($PROPERTY_NAME, $PREFIX = "DMB_")
    {
        // translit name
        $arTransParams = array("max_len" => 25, "change_case" => "U");
        return $PROPERTY_CODE = $PREFIX . CUtil::translit($PROPERTY_NAME, "ru", $arTransParams);
    }

    public static function getPropertyID($IBLOCK_ID, $PROPERTY_CODE)
    {
        CModule::IncludeModule('iblock');
        $return = 0;
        // check property
        $dbProperties = CIBlockProperty::GetList(array(), array("IBLOCK_ID" => $IBLOCK_ID, "CODE" => $PROPERTY_CODE));
        if ($arFields = $dbProperties->GetNext()) {
            $return = $arFields["ID"];
        }
        return $return;
    }

    public static function is_mobile()
    {
        $pda_patterns = array(
            'MIDP', 'FLY-', 'MMP', 'Mobile', 'MOT-',
            'Nokia', 'Obigo', 'Panasonic', 'PPC',
            'ReqwirelessWeb', 'Samsung', 'SEC-SGH',
            'Smartphone', 'SonyEricsson', 'Symbian',
            'WAP Browser', 'j2me', 'BREW', 'iPod', 'iPhone', 'Android', 'webOS', 'BlackBerry', 'Opera M', 'HTC_', 'Fennec/', 'WindowsPhone', 'WP7', 'WP8'
        );
        $agent = $_SERVER['HTTP_USER_AGENT'];
        $user_agent = strtolower($agent);
        foreach ($pda_patterns as $val) {
            $val = strtolower($val);
            if (strpos($user_agent, $val) !== false) {
                return true;
            }
        }
        return false;
    }


    public static function getPhotoResize($id, $width = 0, $height = 0, $method = BX_RESIZE_IMAGE_EXACT)
    {
        if (!CModule::IncludeModule('iblock'))
            return false;
        $arThumbPhoto = CFile::ResizeImageGet(
            $id,
            array('width' => $width, 'height' => $height),
            $method,
            true,
            array()
        );

        $result = array(
            "SRC"      => $arThumbPhoto['src'],
            "HEIGHT"   => $arThumbPhoto['height'],
            "WIDTH"      => $arThumbPhoto['width'],
            "ORIGINAL_PARAM" => $arThumbPhoto
        );

        return $result;
    }


    public static function getFile($id)
    {
        if (!CModule::IncludeModule('iblock'))
            return false;
        return  CFile::GetFileArray($id);
    }

    public static function getEntityDataClass($HlBlockId)
    {
        CModule::IncludeModule('highloadblock');
        if (empty($HlBlockId) || $HlBlockId < 1) {
            return false;
        }
        $hlblock = Bitrix\Highloadblock\HighloadBlockTable::getById($HlBlockId)->fetch();
        $entity = Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlblock);

        $entity_data_class = $entity->getDataClass();
        return $entity_data_class;
    }



    public static function getEntityData($table_id, $filter = array(), $order = array("ID" => "ASC"), $select = array('*'))
    {
        CModule::IncludeModule('highloadblock');
        $result = array();
        $entity_data_class = $this->getEntityDataClass($table_id);
        $rsData = $entity_data_class::getList(array(
            'select' => $select,
            "order" => $order,
            "filter" => $filter
        ));
        while ($el = $rsData->fetch()) {
            $result[] = $el;
        }
        return $result;
    }

    public static function getIncludeStr($name = '')
    {
        if (!is_string($name))
            return "";
        if (is_file($_SERVER['DOCUMENT_ROOT'] . "/include/" . $name . ".php"))
            return file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/include/" . $name . ".php");
        return "";
    }


    public static function setInclude($name = "", $out = true)
    {
        if (!is_string($name))
            return "";
        global $APPLICATION;
        ob_start();
        $APPLICATION->IncludeComponent(
            "bitrix:main.include",
            "",
            array(
                "AREA_FILE_SHOW" => "file",
                "AREA_FILE_SUFFIX" => "inc",
                "EDIT_TEMPLATE" => "",
                "PATH" => "/include/" . $name . ".php"
            )
        );
        $result = ob_get_contents();
        ob_end_clean();
        if (!$out) {

            $str = get_string_include_file($name);
            $result = str_replace($str, "", $result);
        }

        return $result;
    }


    public static function getIblockElements($iblock_id, $filter = array(), $sort = array('SORT' => 'ASC'), $select = array(), $pag = 1000)
    {
        CModule::IncludeModule("iblock");
        $result = array();
        $arFilter = array("IBLOCK_ID" => IntVal($iblock_id));
        foreach ($filter as $key => $value) {
            $arFilter[$key] = $value;
        }
        $res = CIBlockElement::GetList($sort, $arFilter, false, array("nPageSize" => $pag), $select);
        while ($ob = $res->GetNextElement()) {
            $arEl = $ob->GetFields();
            $arEl["PREVIEW_PICTURE"] = CFile::GetFileArray($arEl["PREVIEW_PICTURE"]);
            $arEl["DETAIL_PICTURE"] = CFile::GetFileArray($arEl["DETAIL_PICTURE"]);
            $arEl['PROPERTIES'] = $ob->GetProperties();
            $result[] = $arEl;
        }

        return $result;
    }

    public static function getElement($id)
    {
        CModule::IncludeModule("iblock");
        $result = array();
        $res = CIBlockElement::GetByID($id);
        while ($ob = $res->GetNextElement()) {
            $arEl = $ob->GetFields();
            $arEl["PREVIEW_PICTURE"] = CFile::GetFileArray($arEl["PREVIEW_PICTURE"]);
            $arEl["DETAIL_PICTURE"] = CFile::GetFileArray($arEl["DETAIL_PICTURE"]);
            $arEl['PROPERTIES'] = $ob->GetProperties();
            $result = $arEl;
        }

        return $result;
    }

    public static function getElementByCode($IBLOCK,$CODE)
    {
        CModule::IncludeModule("iblock");
        $result = array();
        $arFilter = array("IBLOCK_ID" => IntVal($IBLOCK),'CODE'=>$CODE);
        $res = CIBlockElement::GetList($sort, $arFilter, false, array("nPageSize" => $pag), $select);
        if ($ob = $res->GetNextElement()) {
            $arEl = $ob->GetFields();
            $arEl["PREVIEW_PICTURE"] = CFile::GetFileArray($arEl["PREVIEW_PICTURE"]);
            $arEl["DETAIL_PICTURE"] = CFile::GetFileArray($arEl["DETAIL_PICTURE"]);
            $arEl['PROPERTIES'] = $ob->GetProperties();
            $result = $arEl;
        }

        return $result;
    }

    public static function updateElementProperty($id, $property_code, $value)
    {
        CModule::IncludeModule("iblock");
        return CIBlockElement::SetPropertyValueCode(
            $id,
            $property_code,
            $value
        );
    }



    public static function getIblockSections($iblock_id, $filter = array('DEPTH_LEVEL' => 1), $sort = array('NAME' => 'ASC'), $select = array(), $pag = 1000)
    {
        CModule::IncludeModule("iblock");
        $result = array();
        $arFilter = array("IBLOCK_ID" => IntVal($iblock_id), "ACTIVE_DATE" => "Y", "ACTIVE" => "Y");
        foreach ($filter as $key => $value) {
            $arFilter[$key] = $value;
        }

        $res = CIBlockSection::GetList($sort, $arFilter, true, $select, array("nPageSize" => $pag));
        while ($ob = $res->GetNext()) {
            $arEl = $ob;
            $arEl["PICTURE"] = CFile::GetFileArray($arEl["PICTURE"]);
            $result[] = $arEl;
        }

        return $result;
    }

    public static function getSection($id)
    {
        CModule::IncludeModule("iblock");
        $result = array();
        $res = CIBlockSection::GetByID($id);
        while ($ob = $res->GetNext()) {
            $arEl = $ob;
            $arEl["PICTURE"] = CFile::GetFileArray($arEl["PICTURE"]);
            $result = $arEl;
        }

        return $result;
    }


    public static function getUserField($id)
    {
        $result = array();
        $rsEnum = CUserFieldEnum::GetList(array(), array('ID' => $id));
        while ($arEnum = $rsEnum->GetNext()) {
            $result[(int) $arEnum['ID']] = $arEnum;
        }
        return $result;
    }



    public static function getUserFieldSection($iblock, $id)
    {
        CModule::IncludeModule('iblock');
        $result = array();

        $rsResult = CIBlockSection::GetList(array("SORT" => "ASC"), array("IBLOCK_ID" => $iblock, "ID" => $id), false, array("UF_*"));
        if ($arSection = $rsResult->GetNext()) {
            foreach ($arSection as $key => $value) {
                if (strpos($key, 'UF_') !== false) {
                    $result[$key] = $value;
                }
            }
        }
        return $result;
    }



    public static function getRootSection($iblock, $id)
    {
        CModule::IncludeModule('iblock');
        return CIBlockSection::GetNavChain($iblock, $id);
    }
}
