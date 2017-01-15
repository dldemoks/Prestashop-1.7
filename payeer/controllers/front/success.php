<?php

class PayeerSuccessModuleFrontController extends ModuleFrontController
{
	public $ssl = true;
    
	public function initContent()
    {
        parent::initContent();
		
		$m_orderid = Tools::getValue('m_orderid');
		$order = new Order((int)$m_orderid);
		$customer = new Customer((int)$order->id_customer);

		Tools::redirectLink(__PS_BASE_URI__ . 'order-confirmation.php?key=' . $customer->secure_key . '&id_cart=' . $order->id_cart .
			'&id_module=' . $this->module->id . '&id_order=' . $order->id);
    }
}
