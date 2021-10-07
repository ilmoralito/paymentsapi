<?php

namespace App\Http\Controllers;

use GlobalPayments\Api\Entities\Exceptions\ApiException;
use GlobalPayments\Api\Entities\Exceptions\BuilderException;
use GlobalPayments\Api\Entities\Exceptions\ConfigurationException;
use GlobalPayments\Api\Entities\Exceptions\GatewayException;
use GlobalPayments\Api\Entities\Exceptions\UnsupportedTransactionException;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\ServiceConfigs\Gateways\PorticoConfig;
use GlobalPayments\Api\ServicesContainer;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct()
    {
        // TODO
    }

    public function payment(Request $request)
    {
        $payload = $request->json()->all();

        // credit card information
        $number = $payload['number'];
        $card_holder_name = $payload['card_holder_name'];
        $expire_month = $payload['expire_month'];
        $expire_year = $payload['expire_year'];
        $cvn = $payload['cvn'];

        // payment information
        $amount = $payload['amount'];
        $currency = $payload['currency'];
        $invoice_number = $payload['invoice_number'];

        // process credit card
        $config = new PorticoConfig();
        $config->secretApiKey = 'skapi_cert_MYl2AQAowiQAbLp5JesGKh7QFkcizOP2jcX9BrEMqQ';
        $config->serviceUrl = 'https://cert.api2.heartlandportico.com';

        ServicesContainer::configureService($config);

        $card = new CreditCardData();
        $card->number = $number;
        $card->cardHolderName = $card_holder_name;
        $card->expMonth = $expire_month;
        $card->expYear = $expire_year;
        $card->cvn = $cvn;

        try {
            $response = $card
                ->charge($amount)
                ->withCurrency($currency)
                ->withInvoiceNumber($invoice_number)
                ->withAllowDuplicates(true)
                ->execute();

            return [
                'transaction_id' => $response->transactionId,
                'response_code' => $response->responseCode,
                'response_message' => $response->responseMessage
            ];
        } catch (BuilderException | ConfigurationException | GatewayException | UnsupportedTransactionException | ApiException $exception) {
            return ['failure' => $exception->getMessage()];
        }
    }
}
