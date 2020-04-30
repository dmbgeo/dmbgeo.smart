<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$signer = new \Bitrix\Main\Security\Sign\Signer;
$signedTemplate = $signer->sign($templateName, 'form.result.new');
$signedParams = $signer->sign(base64_encode(serialize($arParams)), 'form.result.new');
$obName = $arResult['arForm']['SID'] ?? "form";
$containerName = 'form_container_' . $obName;
$this->addExternalCss($templateFolder . '/phone-select/css/intlTelInput.min.css');
// CJSCore::Init(array("jquery"));
?>

<div id="<?= $containerName; ?>">
	<script src="https://code.jquery.com/jquery-latest.min.js"></script>
	<script src="<?= $templateFolder . '/phone-select/js/intlTelInput-jquery.min.js'; ?>"></script>
	<script src="<?= $templateFolder . '/mask.js'; ?>"></script>


	<!-- result-container -->


	<div class="b-quick-form">
		<? if ($arResult['isFormErrors'] == 'Y') : ?>
			<? foreach ($arResult['FORM_ERRORS'] as $error) : ?>
				<p style="color:red;"> <?= $error; ?></p>
			<? endforeach; ?>
		<? else : ?>
			<p style="color:green;"> <?= $arResult['FORM_NOTE']; ?></p>
		<? endif; ?>

		<?= $arResult['FORM_HEADER'] ?>
		<div class="input-group">
			<input type="text" required name="phone" value="+995" class="form-control" placeholder="Введите ваш номер телефона">
			<button class="b-btn-send"><span class="b-txt-pc">Отправить</span><span class="b-txt-mob">Заказать звонок</span></button>
			<input type="hidden" name="web_form_submit" value="Сохранить">
		</div>
		</form>
	</div>



	<!-- result-container -->
</div>
<style>
	.iti__country-list {
		z-index: 100 !important;
	}

	.iti__country-name {
		color: black;
	}

	.b-quick-form .input-group .form-control {

		padding: 0 18px 0 50px;

	}
</style>
<script>
	$(document).ready(function() {
		$("input[name='phone']").mask('+0000000000000000000000000');
		
		$("input[name='phone']").intlTelInput({
			preferredCountries: ["ge", "ru", "ua", "by", "il"],
			nationalMode:false,
			formatOnDisplay:true,
		});

	});
</script>