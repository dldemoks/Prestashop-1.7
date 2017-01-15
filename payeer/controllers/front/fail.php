<?php

class PayeerFailModuleFrontController extends ModuleFrontController
{
	public $ssl = true;
    
	public function initContent()
    {
        parent::initContent();
		
		$m_orderid = Tools::getValue('m_orderid');
		$order = new Order((int)$m_orderid);
		$history = new OrderHistory();
		$history->id_order = (int)$m_orderid;
		$orderStatusId = Configuration::get('PS_OS_CANCELED');
		$history->changeIdOrderState((int)($orderStatusId), $order);
		
		Tools::redirectLink(__PS_BASE_URI__ . 'order-detail.php?id_order=' . $order->id);
    }
}
