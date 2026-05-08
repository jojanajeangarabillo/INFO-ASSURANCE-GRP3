<?php

class PaymentController {
    private $secret_key = 'sk_test_ey8ELcnLA6uGacpP5Hnrpjj1';

    /**
     * Create a PayMongo Checkout Session
     * 
     * @param float $amount 
     * @param array $items 
     * @param string $description 
     * @return string|null 
     */
    public function createCheckoutSession($amount, $items, $description = "J3RS Order Payment") {
        $url = "https://api.paymongo.com/v1/checkout_sessions";

        // PayMongo expects amount in centavos
        $total_amount_centavos = round($amount * 100);

        $line_items = [];
        foreach ($items as $item) {
            $line_items[] = [
                'currency' => 'PHP',
                'amount' => round($item['price'] * 100),
                'description' => $item['name'],
                'name' => $item['name'],
                'quantity' => (int)$item['quantity']
            ];
        }

        $payload = [
            'data' => [
                'attributes' => [
                    'send_email_receipt' => true,
                    'show_description' => true,
                    'show_line_items' => true,
                    'description' => $description,
                    'line_items' => $line_items,
                    'payment_method_types' => ['gcash', 'paymaya', 'grab_pay', 'card'],
                    'success_url' => 'http://localhost/INFO-ASSURANCE-GRP3/payment_success.php',

                    'cancel_url' => 'http://localhost/INFO-ASSURANCE-GRP3/customer_cart.php'
                ]
            ]
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_ENCODING, '');
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'Authorization: Basic ' . base64_encode($this->secret_key . ':'),
            'Content-Type: application/json'
        ]);

        $response = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) {
            error_log("PayMongo CURL Error: " . $err);
            return null;
        }

        $result = json_decode($response, true);
        if (isset($result['data']['attributes']['checkout_url'])) {
            return $result['data']['attributes']['checkout_url'];
        } else {
            error_log("PayMongo API Error: " . $response);
            return null;
        }
    }
}
