<?php

namespace Tests\Misery\Component\Akeneo\Client\Errors;

use Misery\Component\Akeneo\Client\Errors\AkeneoError;
use Misery\Component\Akeneo\Client\Errors\AkeneoErrorFactory;
use Misery\Component\Akeneo\Client\Errors\AkeneoErrors;
use PHPUnit\Framework\TestCase;

class AkeneoErrorFactoryTest extends TestCase
{
    public function testCreateErrorsWithSinglePayload()
    {
        $payload = [
            'code' => 422,
            'message' => 'Invalid data provided',
            'identifier' => 'product_123',
        ];

        $errors = AkeneoErrorFactory::createErrors($payload);

        $this->assertInstanceOf(AkeneoErrors::class, $errors);
        $this->assertEquals(['product_123: 422: Invalid data provided'], $errors->getErrorMessages());
        $this->assertCount(1, $errors->getErrors());
    }

    public function testCreateErrorsWithMultiPayload()
    {
        $payload = [
            [
                'status_code' => 400,
                'message' => 'Invalid attribute',
                'identifier' => 'attribute_x',
                'errors' => [
                    [
                        'message' => 'This value is required.',
                        'property' => 'name',
                        'attribute' => 'attribute_x',
                        'locale' => 'en_US',
                        'scope' => 'ecommerce',
                    ],
                ],
            ],
            [
                'status_code' => 404,
                'message' => 'Product not found',
                'identifier' => 'product_456',
            ],
        ];

        $errors = AkeneoErrorFactory::createErrors($payload);

        $this->assertInstanceOf(AkeneoErrors::class, $errors);
        $this->assertEquals([
            'attribute_x: 400: Invalid attribute',
            'name: This value is required.',
            'product_456: 404: Product not found',
        ], $errors->getErrorMessages());
        $this->assertCount(3, $errors->getErrors());

        $error1 = $errors->getErrors()[0];
        $this->assertInstanceOf(AkeneoError::class, $error1);
        $this->assertEquals('400: Invalid attribute', $error1->getErrorMessage());

        $error2 = $errors->getErrors()[1];
        $this->assertInstanceOf(AkeneoError::class, $error2);
        $this->assertEquals('This value is required.', $error2->getErrorMessage());
        $this->assertEquals('name', $error2->getField());
        $this->assertEquals('attribute_x', $error2->getAttributeCode());
        $this->assertEquals('en_US', $error2->getLocale());
        $this->assertEquals('ecommerce', $error2->getScope());

        $error3 = $errors->getErrors()[2];
        $this->assertInstanceOf(AkeneoError::class, $error3);
        $this->assertEquals('404: Product not found', $error3->getErrorMessage());
    }

    public function testCreateErrorsWithEmptyPayload()
    {
        $payload = [];

        $errors = AkeneoErrorFactory::createErrors($payload);

        $this->assertInstanceOf(AkeneoErrors::class, $errors);
        $this->assertEquals([], $errors->getErrorMessages());
        $this->assertCount(0, $errors->getErrors());
    }
}
