<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("DMBGEO_NAME"),
	"DESCRIPTION" => GetMessage("DMBGEO_DESCRIPTION"),	
	"CACHE_PATH" => "Y",
	"PATH" => array(
		"ID" => "dmbgeo",
		"NAME" => GetMessage("DMBGEO_GROUP_NAME"),
		"CHILD" => array(
			"ID" => "DMBGEO_IBLOCK",
			"NAME" => GetMessage("DMBGEO_GROUP_NAME"),
		)
	),	
);
?>