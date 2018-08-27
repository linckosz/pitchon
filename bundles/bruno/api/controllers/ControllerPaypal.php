<?php

//https://www.onlinequizcreator.com/pricing/item7640

namespace bundles\bruno\api\controllers;

use \libs\Json;
use \libs\Controller;
use \bundles\bruno\data\models\ModelBruno;
use \bundles\bruno\data\Subscription;
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

class ControllerPaypal extends Controller {

	public function billing_post(){
		$app = ModelBruno::getApp();
		$data = ModelBruno::getData();
\libs\Watch::php($data, '$var', __FILE__, __LINE__, false, false, true);

		//Verify first the accuracy of the payment information given to avoid any hack
		$json = new \stdClass;
		$gofail = true;
		if(
			   isset($data->subscription_id)
			&& isset($data->subscription_md5)
			&& isset($data->subscription_plan)
			&& isset($data->subscription_plan_duration)
			&& isset($data->subscription_promocode)
			&& isset($data->subscription_currency)
			&& isset($data->subscription_total_price)
			&& $subscription = Subscription::Where('id', $data->subscription_id)->where('md5', $data->subscription_md5)->first()
		){
			//Default plan
			$plan_title = $app->trans->getBRUT('api', 20, 1); //Starter
			$data->subscription_plan = 1;
			$plan_price = $subscription->starter;
			if($data->subscription_plan == 2){
				$plan_title = $app->trans->getBRUT('api', 20, 2); //Standard
				$plan_price = $subscription->standrad;
			} else if($data->subscription_plan == 2){
				$plan_title = $app->trans->getBRUT('api', 20, 3); //Premium
				$plan_price = $subscription->premium;
			}

			//Defaut duration in months
			$plan_months = 1;
			$plan_discount = 1;
			if($data->subscription_plan_duration == 2){
				$plan_months = 3;
				$plan_discount = 1;
			} else if($data->subscription_plan_duration == 3){
				$plan_months = 6;
				$plan_discount = 0.95;
			} else if($data->subscription_plan_duration == 4){
				$plan_months = 12;
				$plan_discount = 0.85;
			} else if($data->subscription_plan_duration == 5){
				$plan_months = 24;
				$plan_discount = 0.70;
			}

			$json->host_id = null;
			if(strlen($data->subscription_promocode) > 0){
				if($promocode = Promocode::getItem($data->subscription_promocode)){
					if(is_object($promocode) && $promocode->discount > 0 && $promocode->discount <= 100){
						$plan_discount = $plan_discount * (100-intval($promocode->discount))/100;
						$json->host_id = $promocode->user_id;
					}
				}
			}

			//This operation must be the same as subscription.js
			$plan_price = floor($plan_months * intval($plan_price) * $plan_discount);

			if($plan_price > 0){
				//Give a gap of 5% for security, but the result should be very acccurate
				$gap = abs(1 - (intval($data->subscription_total_price) / $plan_price));
				if($gap <= 0.05){
					$gofail = false;
					$json->user_id = $app->bruno->data['user_id'];
					$json->plan = $data->subscription_plan;
					$json->plan_duration = $data->subscription_plan_duration;
					$json->plan_at = null; //toto => to calculate with what's left
				}
			}

		}
		
			

		if($gofail){
			$json = new \stdClass;
			goto failed;
		}

		$payment = new Payment;
		$apiContext = new ApiContext(
			new OAuthTokenCredential(
				$app->bruno->data['paypal_client'],
				$app->bruno->data['paypal_secret']
			)
		);
		$base_url = $_SERVER['REQUEST_SCHEME'].'://app.'.$app->bruno->domain.'/api/paypal/';
		$payment->setIntent('sale');
		$redirectUrls = new RedirectUrls;
		$redirectUrls->setReturnUrl($base_url.'pay');
		$redirectUrls->setCancelUrl($base_url.'fail');
		$payment->setRedirectUrls($redirectUrls);
		$payer = new Payer;
		$payer->setPaymentMethod('paypal');
		$payment->setPayer($payer);
		$itemList = new ItemList;

		$item = new Item;
		$item->setQuantity(1);
		$item->setCurrency($data->subscription_currency);

		$item->setName($plan_title);
		$item->setPrice($data->subscription_total_price);
		$itemList->addItem($item);
		
		$amount = new Amount;
		$amount->setTotal($data->subscription_total_price); //9.00
		$amount->setCurrency($data->subscription_currency); //EUR
		
		$transaction = new Transaction;
		$transaction->setItemList($itemList);
		$transaction->setDescription($plan_title);
		$transaction->setAmount($amount);
		$transaction->setCustom(json_encode($json));
		$payment->setTransactions([$transaction]);

		try {
			$payment->create($apiContext);
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
		$app = ModelBruno::getApp();
		$data = ModelBruno::getData();
		
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
			$msg = array('msg' => 'Paid');
			(new Json($msg))->render();
			return exit(0);
		} catch(PayPalConnectionException $e){
			\libs\Watch::php(\error\getTraceAsString($e, 10), 'PayPal Exception: '.$e->getLine().' / '.$e->getMessage()." /\n ".$e->getCode()." /\n ".$e->getData(), __FILE__, __LINE__, true);
			goto failed;
		}

		failed:
		$msg = array('msg' => 'Failed');
		(new Json($msg))->render();
		return exit(0);
	}

	public function fail_post(){
		$msg = array('msg' => 'Failed');
		(new Json($msg))->render();
		return exit(0);
	}

}
