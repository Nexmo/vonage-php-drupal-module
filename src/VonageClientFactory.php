<?php

namespace Drupal\vonage_drupal;

use Drupal\Core\Config\ConfigFactory;
use Vonage\Client;
use Vonage\Client\Credentials\Basic;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Vonage\Client\Credentials\Container;
use Vonage\Client\Credentials\Keypair;
use Vonage\Client\Credentials\SignatureSecret;

class VonageClientFactory
{
    public function createVonageClient(ContainerInterface $container, ConfigFactory $configFactory)
    {
        $config = $configFactory->get('vonage_drupal.config');

        $credentials = [];

        if (!empty($config->get('vonage_api_key'))) {
            if (!empty($config->get('vonage_api_secret'))) {
                $credentials[] = new Basic($config->get('vonage_api_key'), $config->get('vonage_api_secret'));
            }

            if (!empty($config->get('vonage_signature_secret') && !empty($config->get('vonage_signature_method')))) {
                $credentials[] = new SignatureSecret(
                    $config->get('vonage_api_key'),
                    $config->get('vonage_signature_secret'),
                    $config->get('vonage_signature_method'))
                ;
            }
        }

        if (!empty($config->get('vonage_application_id'))) {
            if (!empty($config->get('vonage_private_key_path'))) {
                $credentials[] = new Keypair(
                    file_get_contents($config->get('vonage_private_key_path')),
                    $config->get('vonage_application_id')
                );
            } elseif (!empty($config->get('vonage_private_key'))) {
                $credentials[] = new Keypair(
                    $config->get('vonage_private_key'),
                    $config->get('vonage_application_id')
                );
            }
        }

        return new Client(new Container($credentials));
    }
}
