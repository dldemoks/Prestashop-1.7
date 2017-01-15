<?php

class PayeerPaymentModuleFrontController extends ModuleFrontController
{
	protected $templateName = 'module:payeer/views/templates/front/payeer_form.tpl';
	public $ssl = true;
    
	public function initContent()
    {
        parent::initContent();
		
		$cart = $this->context->cart;
		
		if ($ordernumber = Order::getOrderByCartId($cart->id))
		{
			$order = new Order((int)$ordernumber);
			
			if ($order->hasBeenPaid())
			{
				Tools::redirectLink(__PS_BASE_URI__ . 'order-confirmation.php?key=' . $order->secure_key . '&id_cart=' . $order->id_cart .
					'&id_module=' . $this->module->id . '&id_order=' . $order->id);
				return;
			}
		}
		
		$currency = new Currency($cart->id_currency);
		$m_url = Configuration::get('merchant_url');
		$m_shop = Configuration::get('merchant_id');
		$m_key = Configuration::get('secret_key');
		$m_orderid = (int)$cart->id;
		$m_amount = number_format($cart->getOrderTotal(true, Cart::BOTH), 2, '.', '');
		$m_curr = $currency->iso_code == 'RUR' ? 'RUB' : $currency->iso_code;
		$m_desc = base64_encode($this->module->l('Payment of order', 'payment') . ' ' . $m_orderid);
		$m_lang = $this->getLang();

		$arHash = array(
			$m_shop,
			$m_orderid,
			$m_amount,
			$m_curr,
			$m_desc,
			$m_key
		);
		$sign = Tools::strtoupper(hash('sha256', implode(":", $arHash)));
		
		$this->context->smarty->assign(array(
			'm_url' => $m_url,
			'm_shop' => $m_shop,
			'm_orderid' => $m_orderid,
			'm_amount' => $m_amount,
			'm_curr' => $m_curr,
			'm_desc' => $m_desc,
			'm_sign' => $sign,
			'm_lang' => $m_lang
		));
		
		$customer = new Customer((int)$cart->id_customer);
        $total = $cart->getOrderTotal(true, Cart::BOTH);
        $this->module->validateOrder($m_orderid, Configuration::get('PS_OS_CHEQUE'), $total, $this->module->displayName, null, array(), null, false, $customer->secure_key);
		
        $this->setTemplate($this->templateName);
    }
	
	private function getLang()
    {
        $cart = $this->context->cart;
        $language = new Language((int)$cart->id_lang);
        $languageCode = $language->iso_code;
		
		if ($languageCode == 'ru')
		{
			return 'ru';
		}
		else
		{
			return 'en';
		}
    }
}