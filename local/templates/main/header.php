<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?php
use Bitrix\Main\Page\Asset;
$obAsset = Asset::getInstance();
const MEDIA_DIR = SITE_TEMPLATE_PATH . '/front';
?>
<!doctype html>
<html lang="<?=LANGUAGE_ID?>">
<head>
    <?$obAsset->addString('<link rel="icon" href="' . MEDIA_DIR . '/favicon.ico"/>')?>
    <?$obAsset->addString('<meta name="viewport" content="width=device-width,initial-scale=1"/>')?>
    <?$obAsset->addString('<meta name="theme-color" content="#000000"/>')?>
    <?$obAsset->addString('<link rel="apple-touch-icon" href="' . MEDIA_DIR . '/logo192.png"/>')?>
    <?$obAsset->addString('<link rel="manifest" href="' . MEDIA_DIR . '/manifest.json"/>')?>
    <title><?$APPLICATION->ShowTitle();?></title>
    <?$obAsset->addCss(MEDIA_DIR . '/static/css/2.935bc9c2.chunk.css')?>
    <?$obAsset->addCss(MEDIA_DIR . '/static/css/main.6fcced4d.chunk.css')?>
    <?$APPLICATION->ShowHead();?>
</head>
<body>
<?php
$APPLICATION->ShowPanel();
?>