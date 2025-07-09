<?php

namespace Tests\Misery\Component\Converter;

use Misery\Component\Converter\XmlExtractionConverter;
use PHPUnit\Framework\TestCase;

class XmlExtractionConverterTest extends TestCase
{
    private array $input = [
        '@attributes' => [
            'mode' => 'new',
        ],
        'SUPPLIER_PID' => '10131801002',
        'PRODUCT_DETAILS' => [
            'DESCRIPTION_SHORT' => 'Special protective conduit helix for sensor technology, stainless steel 304, ID2,5xOD3,1',
            'DESCRIPTION_LONG'  => 'Protective metal conduit helix SPR-PVC-EL Material: stainless steel, Temperature range: -200°C ... +600°C Properties: flexible / temperature resistant / widely resistant to solvents and chemicals Application: opto-electronics / medical technology / beam wave guide technology / technical endoscopy / measuring instruments / sensor technology',
            'INTERNATIONAL_PID'  => [
                '@attributes' => ['type' => 'gtin'],
                '@value'      => '4025113305282',
            ],
            'SUPPLIER_ALT_PID'       => '10131801002',
            'MANUFACTURER_PID'       => '10131801002',
            'MANUFACTURER_NAME'      => [],
            'MANUFACTURER_TYPE_DESCR'=> 'FDW-VA, ID2,5xAD3,1mm',
            'SPECIAL_TREATMENT_CLASS'=> [
                '@attributes' => ['type' => 'NOT_RELEVANT'],
                '@value'      => 'NONE',
            ],
        ],
        'PRODUCT_FEATURES' => [
            'REFERENCE_FEATURE_SYSTEM_NAME' => 'ETIM-9.0',
            'REFERENCE_FEATURE_GROUP_ID'    => 'EC001179',
            'FEATURE' => [
                ['FNAME'=>'EF005114','FVALUE'=>'-','FUNIT'=>'EU570448'],
                ['FNAME'=>'EF000167','FVALUE'=>'0.019','FUNIT'=>'EU570254'],
                ['FNAME'=>'EF000015','FVALUE'=>'3.1','FUNIT'=>'EU570448'],
                ['FNAME'=>'EF000065','FVALUE'=>'2.5','FUNIT'=>'EU570448'],
                ['FNAME'=>'EF001744','FVALUE'=>'20','FUNIT'=>'EU570448'],
                ['FNAME'=>'EF001782','FVALUE'=>'false'],
                ['FNAME'=>'EF001347','FVALUE'=>'-','FUNIT'=>'EU570020'],
                ['FNAME'=>'EF001742','FVALUE'=>['-200','+600'],'FUNIT'=>'EU570073'],
                ['FNAME'=>'EF001783','FVALUE'=>'false'],
                ['FNAME'=>'EF002782','FVALUE'=>'-','FUNIT'=>'EU570020'],
                ['FNAME'=>'EF001779','FVALUE'=>'false'],
                ['FNAME'=>'EF001746','FVALUE'=>'false'],
                ['FNAME'=>'EF001777','FVALUE'=>'-','FUNIT'=>'EU570020'],
                ['FNAME'=>'EF005462','FVALUE'=>'-'],
                ['FNAME'=>'EF002169','FVALUE'=>'EV000166'],
                ['FNAME'=>'EF000139','FVALUE'=>'EV006034'],
                ['FNAME'=>'EF003660','FVALUE'=>'-'],
                ['FNAME'=>'EF003661','FVALUE'=>'EV004433'],
                ['FNAME'=>'EF001776','FVALUE'=>'EV004498'],
                ['FNAME'=>'EF005474','FVALUE'=>'EV006404'],
                ['FNAME'=>'EF000007','FVALUE'=>'EV000294'],
                ['FNAME'=>'EF001778','FVALUE'=>'EV004517'],
                ['FNAME'=>'EF005660','FVALUE'=>'EV000995'],
                ['FNAME'=>'EF000229','FVALUE'=>'-','FUNIT'=>'EU570448'],
            ],
        ],
        'PRODUCT_ORDER_DETAILS' => [
            'ORDER_UNIT'       => 'MTR',
            'CONTENT_UNIT'     => 'MTR',
            'NO_CU_PER_OU'     => '1',
            'PRICE_QUANTITY'   => '100',
            'QUANTITY_MIN'     => '50',
            'QUANTITY_INTERVAL'=> '100',
        ],
        'PRODUCT_PRICE_DETAILS' => [
            'DATETIME' => [
                ['@attributes'=>['type'=>'valid_start_date'],'DATE'=>'2024-10-01'],
                ['@attributes'=>['type'=>'valid_end_date'  ],'DATE'=>'2025-06-30'],
            ],
            'PRODUCT_PRICE' => [
                '@attributes'   => ['price_type'=>'net_list'],
                'PRICE_AMOUNT'  => '494.90',
                'PRICE_CURRENCY'=> 'EUR',
                'TAX'           => '0.19',
                'LOWER_BOUND'   => '50.00',
            ],
        ],
        'USER_DEFINED_EXTENSIONS' => [
            'UDX.EDXF.MIME_INFO' => [
                'UDX.EDXF.MIME' => [
                    ['UDX.EDXF.MIME_SOURCE'=>'FDW_VA.jpg', 'UDX.EDXF.MIME_CODE'=>'MD01', 'UDX.EDXF.MIME_FILENAME'=>'FDW_VA.jpg'],
                    ['UDX.EDXF.MIME_SOURCE'=>'FDW_VA.tif', 'UDX.EDXF.MIME_CODE'=>'MD01', 'UDX.EDXF.MIME_FILENAME'=>'FDW_VA.tif'],
                    ['UDX.EDXF.MIME_SOURCE'=>'FDW_VA.jpg', 'UDX.EDXF.MIME_CODE'=>'MD01', 'UDX.EDXF.MIME_FILENAME'=>'FDW_VA.jpg'],
                    ['UDX.EDXF.MIME_SOURCE'=>'https://www.flexa.de/Produkte/DE_index_1105.html?grpid=c2bfb2bd-fbe2-4a53-8efb-8e528ff18444','UDX.EDXF.MIME_CODE'=>'MD04','UDX.EDXF.MIME_FILENAME'=>'DE_index_1105.html?grpid=c2bfb2bd-fbe2-4a53-8efb-8e528ff18444'],
                    ['UDX.EDXF.MIME_SOURCE'=>'https://www.flexa.de/Service/Konformitaetserklaerungen/DE_index_3012.html','UDX.EDXF.MIME_CODE'=>'MD05','UDX.EDXF.MIME_FILENAME'=>'DE_index_3012.html'],
                    ['UDX.EDXF.MIME_SOURCE'=>'RoHS_deutsch.pdf','UDX.EDXF.MIME_CODE'=>'MD49','UDX.EDXF.MIME_FILENAME'=>'RoHS_deutsch.pdf'],
                    ['UDX.EDXF.MIME_SOURCE'=>'https://flexa.partcommunity.com?info=flexa%2Fspecial%5Fconduits%2Ffdw%5Fva%2Eprj&varset=%7BART%3D10131801002%7D&encoding=%25','UDX.EDXF.MIME_CODE'=>'MD99','UDX.EDXF.MIME_FILENAME'=>'flexa.partcommunity.com?info=flexa%2Fspecial%5Fconduits%2Ffdw%5Fva%2Eprj&varset=%7BART%3D10131801002%7D&encoding=%25'],
                    ['UDX.EDXF.MIME_SOURCE'=>'RoHS_deutsch.pdf','UDX.EDXF.MIME_CODE'=>'MD49','UDX.EDXF.MIME_FILENAME'=>'RoHS_deutsch.pdf'],
                ],
            ],
            'UDX.EDXF.DESCRIPTION_VERY_SHORT' => 'FDW-VA, helix',
            'UDX.EDXF.DISCOUNT_GROUP'       => ['UDX.EDXF.DISCOUNT_GROUP_MANUFACTURER'=>'1024'],
            'UDX.EDXF.PACKING_UNITS'        => [
                'UDX.EDXF.PACKING_UNIT'=>[
                    'UDX.EDXF.QUANTITY_MIN'=>'50',
                    'UDX.EDXF.QUANTITY_MAX'=>'50',
                    'UDX.EDXF.PACKING_UNIT_CODE'=>'RG',
                ],
            ],
            'UDX.EDXF.REACH'                => [
                'UDX.EDXF.REACH.LISTDATE'=>'2025-05-09',
                'UDX.EDXF.REACH.INFO'    =>'false',
            ],
        ],
        'PRODUCT_LOGISTIC_DETAILS' => [
            'CUSTOMS_TARIFF_NUMBER'=>['CUSTOMS_NUMBER'=>'74122000'],
            'COUNTRY_OF_ORIGIN'     =>'DE',
        ],
    ];

