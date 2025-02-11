<?php

namespace Tests\Misery\Component\Parser;

use Misery\Component\Common\FileManager\InMemoryFileManager;
use Misery\Component\Parser\ItemParserFactory;
use Misery\Component\Reader\ItemReader;
use PHPUnit\Framework\TestCase;

class ItemParserFactoryTest extends TestCase
{
    public function test_joining_csv_file(): void
    {
        $path = __DIR__ . '/../../examples/data_file_%d.csv';

        $manager = new InMemoryFileManager();
        $filePaths = [];
        foreach (range(1, 8) as $range) {
            $filePaths[] = sprintf($path, $range);
        }
        $manager->addFiles($filePaths);

        $configuration = [
                'type' => 'csv',
                'filename' => 'data_file_1.csv',
                'fetcher' => 'continuous',
                'join' => [
                    [
                        'filename' => 'data_file_2.csv',
                        'type' => 'csv',
                        'link' => 'ID',
                        'link_join' => 'ID',
                    ],
                    [
                        'filename' => 'data_file_3.csv',
                        'type' => 'csv',
                        'link' => 'ID',
                        'link_join' => 'ID',
                    ],
                    [
                        'filename' => 'data_file_4.csv',
                        'type' => 'csv',
                        'link' => 'ID',
                        'link_join' => 'ID',
                    ],
                    [
                        'filename' => 'data_file_5.csv',
                        'type' => 'csv',
                        'link' => 'ID',
                        'link_join' => 'ID',
                    ],
                    [
                        'filename' => 'data_file_6.csv',
                        'type' => 'csv',
                        'link' => 'ID',
                        'link_join' => 'ID',
                    ],
                    [
                        'filename' => 'data_file_7.csv',
                        'type' => 'csv',
                        'link' => 'ID',
                        'link_join' => 'ID',
                    ],
                    [
                        'filename' => 'data_file_8.csv',
                        'type' => 'csv',
                        'link' => 'ID',
                        'link_join' => 'ID',
                    ],
            ],
        ];
        $factory = new ItemParserFactory();
        $cursor = $factory->createFromConfiguration($configuration, $manager);

        $reader = new ItemReader($cursor);

        $expected = [
            "ID" => "879",
            "Column_1A" => "90",
            "Column_1B" => "0.6850634993647092",
            "Column_1C" => "X",
            "Column_2A" => "1",
            "Column_2B" => "0.5258686170460832",
            "Column_2C" => "K",
            "Column_3A" => "42",
            "Column_3B" => "0.13879594979073628",
            "Column_3C" => "L",
            "Column_4A" => "62",
            "Column_4B" => "0.5956415160968619",
            "Column_4C" => "E",
            "Column_5A" => "57",
            "Column_5B" => "0.08609535025019377",
            "Column_5C" => "X",
            "Column_6A" => "71",
            "Column_6B" => "0.8780018532134506",
            "Column_6C" => "H",
            "Column_7A" => "14",
            "Column_7B" => "0.9804305316345462",
            "Column_7C" => "L",
            "Column_8A" => "66",
            "Column_8B" => "0.5099059345203848",
            "Column_8C" => "Z",
        ];

        $this->assertSame($expected, $reader->index([879])->read());

        $expected = [
            "ID" => "5879",
            "Column_1A" => "1",
            "Column_1B" => "0.9363293097891922",
            "Column_1C" => "E",
            "Column_2A" => "53",
            "Column_2B" => "0.2924569181413661",
            "Column_2C" => "Y",
            "Column_3A" => "28",
            "Column_3B" => "0.39742359296570895",
            "Column_3C" => "O",
            "Column_4A" => "37",
            "Column_4B" => "0.3454988144027209",
            "Column_4C" => "S",
            "Column_5A" => "49",
            "Column_5B" => "0.2282372526736387",
            "Column_5C" => "V",
            "Column_6A" => "52",
            "Column_6B" => "0.7036466383338789",
            "Column_6C" => "D",
            "Column_7A" => "29",
            "Column_7B" => "0.1261233819015699",
            "Column_7C" => "W",
            "Column_8A" => "87",
            "Column_8B" => "0.02860462306530187",
            "Column_8C" => "N",
        ];

        $this->assertSame($expected, $reader->index([5879])->read());
    }

