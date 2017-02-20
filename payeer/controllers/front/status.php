<?php

class PayeerStatusModuleFrontController extends ModuleFrontController
{
	protected $templateName = 'module:payeer/views/templates/front/payeer_status.tpl';
	public $display_header = false;
	public $display_column_left = false;
	public $display_column_right = false;
	public $display_footer = false;
	public $ssl = true;
	
	public function postProcess()
	{
		parent::postProcess();
		
		if (Tools::getValue('m_operation_id') && Tools::getValue('m_sign'))
		{
			$m_orderid = Tools::getValue('m_orderid');
			$payeer = $this->module;
			$status = 'success';
			$err = false;
			$message = '';
			
			// запись логов

			$log_text = 
				"--------------------------------------------------------\n" .
				"operation id       " . Tools::getValue('m_operation_id') . "\n" .
				"operation ps       " . Tools::getValue('m_operation_ps') . "\n" .
				"operation date     " . Tools::getValue('m_operation_date') . "\n" .
				"operation pay date " . Tools::getValue('m_operation_pay_date') . "\n" .
				"shop               " . Tools::getValue('m_shop') . "\n" .
				"order id           " . Tools::getValue('m_orderid') . "\n" .
				"amount             " . Tools::getValue('m_amount') . "\n" .
				"currency           " . Tools::getValue('m_curr') . "\n" .
				"description        " . base64_decode(Tools::getValue('m_desc')) . "\n" .
				"status             " . Tools::getValue('m_status') . "\n" .
				"sign               " . Tools::getValue('m_sign') . "\n\n";
			
			$log_file = Configuration::get('payeer_log');
			
			if (!empty($log_file))
			{
				file_put_contents($_SERVER['DOCUMENT_ROOT'] . $log_file, $log_text, FILE_APPEND);
			}

			// проверка цифровой подписи и ip

			$sign_hash = strtoupper(hash('sha256', implode(":", array(
				Tools::getValue('m_operation_id'),
				Tools::getValue('m_operation_ps'),
				Tools::getValue('m_operation_date'),
				Tools::getValue('m_operation_pay_date'),
				Tools::getValue('m_shop'),
				Tools::getValue('m_orderid'),
				Tools::getValue('m_amount'),
				Tools::getValue('m_curr'),
				Tools::getValue('m_desc'),
				Tools::getValue('m_status'),
				Configuration::get('secret_key')
			))));
			
			$valid_ip = true;
			$sIP = str_replace(' ', '', Configuration::get('ip_filter'));
			
			if (!empty($sIP))
			{
				$arrIP = explode('.', $_SERVER['REMOTE_ADDR']);
				if (!preg_match('/(^|,)(' . $arrIP[0] . '|\*{1})(\.)' .
				'(' . $arrIP[1] . '|\*{1})(\.)' .
				'(' . $arrIP[2] . '|\*{1})(\.)' .
				'(' . $arrIP[3] . '|\*{1})($|,)/', $sIP))
				{
					$valid_ip = false;
				}
			}
			
			if (!$valid_ip)
			{
				$message .= $payeer->l(' - the ip address of the server is not trusted', 'status') . "\n" .
				$payeer->l('   trusted ip: ', 'status') . $sIP . "\n" .
				$payeer->l('   the ip of the current server: ', 'status') . $_SERVER['REMOTE_ADDR'] . "\n";
				$err = true;
			}

			if (Tools::getValue('m_sign') != $sign_hash)
			{
				$message .= $payeer->l(' - do not match the digital signature', 'status') . "\n";
				$err = true;
			}

			if (!$err)
			{
				// загрузка заказа
				
				$order = new Order((int)$m_orderid);
				$order_curr = new Currency($order->id_currency);
				$order_curr = $order_curr->iso_code == 'RUR' ? 'RUB' : $order_curr->iso_code;
				$order_amount = number_format($order->total_paid, 2, '.', '');

				// проверка суммы и валюты
			
				if (Tools::getValue('m_amount') != $order_amount)
				{
					$message .= $payeer->l(' - wrong amount', 'status') . "\n";
					$err = true;
				}

				if (Tools::getValue('m_curr') != $order_curr)
				{
					$message .= $payeer->l(' - wrong currency', 'status') . "\n";
					$err = true;
				}

				// проверка статуса
				
				if (!$err)
				{
					$history = new OrderHistory();
					$history->id_order = (int)$m_orderid;
					
					switch (Tools::getValue('m_status'))
					{
						case 'success':
							
							if ($order->valid == 0)
							{
								$orderStatusId = Configuration::get('PS_OS_PAYMENT');
								$history->changeIdOrderState((int)($orderStatusId), $order);
							}
							
							break;
							
						default:
						
							$message .= $payeer->l(' - the payment status is not success', 'status') . "\n";
							$err = true;
							
							if ($order->valid == 0)
							{
								$orderStatusId = Configuration::get('PS_OS_ERROR');
								$history->changeIdOrderState((int)($orderStatusId), $order);
							}
							
							break;
					}
					
					
				}
			}
			
			if ($err)
			{
				$to = Configuration::get('email_error');

				if (!empty($to))
				{
					$message = $payeer->l('Failed to make the payment through Payeer for the following reasons:', 'status') . "\n\n" . $message . "\n" . $log_text;
					$headers = "From: no-reply@" . $_SERVER['HTTP_HOST'] . "\r\n" . 
					"Content-type: text/plain; charset=utf-8 \r\n";
					mail($to, $payeer->l('Error payment', 'status'), $message, $headers);
				}
				
				$status = 'error';
			}
			
			$this->context->smarty->assign(array(
				'm_orderid' => $m_orderid,
				'status' => $status
			));
			
			$this->setTemplate($this->templateName);
		}
	}
}