    public function testConvertProductDetails()
    {
        $converter = new XmlExtractionConverter();
        $converter->setOptions([
            'separator' => '|',
            'extract' => [
                'PRODUCT_DETAILS' => '',
            ],
        ]);

        $expected = [
            "PRODUCT_DETAILS|DESCRIPTION_SHORT" => "Special protective conduit helix for sensor technology, stainless steel 304, ID2,5xOD3,1",
            "PRODUCT_DETAILS|DESCRIPTION_LONG" => "Protective metal conduit helix SPR-PVC-EL Material: stainless steel, Temperature range: -200°C ... +600°C Properties: flexible / temperature resistant / widely resistant to solvents and chemicals Application: opto-electronics / medical technology / beam wave guide technology / technical endoscopy / measuring instruments / sensor technology",
            "PRODUCT_DETAILS|INTERNATIONAL_PID|@attributes|type" => "gtin",
            "PRODUCT_DETAILS|INTERNATIONAL_PID|@value" => "4025113305282",
            "PRODUCT_DETAILS|SUPPLIER_ALT_PID" => "10131801002",
            "PRODUCT_DETAILS|MANUFACTURER_PID" => "10131801002",
            "PRODUCT_DETAILS|MANUFACTURER_TYPE_DESCR" => "FDW-VA, ID2,5xAD3,1mm",
            "PRODUCT_DETAILS|SPECIAL_TREATMENT_CLASS|@attributes|type" => "NOT_RELEVANT",
            "PRODUCT_DETAILS|SPECIAL_TREATMENT_CLASS|@value" => "NONE",
//            'PRODUCT_DETAILS|DESCRIPTION_SHORT|dut' => 'T-pijpsleutel 1000 V',
//            'PRODUCT_DETAILS|DESCRIPTION_SHORT|eng' => 'T-Handle socket wrenches 1000 V',
//            'PRODUCT_DETAILS|DESCRIPTION_LONG|eng' => 'chrome vanadium steel, full insulated, for hexagon nuts, DIN 7440, 2-coloured coating-insulation - inside yellow/outside red',
//            'PRODUCT_DETAILS|DESCRIPTION_LONG|dut' => 'van vanadium-molybdeen-staal, voor zeskantmoeren DIN 7440, volledig geïsoleerd volgens VDE 0680/2/3.78, 0682/201, 2-kleurige dompelisolatie, buitenzijde rood/binnenzijde geel',
        ];

        $result = $converter->convert($this->input);

        $this->assertEquals($expected, $result);
    }

