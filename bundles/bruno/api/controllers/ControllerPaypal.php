<?php

//https://www.onlinequizcreator.com/pricing/item7640

namespace bundles\bruno\api\controllers;

use \libs\Json;
use \libs\Controller;
use \bundles\bruno\data\models\ModelBruno;
use PayPal\Rest\ApiContext;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Exception\PayPalConnectionException;

use PayPal\Api\ChargeModel;
use PayPal\Api\Currency;
use PayPal\Api\MerchantPreferences;
use PayPal\Api\PaymentDefinition;
use PayPal\Api\Plan;
use PayPal\Api\Patch;
use PayPal\Api\PatchRequest;
use PayPal\Common\PayPalModel;

use PayPal\Api\Agreement;
use PayPal\Api\Payer;
use PayPal\Api\ShippingAddress;

class ControllerPaypal extends Controller {

	public function billing_post(){
		$app = ModelBruno::getApp();
		$data = ModelBruno::getData();
		
		$apiContext = new ApiContext(
			new OAuthTokenCredential(
				$app->bruno->data['paypal_client'],
				$app->bruno->data['paypal_secret']
			)
		);
		
		// Create a new billing plan
		$plan = new Plan();
		$plan
			->setName('T-Shirt of the Month Club Plan')
			->setDescription('Template creation.')
			->setType('fixed');

		// Set billing plan definitions
		$paymentDefinition = new PaymentDefinition();
		$paymentDefinition
			->setName('Regular Payments')
			->setType('REGULAR')
			->setFrequency('Month')
			->setFrequencyInterval('2')
			->setCycles('12')
			->setAmount(new Currency(array('value' => 100, 'currency' => 'USD')));

		// Set charge models
		$chargeModel = new ChargeModel();
		$chargeModel
			->setType('SHIPPING')
			->setAmount(new Currency(array('value' => 10, 'currency' => 'USD')));
		$paymentDefinition->setChargeModels(array($chargeModel));

//https://stackoverflow.com/questions/51424433/paypal-php-sdk-notifyurl-is-not-a-fully-qualified-url-error/51431104
		// Set merchant preferences
		$merchantPreferences = new MerchantPreferences();
		$merchantPreferences
			->setReturnUrl('https://app.pitchon.net/api/paypal/pay')
			->setCancelUrl('https://app.pitchon.net/api/paypal/fail')
			->setNotifyUrl('https://app.pitchon.net/api/paypal/notify')
			->setAutoBillAmount('yes')
			->setInitialFailAmountAction('CONTINUE')
			->setMaxFailAttempts('0')
			->setSetupFee(new Currency(array('value' => 1, 'currency' => 'USD')));

		$plan->setPaymentDefinitions(array($paymentDefinition));
		$plan->setMerchantPreferences($merchantPreferences);

		$plan->create($apiContext);
		\libs\Watch::php($plan, '$plan', __FILE__, __LINE__, false, false, true);

		//create plan
		try {
			$createdPlan = $plan->create($apiContext);
			\libs\Watch::php($createdPlan, '$createdPlan', __FILE__, __LINE__, false, false, true);

			try {
				$patch = new Patch();
				$value = new PayPalModel('{"state":"ACTIVE"}');
				$patch
					->setOp('replace')
					->setPath('/')
					->setValue($value);
				$patchRequest = new PatchRequest();
				$patchRequest->addPatch($patch);
				$createdPlan->update($patchRequest, $apiContext);
				$plan = Plan::get($createdPlan->getId(), $apiContext);

				// Output plan id
				$msg = array('id' => $plan->getId());
				(new Json($msg))->render();
				return exit(0);
			} catch (PayPal\Exception\PayPalConnectionException $e) {
				echo $ex->getCode();
				echo $ex->getData();
				\libs\Watch::php(\error\getTraceAsString($e, 10), 'PayPal Exception: '.$e->getLine().' / '.$e->getMessage()." /\n ".$e->getCode()." /\n ".$e->getData(), __FILE__, __LINE__, true);
				goto failed;
			} catch (Exception $e) {
				\libs\Watch::php(\error\getTraceAsString($e, 10), 'PayPal Exception: '.$e->getLine().' / '.$e->getMessage()." /\n ".$e->getCode()." /\n ".$e->getData(), __FILE__, __LINE__, true);
				goto failed;
			}
		} catch (PayPal\Exception\PayPalConnectionException $e) {
			\libs\Watch::php(\error\getTraceAsString($e, 10), 'PayPal Exception: '.$e->getLine().' / '.$e->getMessage()." /\n ".$e->getCode()." /\n ".$e->getData(), __FILE__, __LINE__, true);
			goto failed;
		} catch (Exception $e) {
			\libs\Watch::php(\error\getTraceAsString($e, 10), 'PayPal Exception: '.$e->getLine().' / '.$e->getMessage()." /\n ".$e->getCode()." /\n ".$e->getData(), __FILE__, __LINE__, true);
			goto failed;
		}

		/*
		// Create new agreement
		$agreement = new Agreement();
		$agreement
			->setName('Base Agreement')
			->setDescription('Basic Agreement')
			->setStartDate('2019-06-17T9:45:04Z')
			->setPlan($plan);

		// Add payer type
		$payer = new Payer();
		$payer->setPaymentMethod('paypal');
		$agreement->setPayer($payer);

		try {
			// Create agreement
			$agreement = $agreement->create($apiContext);
			// Output agreement id
			$msg = array('id' => $agreement->getId());
			(new Json($msg))->render();
			return exit(0);
		} catch (PayPal\Exception\PayPalConnectionException $e) {
			\libs\Watch::php(\error\getTraceAsString($e, 10), 'PayPal Exception: '.$e->getLine().' / '.$e->getMessage()." /\n ".$e->getCode()." /\n ".$e->getData(), __FILE__, __LINE__, true);
			goto failed;
		} catch (Exception $e) {
			\libs\Watch::php(\error\getTraceAsString($e, 10), 'PayPal Exception: '.$e->getLine().' / '.$e->getMessage()." /\n ".$e->getCode()." /\n ".$e->getData(), __FILE__, __LINE__, true);
			goto failed;
		}
		*/

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
		} catch(PayPalConnectionException $e){
			\libs\Watch::php(\error\getTraceAsString($e, 10), 'PayPal Exception: '.$e->getLine().' / '.$e->getMessage()." /\n ".$e->getCode()." /\n ".$e->getData(), __FILE__, __LINE__, true);
			goto failed;
		}

		failed:
		$msg = array('msg' => 'Paid');
		(new Json($msg))->render();
		return exit(0);
	}

	public function fail_post(){
		$msg = array('msg' => 'Failed');
		(new Json($msg))->render();
		return exit(0);
	}

	public function notify_post(){
		$msg = array('msg' => 'Notified');
		(new Json($msg))->render();
		return exit(0);
	}

	public function _get(){
		$msg = array('msg' => 'ok');
		(new Json($msg))->render();
		return exit(0);
	}

}
