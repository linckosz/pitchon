<?php

//https://www.onlinequizcreator.com/pricing/item7640

namespace bundles\bruno\api\controllers;

use \libs\Json;
use \libs\Controller;
use \bundles\bruno\data\models\ModelBruno;
use \bundles\bruno\data\models\Subscription;
use \bundles\bruno\data\models\Subscribed;
use \bundles\bruno\data\models\Bank;
use \bundles\bruno\data\models\data\User;
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
use Illuminate\Database\Capsule\Manager as Capsule;

class ControllerPaypal extends Controller {

	public function billing_post(){
		$app = ModelBruno::getApp();
		$data = ModelBruno::getData();

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
			&& $subscription_selected = Subscription::Where('id', $data->subscription_id)->where('md5', $data->subscription_md5)->first()
		){
			//Default plan
			$plan_title = $app->trans->getBRUT('api', 20, 1); //Starter
			$plan_price = $subscription_selected->starter;
			if($data->subscription_plan == 2){
				$plan_title = $app->trans->getBRUT('api', 20, 2); //Standard
				$plan_price = $subscription_selected->standard;
			} else if($data->subscription_plan == 3){
				$plan_title = $app->trans->getBRUT('api', 20, 3); //Premium
				$plan_price = $subscription_selected->premium;
			} else {
				$data->subscription_plan = 1; //Starter
			}

			$plan_price_original = $plan_price;

			//Defaut duration in months
			$plan_months = 1;
			$plan_discount = 1;
			$plan_title_duration = '+'.$app->trans->getBRUT('api', 20, 26); //1 month
			if($data->subscription_plan_duration == 2){
				$plan_months = 3;
				$plan_discount = 1;
				$plan_title_duration = '+'.$app->trans->getBRUT('api', 20, 27); //3 months
			} else if($data->subscription_plan_duration == 3){
				$plan_months = 6;
				$plan_discount = 0.95;
				$plan_title_duration = '+'.$app->trans->getBRUT('api', 20, 28); //6 months
			} else if($data->subscription_plan_duration == 4){
				$plan_months = 12;
				$plan_discount = 0.85;
				$plan_title_duration = '+'.$app->trans->getBRUT('api', 20, 29); //1 year
			} else if($data->subscription_plan_duration == 5){
				$plan_months = 24;
				$plan_discount = 0.70;
				$plan_title_duration = '+'.$app->trans->getBRUT('api', 20, 30); //2 years
			}

			$json->host_id = null;
			if(strlen($data->subscription_promocode) > 0){
				//Check first from Promotion code
				if($promocode = Promocode::getItem($data->subscription_promocode)){
					if(is_object($promocode) && $promocode->discount > 0 && $promocode->discount <= 100){
						$plan_discount = $plan_discount * (100-intval($promocode->discount))/100;
						$json->host_id = $promocode->user_id;
						$json->promocode = $data->subscription_promocode;
					}
				}
				//Check then from sales (10% by default)
				if($sales = User::Where('promocode', $promocode)->first()){
					$plan_discount = $plan_discount * 0.90;
					$json->host_id = $sales->id;
					$json->promocode = $data->subscription_promocode;
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
					$json->plan_at = null;
					$json->amount = $data->subscription_total_price;
					$json->amount_real = $plan_price;
					$json->subscription_id = $subscription_selected->id;

					//EXPIRATION DATE CALULATION
					//It takes in consideration the remaining time and its convertion if the user change the plan
					$user = User::getUser();
					$plan = $user->plan;
					$plan_expire = $user->plan_at;
					$now = ModelBruno::getMStime();
					$expiration = new \DateTime();
					$subscription = Subscription::getLatest();
					$subscription_price = $subscription->starter;
					if($plan==2){
						$subscription_price = $subscription->standard;
					} else if($plan==3){
						$subscription_price = $subscription->premium;
					}
					if($plan_expire > $now && $plan){
						//If currently active we postpone the plan
						if($plan != $data->subscription_plan){
							//If the plan is different, we need to extrapolate an updated expiration date based on remaining days and plan price
							$ms_diff = $plan_expire - $now; //Time difference in ms
							if($subscription_price > 0){
								$ms_diff = floor($ms_diff * $subscription_price / $plan_price_original);
								$new_plan_expire = $now + $ms_diff;
							}
						} else {
							$new_plan_expire = $plan_expire;
						}
						$expiration->setTimestamp($new_plan_expire/1000);
					}
					//Add extra expiration
					$expiration->modify('+'.$plan_months.' months');
					$json->plan_at = $expiration->format('U') * 1000;
					//Convert $expiration in veral expretion
					$plan_title_duration .= ' - '.$app->trans->getBRUT('api', 20, 31, array('expiration' => $expiration->format('M j, Y'))).' - '.$app->trans->getBRUT('api', 20, 32, array('account' => $user->email)); //New expiration date: @@expiration~~ / account: @@account~~
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
		$transaction->setDescription($plan_title.' - '.$plan_title_duration);
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
		$msg = array('msg' => 'Failed', true);
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
			$json = json_decode($payment->getTransactions()[0]->getCustom());

			\libs\Watch::php($json, '$json', __FILE__, __LINE__, false, false, true);
			
			$db = Capsule::connection('data');
			$db->beginTransaction();
			$committed = false;
			try {
				$user = User::getUser();
				if(isset($json->plan) && isset($json->plan_duration) && isset($json->plan_at) && isset($json->user_id) && $json->user_id==$user->id){

					//Update User
					$user->plan = $json->plan;
					$user->plan_duration = $json->plan_duration;
					$user->plan_at = $json->plan_at;
					$user->save();

					//Record subscription status
					if(isset($json->amount) && isset($json->amount_real) && isset($json->subscription_id)){
						$subscribed = new Subscribed();
						$subscribed->user_id = $user->id;
						$subscribed->amount = $json->amount;
						$subscribed->amount_real = $json->amount_real;
						if(isset($json->promocode)){
							$subscribed->promocode = $json->promocode;
						}
						$subscribed->plan = $json->plan;
						$subscribed->subscription_id = $json->subscription_id;
						$subscribed->save();
					}

					//Record bank status
					if(isset($json->amount) && isset($json->amount_real) && isset($json->subscription_id)){
						$bank = new Bank();
						$bank->guest_user_id = $user->id;
						$bank->eur = $json->amount_real;
						//Rules to record host_id
						// 1) Check if the promocode comes from a sales (already set before sending the payment, so we skip to #2)
						// 2) Check if the host_id is a sales
						// 3) Check if this is the first time a user subscribe from his host
						if($host = User::Where('id', $app->bruno->data['user_id'])->first()){
							//Check if it's a sales OR Check if already recorded once
							if($host->promocode || !Bank::Where('host_user_id', $host->id)->where('guest_user_id', $user->id)->first()){
								$bank->host_user_id = $json->host_id;
							}
						}
						$bank->save();
					}
				}
				$db->commit();
				$committed = true;
			} catch (\Exception $e){
				$committed = false;
				$db->rollback();
				goto failed;
			}

			$msg = array('msg' => 'Paid');
			(new Json($msg))->render();
			return exit(0);
		} catch(PayPalConnectionException $e){
			\libs\Watch::php(\error\getTraceAsString($e, 10), 'PayPal Exception: '.$e->getLine().' / '.$e->getMessage()." /\n ".$e->getCode()." /\n ".$e->getData(), __FILE__, __LINE__, true);
			goto failed;
		}

		failed:
		$msg = array('msg' => 'Failed', true);
		(new Json($msg))->render();
		return exit(0);
	}

	public function fail_post(){
		$msg = array('msg' => 'Failed', true);
		(new Json($msg))->render();
		return exit(0);
	}

}