    public function test_joining_multiple_references_from_same_file(): void
    {
        $path = __DIR__ . '/../../examples/%s.csv';

        $manager = new InMemoryFileManager();

        $manager->addFiles([
            sprintf($path, 'organization_workers'),
            sprintf($path, 'organizations'),
            sprintf($path, 'users'),
        ]);

        $configuration = [
            'type' => 'csv',
            'filename' => 'organization_workers.csv',
            'join' => [
                [
                    'filename' => 'organizations.csv',
                    'type' => 'csv',
                    'link' => 'company_ID',
                    'link_join' => 'ID',
                    'return' => ['Name'],
                ],
                [
                    'filename' => 'users.csv',
                    'type' => 'csv',
                    'link' => 'worker_1',
                    'link_join' => 'username',
                    'return' => ['email'],
                ],
                [
                    'filename' => 'users.csv',
                    'type' => 'csv',
                    'link' => 'worker_2',
                    'link_join' => 'username',
                    'return' => ['username'],
                ],
                [
                    'filename' => 'users.csv',
                    'type' => 'csv',
                    'link' => 'worker_3',
                    'link_join' => 'username',
                    'return' => ['first_name'],
                ],
                [
                    'filename' => 'users.csv',
                    'type' => 'csv',
                    'link' => 'worker_4',
                    'link_join' => 'username',
                    'return' => ['last_name'],
                ],
                [
                    'filename' => 'users.csv',
                    'type' => 'csv',
                    'link' => 'worker_5',
                    'link_join' => 'username',
                    'return' => ['phoneNumber'],
                ],
            ],
        ];
        $factory = new ItemParserFactory();
        $cursor = $factory->createFromConfiguration($configuration, $manager);

        $reader = new ItemReader($cursor);

        $expected = [
            'company_ID' => '10',
            'worker_1' => 'meriel_choupin',
            'worker_2' => 'eleonore_hewertson',
            'worker_3' => 'moritz_gotling',
            'worker_4' => 'pippy_syseland',
            'worker_5' => 'hobard_sciacovelli',
            'worker_6' => 'jeniffer_sharkey',
            'worker_7' => 'henrik_tessington',
            'worker_8' => 'willabella_pirt',
            'worker_9' => 'karilynn_domelow',
            'worker_10' => 'shelley_waterstone',
            'worker_11' => 'amandie_pilmer',
            'worker_12' => 'shep_patise',
            'worker_13' => 'dudley_branscombe',
            'worker_14' => 'alica_yanne',
            'worker_15' => 'alphonso_folliss',
            'worker_16' => 'fredelia_tarver',
            'worker_17' => 'giordano_mcffaden',
            'worker_18' => 'harlen_bannon',
            'worker_19' => 'sansone_belsham',
            'worker_20' => 'orlan_furze',
            'worker_21' => 'tremayne_lapsley',
            'worker_22' => 'wake_petrovic',
            'worker_23' => 'christin_rivalland',
            'worker_24' => 'cecilla_pycock',
            'worker_25' => 'peggy_mactrustam',
            'worker_26' => 'obadias_lear',
            'worker_27' => 'joell_wooffinden',
            'worker_28' => 'hillier_titterell',
            'worker_29' => 'paulie_lysons',
            'worker_30' => 'yettie_shearn',
            'Name' => 'Osborn, Ford and Macdonald',
            'email' => 'mchoupin5@bravesites.com',
            'username' => 'eleonore_hewertson',
            'first_name' => 'Moritz',
            'last_name' => 'Syseland',
            'phoneNumber' => '+32 819 730 5095',
        ];

        $this->assertSame($expected, $reader->index([10])->read());
    }

//    public function test_joining_files_with_reusing_codes(): void
//    {
//        $path = __DIR__ . '/../../examples/%s.csv';
//
//        $manager = new InMemoryFileManager();
//
//        $manager->addFiles([
//            sprintf($path, 'designers'),
//            sprintf($path, 'field_codes'),
//        ]);
//
//        $configuration = [
//            'type' => 'csv',
//            'filename' => 'field_codes.csv',
//            'join' => [
//                [
//                    'filename' => 'designers.csv',
//                    'type' => 'csv',
//                    'link' => 'single_line_text_field',
//                    'link_join' => 'code',
//                    'return' => ['label'],
//                ],
//            ],
//        ];
//        $factory = new ItemParserFactory();
//        $cursor = $factory->createFromConfiguration($configuration, $manager);
//
//        $reader = new ItemReader($cursor);
//        $reader->read();$reader->read();$reader->read(); # read ahead 3 lines
//
//        $expected = [
//            'single_line_text_field' => '103',
//            'label' => 'Ann Demeulemeester',
//        ];
//
//        $this->assertSame($expected, $reader->read());
//    }
}