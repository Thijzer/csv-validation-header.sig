<?php

namespace Misery\Component\Akeneo\Client;

use Assert\Assert;
use Misery\Component\Common\Client\Endpoint\BasicApiEndpoint;
use Misery\Component\Common\Registry\RegisteredByNameInterface;
use Misery\Component\Configurator\Configuration;
use Misery\Component\Reader\ReaderInterface;
use Misery\Component\Writer\ItemWriterInterface;

class HttpReaderFactory implements RegisteredByNameInterface
{
    public function createFromConfiguration(array $configuration, Configuration $config): ReaderInterface
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
                'GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'MULTI_PATCH', 'get', 'post', 'put', 'delete', 'patch', 'multi_patch'
            ]);

            $endpoint = $configuration['endpoint'];
            $method = $configuration['method'];

            Assert::that(
                $endpoint,
                'endpoint must be valid.'
            )->notNull()->notEmpty();

            $context = ['filters' => []];
            $context['container'] = $configuration['container'] ?? null;
            $configContext = $config->getContext();

            if (isset($configuration['identifier_filter_list'])) {
                $context['multiple'] = true;
                $context['list'] = is_array($configuration['identifier_filter_list']) ? $configuration['identifier_filter_list'] : $config->getList($configuration['identifier_filter_list']);
            }

            if (isset($configuration['filters'])) {
                $filters = $configuration['filters'];
                foreach ($filters as $fieldCode => $filterConfig) {
                    foreach ($filterConfig as $filterType => $value) {
                        if ($filterType === 'list') {
                            $context['filters'][$fieldCode] = $config->getList($value);
                        }
                    }
                }
            }

            $context['limiters'] = $configuration['limiters'] ?? [];
            if (isset($configuration['akeneo-filter'])) {
                $akeneoFilter = $configuration['akeneo-filter'];
                if (!isset($configContext['akeneo_filters'][$akeneoFilter])) {
                    throw new \Exception(sprintf('The configuration is using an Akeneo filter code (%s) wich is not linked to this job profile.', $configuration['akeneo-filter']));
                }

                // create query string
                $context['limiters']['query_array'] = $configContext['akeneo_filters'][$akeneoFilter]['search'];
            }

            $accountCode = (isset($configuration['account'])) ? $configuration['account'] : 'source_resource';
            $account = $config->getAccount($accountCode);

            if (!$account) {
                throw new \Exception(sprintf('Account "%s" not found.', $accountCode));
            }

            return new ApiReader(
                $account,
                $account->getApiEndpoint($endpoint),
                $context + $configContext
            );
        }

        throw new \Exception('Unknown type: ' . $configuration['type']);
    }

    public function getName(): string
    {
        return 'http_reader';
    }
}