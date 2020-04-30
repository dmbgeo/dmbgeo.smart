<?if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @global CMain $APPLICATION
 * @var array $arParams
 * @var array $arResult
 * @var CatalogSectionComponent $component
 * @var CBitrixComponentTemplate $this
 * @var string $templateName
 * @var string $componentPath
 */

$this->setFrameMode(true);

?>
<ul>
    <?if (!empty($arResult['ITEMS'])): ?>
        <?foreach ($arResult['ITEMS'] as $arItem): ?>
        <?if ($arItem['SELECTED']): ?>
            <li class="SELECTED"><?=$arItem['NAME']?></li>
        <?else: ?>
            <li><a href="<?=$arItem['DETAIL_PAGE_URL']?>"><?=$arItem['NAME']?></a></li>
        <?endif;?>
        <?endforeach;?>
    <?endif;?>
</ul>