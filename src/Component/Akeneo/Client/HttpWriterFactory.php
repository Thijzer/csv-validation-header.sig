<?php

namespace Misery\Component\Akeneo\Client;

use Assert\Assert;
use Misery\Component\Common\Registry\RegisteredByNameInterface;
use Misery\Component\Configurator\Configuration;
use Misery\Component\Writer\ItemWriterInterface;

class HttpWriterFactory implements RegisteredByNameInterface
{
    public function createFromConfiguration(array $configuration, Configuration $config): ItemWriterInterface
    {
        Assert::that(
            $configuration['type'],
            'type must be filled in.'
        )->notEmpty()->string()->inArray(['rest_api']);

        if ($configuration['type'] === 'rest_api') {
            Assert::that(
                $configuration['endpoint'],
                'endpoint must be filled in.'
            )->notEmpty()->string();

            Assert::that(
                $configuration['method'],
                'method must be filled in.'
            )->notEmpty()->string()->inArray([
                'GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'MULTI_PATCH', 'get', 'post', 'put', 'delete', 'patch', 'multi_patch',
            ]);

            $endpoint = $configuration['endpoint'];
            $method = $configuration['method'];

            Assert::that(
                $endpoint,
                'endpoint must be valid.'
            )->notNull()->notEmpty();

            $accountCode = (isset($configuration['account'])) ? $configuration['account'] : 'target_resource';
            $account = $config->getAccount($accountCode);

            if (!$account) {
                throw new \Exception(sprintf('Account "%s" not found.', $accountCode));
            }

            return new ApiWriter(
                $account,
                $account->getApiEndpoint($endpoint),
                $method,
                $config->getLogger()
            );
        }

        throw new \Exception('Unknown type: ' . $configuration['type']);
    }

    public function getName(): string
    {
        return 'http_writer';
    }
}