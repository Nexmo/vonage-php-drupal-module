<?php

namespace Drupal\vonage_drupal\Form;

use Vonage\Client;
use Vonage\SMS\Message\SMS;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Vonage\Voice\Endpoint\Phone;
use Vonage\Voice\NCCO\Action\Talk;
use Vonage\Voice\NCCO\NCCO;
use Vonage\Voice\OutboundCall;

class VoiceTestingForm extends ConfigFormBase
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

        if ($config->get('vonage_application_id') && $config->get('vonage_private_key')) {
            $form['voice_testing'] = [
                '#type' => 'details',
                '#title' => $this->t('Test Voice Settings'),
                '#open' => true
            ];

            $form['voice_testing']['instructions'] = [
                '#type' => 'markup',
                '#markup' => 'You can make a Text to Speech testing call to see if you have everything configured correctly. Enter the telephone number you want to call, such as your personal mobile phone, and your Vonage telephone number you already have on your account. You can then enter a message that will be played once the call is answered.'
            ];

            $form['voice_testing']['to'] = [
                '#type' => 'textfield',
                '#required' => true,
                '#title' => $this->t('To'),
                '#description' => $this->t('Number to call'),
            ];

            $form['voice_testing']['from'] = [
                '#type' => 'textfield',
                '#required' => true,
                '#title' => $this->t('From'),
                '#description' => $this->t('Your Vonage number to call from'),
            ];

            $form['voice_testing']['message'] = [
                '#type' => 'textfield',
                '#required' => true,
                '#title' => $this->t('Message'),
                '#description' => $this->t('A sample message to speak as part of the call'),
            ];
            return parent::buildForm($form, $form_state);
        }

        $form['voice_testing'] = [
            '#type' => 'markup',
            '#markup' => 'To use the Vonage Voice API, make sure that you have configured your Application ID and Private Key'
        ];

        return $form;
    }

    protected function getEditableConfigNames()
    {
        return [
            'vonage_drupal.voice_testing'
        ];
    }

    public function getFormId()
    {
        return 'vonage_drupal_voice_testing';
    }

    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        try {
            $to = $form_state->getValue('to');
            $from = $form_state->getValue('from');
            $message = $form_state->getValue('message');

            $outboundCall = new OutboundCall(
                new Phone($to),
                new Phone($from)
            );
            $ncco = new NCCO();
            $ncco->addAction(new Talk($message));
            $outboundCall->setNCCO($ncco);

            $this->client->voice()->createOutboundCall($outboundCall);
        } catch (\Exception $e) {
            $this->messenger()->addError($this->t('Unable to make call: ' . $e->getMessage()));
            return;
        }

        $this->messenger()->addMessage('Call was submitted successfully. Please answer your phone.');
    }
}
