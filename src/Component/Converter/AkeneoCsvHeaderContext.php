<?php

namespace Misery\Component\Converter;

use Misery\Component\Validator\LocaleValidator;

class AkeneoCsvHeaderContext
{
    private $header;

    public function create(array $item): array
    {
        if ($this->header) {
            return $this->header;
        }

        $output = [];

        # we need to calculate the keys here on the header,
        # then we calculated store result an merge recursively.
        # after that we only need to set our data value or unit

        foreach ($item as $key => $value) {
            # values
            $prep = [
                'data' => null,
                'locale' => null,
                'scope' => null,
                'key' => $key, # original key
            ];

            $prep = $this->prepContext($key, $prep);

            $output[$key] = $prep;
            unset($item[$key]);
        }

        $this->header = $item+$output;

        return $this->header;
    }

    private function prepContext(string $key, array $prep): array
    {
        $separator = '-';
        $scopes = explode($separator, $key);
        unset($scopes[0]);

        foreach ($scopes as $scope) {
            if (LocaleValidator::validate($scope)) {
                $prep['locale'] = $scope;
            } else {
                $prep['scope'] = $scope;
            }
        }

        return $prep;
    }

    public function unsetHeader() {
        $this->header = null;
    }
}