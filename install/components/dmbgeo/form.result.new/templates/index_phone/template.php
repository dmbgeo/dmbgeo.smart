<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$signer = new \Bitrix\Main\Security\Sign\Signer;
$signedTemplate = $signer->sign($templateName, 'form.result.new');
$signedParams = $signer->sign(base64_encode(serialize($arParams)), 'form.result.new');
$obName = $arResult['arForm']['SID'] ?? "form";
$containerName = 'form_container_' . $obName;
$this->addExternalCss($templateFolder . '/phone-select/css/intlTelInput.min.css');

$url=$_SERVER['REQUEST_SCHEME']."://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

?>

<div id="<?= $containerName; ?>">
	<script src="https://code.jquery.com/jquery-latest.min.js"></script>
	<script src="<?= $templateFolder . '/phone-select/js/intlTelInput-jquery.min.js'; ?>"></script>
	<script src="<?= $templateFolder . '/mask.js'; ?>"></script>


	<!-- result-container -->


	<div class="b-quick-form">


		<?= $arResult['FORM_HEADER'] ?>

		<div class="input-group">

			<input type="text" required name="form_text_11" value="+995" class="form-control" placeholder="Введите ваш номер телефона">
			<button class="b-btn-send"><span class="b-txt-pc">Отправить</span><span class="b-txt-mob">Заказать звонок</span></button>
			<input type="hidden" name="web_form_submit" value="Сохранить">
			<input type="hidden" name="form_url_12" value="<?=$url?>">
			
		</div>
		</form>
		<? if ($arResult['isFormErrors'] == 'Y') : ?>
			<div class="errors-fld">

				<? foreach ($arResult['FORM_ERRORS'] as $error) : ?>
					<p> <?= $error; ?></p>
				<? endforeach; ?>
			</div>
		<? else : ?>
			<? if ($arResult['FORM_NOTE'] != '') : ?>
				<div class="success-fld">
					<p> <?= $arResult['FORM_NOTE']; ?></p>
				</div>
			<? endif; ?>
		<? endif; ?>
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
		$("input[name='form_text_11']").mask('+0000000000000000000000000');

		$("input[name='form_text_11']").intlTelInput({
			preferredCountries: ["ge", "ru", "ua", "by", "il"],
			nationalMode: false,
			formatOnDisplay: true,
		});

	});
</script>