<?php

namespace Drupal\vonage_drupal\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class ConfigForm extends ConfigFormBase
{
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $config = $this->config('vonage_drupal.config');

        $form['token'] = [
            '#type' => 'details',
            '#title' => $this->t('Account Details'),
            '#open' => true
        ];

        $form['application'] = [
            '#type' => 'details',
            '#title' => $this->t('Application Details'),
            '#open' => $config->get('vonage_application_id') ? true : false
        ];

        $form['token']['vonage_api_key'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Vonage API Key'),
            '#description' => $this->t('Your Vonage API Key'),
            '#default_value' => $config->get('vonage_api_key'),
        ];

        $form['token']['vonage_api_secret'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Vonage API Secret'),
            '#description' => $this->t('Your Vonage API Secret'),
            '#default_value' => $config->get('vonage_api_secret') ? '*****' . substr($config->get('vonage_api_secret'), -4) : '',
        ];

        $form['application']['vonage_application_id'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Application ID'),
            '#description' => $this->t('Application ID to use if using the Voice API'),
            '#default_value' => $config->get('vonage_application_id'),
        ];

        $form['application']['vonage_private_key'] = [
            '#type' => 'textarea',
            '#title' => $this->t('Application Private Key'),
            '#description' => $this->t('Private Key to use for application authentication'),
            '#default_value' => $config->get('vonage_private_key'),
        ];

        return parent::buildForm($form, $form_state);
    }

    protected function getEditableConfigNames()
    {
        return [
            'vonage_drupal.config'
        ];
    }

    public function getFormId()
    {
        return 'vonage_drupal';
    }

    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        parent::submitForm($form, $form_state);
        $config = $this->config('vonage_drupal.config');
        $config
            ->set('vonage_api_key', $form_state->getValue('vonage_api_key'))
            ->set('vonage_application_id', $form_state->getValue('vonage_application_id'))
            ->set('vonage_private_key', $form_state->getValue('vonage_private_key'))
            ->save()
        ;

        $maskedSecret = '*****' . substr($config->get('vonage_api_secret'), -4);
        if ($form_state->getValue('vonage_api_secret') !== $maskedSecret) {
            $config->set('vonage_api_secret', $form_state->getValue('vonage_api_secret'));
        }

        $config->save();
    }
}
