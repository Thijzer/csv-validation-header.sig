<?php

namespace Tests\Misery\Component\Common\Utils;

use Misery\Component\Common\Utils\ContextFormatter;
use PHPUnit\Framework\TestCase;

class ContextFormatterTest extends TestCase
{
    public function testContextFormat()
    {
        $context = [
            'locale' => 'en_US',
        ];
        $data = [
            'filter' => [
                [
                    'name' => 'my_filter',
                    'source' => 'some_path.csv',
                    'options' => [
                        'filter' => [
                            'code' => 'some_code',
                            'locale' => '%locale%',
                        ],
                        'return_value' => 'code',
                    ],
                ]
            ]
        ];
        $expectedResult = [
            'filter' => [
                [
                    'name' => 'my_filter',
                    'source' => 'some_path.csv',
                    'options' => [
                        'filter' => [
                            'code' => 'some_code',
                            'locale' => 'en_US',
                        ],
                        'return_value' => 'code',
                    ],
                ]
            ]
        ];

        $result = ContextFormatter::format($context, $data);

        $this->assertEquals($expectedResult, $result);
    }

    public function testContextFormatWithMissingValues()
    {
        $context = [
            'locale' => 'en_US',
        ];
        $data = [
            'filter' => [
                [
                    'name' => 'my_filter',
                    'source' => 'some_path.csv',
                    'options' => [
                        'filter' => [
                            'code' => '%code%',
                            'locale' => '%locale%',
                        ],
                        'return_value' => 'code',
                    ],
                ]
            ]
        ];
        $expectedResult = [
            'filter' => [
                [
                    'name' => 'my_filter',
                    'source' => 'some_path.csv',
                    'options' => [
                        'filter' => [
                            'code' => '%code%',
                            'locale' => 'en_US',
                        ],
                        'return_value' => 'code',
                    ],
                ]
            ]
        ];

        $result = ContextFormatter::format($context, $data);

        $this->assertEquals($expectedResult, $result);
    }

    public function testContextFormatWithMultipleValues()
    {
        $context = [
            'locale' => 'en_US',
            'code' => 'some_code',
        ];
        $data = [
            'filter' => [
                [
                    'name' => 'my_filter',
                    'source' => 'some_path.csv',
                    'options' => [
                        'filter' => [
                            'code' => '%code%',
                            'locale' => '%locale%',
                        ],
                        'return_value' => 'code',
                    ],
                ]
            ]
        ];
        $expectedResult = [
            'filter' => [
                [
                    'name' => 'my_filter',
                    'source' => 'some_path.csv',
                    'options' => [
                        'filter' => [
                            'code' => 'some_code',
                            'locale' => 'en_US',
                        ],
                        'return_value' => 'code',
                    ],
                ]
            ]
        ];

        $result = ContextFormatter::format($context, $data);

        $this->assertEquals($expectedResult, $result);
    }

    public function testContextFormatWithMultipleFoundValues()
    {
        $context = [
            'locale' => 'en_US',
            'code' => 'some_code',
        ];
        $data = [
            'filter' => [
                [
                    'name' => 'my_filter',
                    'source' => 'some_path.csv',
                    'options' => [
                        'filter' => [
                            'code' => '%code%',
                            'locale' => '%locale%',
                        ],
                        'return_value' => '%code%',
                    ],
                ]
            ]
        ];
        $expectedResult = [
            'filter' => [
                [
                    'name' => 'my_filter',
                    'source' => 'some_path.csv',
                    'options' => [
                        'filter' => [
                            'code' => 'some_code',
                            'locale' => 'en_US',
                        ],
                        'return_value' => 'some_code',
                    ],
                ]
            ]
        ];

        $result = ContextFormatter::format($context, $data);

        $this->assertEquals($expectedResult, $result);
    }

    public function testContextFormatWithMultipleFoundValuesAndKeys()
    {
        $context = [
            'locale' => 'en_US',
            'code' => 'some_code',
        ];
        $data = [
            'filter' => [
                [
                    'name' => 'my_filter',
                    'source' => 'some_path.csv',
                    'options' => [
                        'filter' => [
                            'code' => '%code%',
                            'locale' => '%locale%',
                        ],
                        'return_value' => '%code%',
                    ],
                ]
            ],
            'action' => [
                'first_action' => [
                    'context' => [
                        '%code%' =>  'code',
                    ]
                ]
            ]
        ];
        $expectedResult = [
            'filter' => [
                [
                    'name' => 'my_filter',
                    'source' => 'some_path.csv',
                    'options' => [
                        'filter' => [
                            'code' => 'some_code',
                            'locale' => 'en_US',
                        ],
                        'return_value' => 'some_code',
                    ],
                ]
            ],
            'action' => [
                'first_action' => [
                    'context' => [
                        'some_code' =>  'code',
                    ]
                ]
            ]
        ];

        $result = ContextFormatter::format($context, $data);

        $this->assertEquals($expectedResult, $result);
    }
}
