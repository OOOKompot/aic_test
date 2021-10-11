<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Title");
?><?$APPLICATION->IncludeComponent(
	"kompot:rest.api",
	"",
	Array(
		"SEF_FOLDER" => "/api/",
		"SEF_MODE" => "Y",
		"SEF_URL_TEMPLATES" => Array("url"=>"#ENTITY#/#METHOD#/")
	)
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>