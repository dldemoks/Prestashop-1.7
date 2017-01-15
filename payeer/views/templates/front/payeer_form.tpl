<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset=utf-8 />
		<title>Payment</title>
	</head>
	<body>
		<form name="payeer_form" class="prestalab_ru" action="{$m_url|escape:'html':'UTF-8'}" method="get">
			<input type="hidden" name="m_shop" value="{$m_shop|escape:'html':'UTF-8'}"/>
			<input type="hidden" name="m_orderid" value="{$m_orderid|escape:'html':'UTF-8'}"/>
			<input type="hidden" name="m_amount" value="{$m_amount|escape:'html':'UTF-8'}"/>
			<input type="hidden" name="m_curr" value="{$m_curr|escape:'html':'UTF-8'}"/>
			<input type="hidden" name="m_desc" value="{$m_desc|escape:'html':'UTF-8'}"/>
			<input type="hidden" name="lang" value="{$m_lang|escape:'html':'UTF-8'}"/>
			<input type="hidden" name="m_sign" value="{$m_sign|escape:'html':'UTF-8'}"/>
		</form>
		<script>
			<!--
			 document.payeer_form.submit();
		 -->
		</script>
	</body>
</html>