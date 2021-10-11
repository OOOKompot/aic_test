<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("IBLOCK_NEWS_NAME"),
	"DESCRIPTION" => GetMessage("IBLOCK_NEWS_DESCRIPTION"),
	"COMPLEX" => "Y",
	"PATH" => array(
		"ID" => "kompot",
		"CHILD" => array(
			"ID" => "rest.api",
			"NAME" => GetMessage("T_IBLOCK_DESC_NEWS"),
		),
	),
);

?>