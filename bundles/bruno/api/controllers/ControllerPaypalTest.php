<?php

namespace bundles\bruno\api\controllers;

use \libs\Json;
use \libs\Controller;
use \bundles\bruno\data\models\ModelBruno;
use PayPal\Api\Payment;
use PayPal\Api\Payer;
use PayPal\Api\RedirectUrls;
use PayPal\Api\ItemList;
use PayPal\Api\Item;
use PayPal\Api\Amount;
use PayPal\Api\Detail;
use PayPal\Api\Transaction;
use PayPal\Api\PaymentExecution;
use PayPal\Rest\ApiContext;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Exception\PayPalConnectionException;

class ControllerPaypalTest extends Controller {

	public function payment_post(){
		$data = ModelBruno::getData();
		$app = ModelBruno::getApp();

\libs\Watch::php($data, '$var', __FILE__, __LINE__, false, false, true);
goto failed;
		
		$payment = new Payment;
		$apiContext = new ApiContext(
			new OAuthTokenCredential(
				$app->bruno->data['paypal_client'],
				$app->bruno->data['paypal_secret']
			)
		);
		
		$payment->setIntent('sale');
		$redirectUrls = new RedirectUrls;
		$redirectUrls->setReturnUrl('https://app.pitchon.net/api/paypal/pay');
		$redirectUrls->setCancelUrl('https://app.pitchon.net/api/paypal/fail');
		$payment->setRedirectUrls($redirectUrls);
		$payer = new Payer;
		$payer->setPaymentMethod('paypal');
		$payment->setPayer($payer);
		$itemList = new ItemList;

		$item = new Item;
		$item->setQuantity(1);
		$item->setCurrency('EUR');

		$item->setName('Standard2');
		
		
		$item->setPrice('9.00');
		$itemList->addItem($item);
		
		$amount = new Amount;
		$amount->setTotal('9.00');
		$amount->setCurrency('EUR');
		
		$transaction = new Transaction;
		$transaction->setItemList($itemList);
		$transaction->setDescription('Something to subscribe');
		$transaction->setAmount($amount);
		$transaction->setCustom($data);
		$payment->setTransactions([$transaction]);
		$payment->create($apiContext);

		try {
			$msg = array('id' => $payment->getId());
			(new Json($msg))->render();
			return exit(0);
		} catch(PayPalConnectionException $e){
			\libs\Watch::php(\error\getTraceAsString($e, 10), 'PayPal Exception: '.$e->getLine().' / '.$e->getMessage().' / '.$e->getData(), __FILE__, __LINE__, true);
		}

		failed:
		$msg = array('msg' => 'Failed');
		(new Json($msg))->render();
		return exit(0);
	}

	public function pay_post(){
		$data = ModelBruno::getData();
		$app = ModelBruno::getApp();
		
		$apiContext = new ApiContext(
			new OAuthTokenCredential(
				$app->bruno->data['paypal_client'],
				$app->bruno->data['paypal_secret']
			)
		);
		$payment = Payment::get($data->paymentID, $apiContext);

		$execution = new PaymentExecution;
		$execution->setPayerId($data->payerID);
		$execution->setTransactions($payment->getTransactions());

		try {
			$payment->execute($execution, $apiContext);
			\libs\Watch::php($payment, '$payment', __FILE__, __LINE__, false, false, true);
		} catch(PayPalConnectionException $e){
			\libs\Watch::php(\error\getTraceAsString($e, 10), 'PayPal Exception: '.$e->getLine().' / '.$e->getMessage().' / '.$e->getData(), __FILE__, __LINE__, true);
		}

		$msg = array('msg' => 'Paid');
		(new Json($msg))->render();
		return exit(0);
	}

	public function fail_post(){
		$msg = array('msg' => 'Failed');
		(new Json($msg))->render();
		return exit(0);
	}

}