    public function testConvertUserDefinedExtensions()
    {
        $converter = new XmlExtractionConverter();
        $converter->setOptions([
            'separator' => '|',
            'extract' => [
                'USER_DEFINED_EXTENSIONS|UDX.EDXF.MIME_INFO|UDX.EDXF.MIME' => [
                    'k' => 'UDX.EDXF.MIME_CODE',
                    'v' => 'UDX.EDXF.MIME_FILENAME',
                ],
            ],
        ]);

        $expected = [
            "USER_DEFINED_EXTENSIONS|UDX.EDXF.MIME_INFO|UDX.EDXF.MIME|MD01" => "FDW_VA.jpg",
            "USER_DEFINED_EXTENSIONS|UDX.EDXF.MIME_INFO|UDX.EDXF.MIME|MD04" => "DE_index_1105.html?grpid=c2bfb2bd-fbe2-4a53-8efb-8e528ff18444",
            "USER_DEFINED_EXTENSIONS|UDX.EDXF.MIME_INFO|UDX.EDXF.MIME|MD05" => "DE_index_3012.html",
            "USER_DEFINED_EXTENSIONS|UDX.EDXF.MIME_INFO|UDX.EDXF.MIME|MD49" => "RoHS_deutsch.pdf",
            "USER_DEFINED_EXTENSIONS|UDX.EDXF.MIME_INFO|UDX.EDXF.MIME|MD99" => "flexa.partcommunity.com?info=flexa%2Fspecial%5Fconduits%2Ffdw%5Fva%2Eprj&varset=%7BART%3D10131801002%7D&encoding=%25",
        ];

        $result = $converter->convert($this->input);

        $this->assertEquals($expected, $result);
    }
}