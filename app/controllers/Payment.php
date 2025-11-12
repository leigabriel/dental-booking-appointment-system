<?php
defined('PREVENT_DIRECT_ACCESS') or exit('No direct script access allowed');

class Payment extends Controller
{
    public function __construct()
    {
        parent::__construct();

        if (!$this->session->userdata('is_logged_in')) {
            $this->session->set_flashdata('error_message', 'You must be logged in to make a payment.');
            redirect('login');
        }

        $this->call->model(['AppointmentModel', 'ServiceModel']);
        $this->call->helper('url');
    }

    // GCash Payment via PayMongo Sandbox
    public function gcash($appointment_id)
    {
        $appointment = $this->AppointmentModel->find($appointment_id);
        
        if (!$appointment || $appointment['user_id'] != $this->session->userdata('user_id')) {
            $this->session->set_flashdata('error_message', 'Invalid appointment.');
            redirect('book');
        }

        // Get service details for amount
        $service = $this->ServiceModel->find($appointment['service_id']);
        $amount = $service['price'] * 100; // Convert to cents

        $api_key = 'sk_test_8qWkpzuyouUYnpp86PimNcKb';
        
        // Create payment source
        $data = [
            'data' => [
                'attributes' => [
                    'amount' => $amount,
                    'redirect' => [
                        'success' => site_url('payment/gcash/success/' . $appointment_id),
                        'failed' => site_url('payment/gcash/failed/' . $appointment_id)
                    ],
                    'type' => 'gcash',
                    'currency' => 'PHP'
                ]
            ]
        ];

        $ch = curl_init('https://api.paymongo.com/v1/sources');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Basic ' . base64_encode($api_key . ':')
        ]);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code === 200) {
            $result = json_decode($response, true);
            $checkout_url = $result['data']['attributes']['redirect']['checkout_url'] ?? null;
            
            if ($checkout_url) {
                // Store payment reference
                $this->AppointmentModel->update($appointment_id, [
                    'payment_reference' => $result['data']['id']
                ]);
                
                // Redirect to GCash payment page
                header('Location: ' . $checkout_url);
                exit;
            }
        }

        $this->session->set_flashdata('error_message', 'Failed to initiate GCash payment. Please try again or choose a different payment method.');
        redirect('book');
    }

    // GCash Payment Success
    public function gcash_success($appointment_id)
    {
        $appointment = $this->AppointmentModel->find($appointment_id);
        
        if ($appointment && $appointment['user_id'] == $this->session->userdata('user_id')) {
            $this->AppointmentModel->update($appointment_id, [
                'payment_status' => 'paid'
            ]);
            
            $this->session->set_flashdata('success_message', 'Payment successful! Your appointment is confirmed and awaiting admin approval.');
        }
        
        redirect('profile');
    }

    // GCash Payment Failed
    public function gcash_failed($appointment_id)
    {
        $this->session->set_flashdata('error_message', 'GCash payment was cancelled or failed. Your appointment is still pending. Please try again.');
        redirect('profile');
    }

    // PayPal Payment Sandbox
    public function paypal($appointment_id)
    {
        $appointment = $this->AppointmentModel->find($appointment_id);
        
        if (!$appointment || $appointment['user_id'] != $this->session->userdata('user_id')) {
            $this->session->set_flashdata('error_message', 'Invalid appointment.');
            redirect('book');
        }

        // Get service details for amount
        $service = $this->ServiceModel->find($appointment['service_id']);
        $amount = number_format($service['price'], 2, '.', '');

        // PayPal Sandbox credentials
        $client_id = 'AXLeP16hnIAruMJoRAdcopKesDd1i1vAyVJLnL_O8v5eLl4oXY0wcKl6SBxt4T5XWdj-lYa4FijryGSj';
        $secret = 'EN6F2ODnW7v7-unddDynhxEDeC0b_FMz5RdKKoZ-3a09qevTCnTSCIV6jPVoTGUzdPU00UOdEkg6lZDJ';
        $paypal_url = 'https://api-m.sandbox.paypal.com';

        // Get Access Token
        $ch = curl_init($paypal_url . '/v1/oauth2/token');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, 'grant_type=client_credentials');
        curl_setopt($ch, CURLOPT_USERPWD, $client_id . ':' . $secret);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        $result = json_decode($response, true);
        $access_token = $result['access_token'] ?? null;

        if (!$access_token) {
            $this->session->set_flashdata('error_message', 'Failed to connect to PayPal. Please try again.');
            redirect('book');
        }

        // Create PayPal Order
        $order_data = [
            'intent' => 'CAPTURE',
            'purchase_units' => [[
                'amount' => [
                    'currency_code' => 'PHP',
                    'value' => $amount
                ],
                'description' => 'Dental Appointment - ' . $service['name']
            ]],
            'application_context' => [
                'return_url' => site_url('payment/paypal/success/' . $appointment_id),
                'cancel_url' => site_url('payment/paypal/cancel/' . $appointment_id)
            ]
        ];

        $ch = curl_init($paypal_url . '/v2/checkout/orders');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($order_data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $access_token
        ]);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code === 201) {
            $result = json_decode($response, true);
            $approve_url = null;
            
            foreach ($result['links'] as $link) {
                if ($link['rel'] === 'approve') {
                    $approve_url = $link['href'];
                    break;
                }
            }
            
            if ($approve_url) {
                // Store PayPal order ID
                $this->AppointmentModel->update($appointment_id, [
                    'payment_reference' => $result['id']
                ]);
                
                header('Location: ' . $approve_url);
                exit;
            }
        }

        $this->session->set_flashdata('error_message', 'Failed to initiate PayPal payment. Please try again or choose a different payment method.');
        redirect('book');
    }

    // PayPal Payment Success
    public function paypal_success($appointment_id)
    {
        $appointment = $this->AppointmentModel->find($appointment_id);
        
        if (!$appointment || $appointment['user_id'] != $this->session->userdata('user_id')) {
            $this->session->set_flashdata('error_message', 'Invalid appointment.');
            redirect('book');
        }

        $order_id = $this->io->get('token');
        
        if ($order_id) {
            
            // PayPal credentials
            $client_id = 'AXLeP16hnIAruMJoRAdcopKesDd1i1vAyVJLnL_O8v5eLl4oXY0wcKl6SBxt4T5XWdj-lYa4FijryGSj';
            $secret = 'EN6F2ODnW7v7-unddDynhxEDeC0b_FMz5RdKKoZ-3a09qevTCnTSCIV6jPVoTGUzdPU00UOdEkg6lZDJ';
            $paypal_url = 'https://api-m.sandbox.paypal.com';

            // Get Access Token
            $ch = curl_init($paypal_url . '/v1/oauth2/token');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, 'grant_type=client_credentials');
            curl_setopt($ch, CURLOPT_USERPWD, $client_id . ':' . $secret);
            
            $response = curl_exec($ch);
            curl_close($ch);
            
            $result = json_decode($response, true);
            $access_token = $result['access_token'] ?? null;

            if ($access_token) {

                // Capture the payment
                $ch = curl_init($paypal_url . '/v2/checkout/orders/' . $order_id . '/capture');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $access_token
                ]);

                $response = curl_exec($ch);
                $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($http_code === 201) {
                    // Payment captured successfully
                    $this->AppointmentModel->update($appointment_id, [
                        'payment_status' => 'paid'
                    ]);
                    
                    $this->session->set_flashdata('success_message', 'PayPal payment successful! Your appointment is confirmed and awaiting for approval.');
                    redirect('profile');
                }
            }
        }

        $this->session->set_flashdata('error_message', 'Failed to process PayPal payment. Please contact support.');
        redirect('profile');
    }

    // PayPal Payment Cancelled
    public function paypal_cancel($appointment_id)
    {
        $this->session->set_flashdata('error_message', 'PayPal payment was cancelled. Your appointment is still pending. Please try again.');
        redirect('profile');
    }
}