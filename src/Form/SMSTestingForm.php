<?php

namespace Drupal\vonage_drupal\Form;

use Vonage\Client;
use Vonage\SMS\Message\SMS;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class SMSTestingForm extends ConfigFormBase
{
    /**
     * @var Client
     */
    protected $client;

    public function __construct(ConfigFactoryInterface $config, Client $client)
    {
        parent::__construct($config);
        $this->client = $client;
    }

    /**
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container) {
        return new static(
            $container->get('config.factory'),
            $container->get(Client::class)
        );
    }

    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $config = $this->config('vonage_drupal.config');

        if ($config->get('vonage_api_key') && $config->get('vonage_api_secret')) {
            $form['sms_testing'] = [
                '#type' => 'details',
                '#title' => $this->t('Test SMS Settings'),
                '#open' => true
            ];

            $form['sms_testing']['instructions'] = [
                '#type' => 'markup',
                '#markup' => 'You can send a test SMS message to see if you have everything configured correctly. Enter the telephone number you want to send the message to, such as your personal mobile phone, and your Vonage telephone number you already have on your account. You can then enter a message that will be sent as the SMS text.'
            ];
    
            $form['sms_testing']['to'] = [
                '#type' => 'textfield',
                '#required' => true,
                '#title' => $this->t('To'),
                '#description' => $this->t('Number to send the SMS to'),
            ];
    
            $form['sms_testing']['from'] = [
                '#type' => 'textfield',
                '#required' => true,
                '#title' => $this->t('From'),
                '#description' => $this->t('Your Vonage number to send the SMS from'),
            ];
    
            $form['sms_testing']['message'] = [
                '#type' => 'textfield',
                '#required' => true,
                '#title' => $this->t('Message'),
                '#description' => $this->t('A test message to send via SMS'),
            ];
            return parent::buildForm($form, $form_state);
        } 
        
        $form['sms_testing'] = [
            '#type' => 'markup',
            '#markup' => 'To use the Vonage SMS API, make sure that you have configured your API Key and Secret'
        ];

        return $form;
    }

    protected function getEditableConfigNames()
    {
        return [
            'vonage_drupal.sms_testing'
        ];
    }

    public function getFormId()
    {
        return 'vonage_drupal_sms_testing';
    }

    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        try {
            $to = $form_state->getValue('to');
            $from = $form_state->getValue('from');
            $message = $form_state->getValue('message');

            $this->client->sms()->send(new SMS($to, $from, $message));
        } catch (\Exception $e) {
            $this->messenger()->addError($this->t('Unable to send SMS: ' . $e->getMessage()));
            return;
        }

        $this->messenger()->addMessage('SMS was sent. Please check the mobile phone.');
    }
}
