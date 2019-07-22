<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\tests\framework\helpers;

use yii\helpers\FileHelper;
use yii\helpers\HtmlPurifier;
use yii\tests\TestCase;

/**
 * @group html-purifier
 */
class HtmlPurifierTest extends TestCase
{
    private $runtimePath;

    protected function setUp()
    {
        if (!class_exists(\HTMLPurifier_Config::class)) {
            $this->markTestSkipped('"ezyang/htmlpurifier" package required');
            return;
        }

        $this->runtimePath = \dirname(__DIR__, 2) . '/runtime/HtmlPurifier';
        FileHelper::createDirectory($this->runtimePath);

        parent::setUp();
    }

    public function tearDown()
    {
        FileHelper::removeDirectory($this->runtimePath);
    }

    /**
     * Data provider for {@see testProcess()}
     * @return array test data.
     */
    public function dataProviderProcess(): array
    {
        return [
            ['Some <b>html</b>', 'Some <b>html</b>'],
            ['Some script<script>alert("!")</script>', 'Some script'],
        ];
    }

    /**
     * @dataProvider dataProviderProcess
     *
     * @param string $content
     * @param string $expected
     * @throws \yii\exceptions\InvalidConfigException
     * @throws \yii\exceptions\Exception
     */
    public function testProcess(string $content, string $expected): void
    {
        $this->assertSame($expected, HtmlPurifier::process($content, function (\HTMLPurifier_Config $config) {
            $config->set('Cache.SerializerPath', $this->runtimePath);
            $config->set('Cache.SerializerPermissions', 0775);
        }));
    }

    public function dataProviderTruncateCharacters(): array
    {
        return [
            [
                '<span>This is a test sentance</span>',
                14,
                '...',
                '<span>This is a test</span>...'
            ],
            [
                '<span>This is a test </span>sentance',
                14,
                '...',
                '<span>This is a test</span>...'
            ],
            [
                '<span>This is a test </span><strong>for a sentance</strong>',
                18,
                '...',
                '<span>This is a test </span><strong>for</strong>...'
            ],
            [
                '<span>This is a test</span><strong> for a sentance</strong>',
                18,
                '...',
                '<span>This is a test</span><strong> for</strong>...'
            ],
            [
                '<span><img src="image.png" />This is a test sentance</span>',
                14,
                '...',
                '<span><img src="image.png" />This is a test</span>...'
            ],
            [
                '<span><img src="image.png" />This is a test </span>sentance',
                14,
                '...',
                '<span><img src="image.png" />This is a test</span>...'
            ],
            [
                '<span><img src="image.png" />This is a test </span><strong>for a sentance</strong>',
                18,
                '...',
                '<span><img src="image.png" />This is a test </span><strong>for</strong>...'
            ],
            [
                '<p>This is a test</p><ul><li>bullet1</li><li>bullet2</li><li>bullet3</li><li>bullet4</li></ul>',
                22,
                '...',
                '<p>This is a test</p><ul><li>bullet1</li><li>b</li></ul>...'
            ],
            [
                '<div><ul><li>bullet1</li><li><div>bullet2</div></li></ul><br></div>',
                8,
                '...',
                '<div><ul><li>bullet1</li><li><div>b</div></li></ul></div>...'
            ]
        ];
    }

    /**
     * @dataProvider dataProviderTruncateCharacters
     *
     * @throws \HTMLPurifier_Exception
     * @throws \yii\exceptions\InvalidConfigException
     */
    public function testTruncateCharacters($content, $length, $suffix, $expected): void
    {
        $this->assertEquals($expected, HtmlPurifier::truncateCharacters($content, $length, $suffix, 'utf-8', function (\HTMLPurifier_Config $config) {
            $config->set('Cache.SerializerPath', $this->runtimePath);
            $config->set('Cache.SerializerPermissions', 0775);
        }));
    }

    public function dataProviderTruncateWords()
    {
        return [
            [
                'lorem ipsum',
                3,
                '...',
                'lorem ipsum'
            ],
            [
                ' lorem ipsum',
                3,
                '...',
                ' lorem ipsum'
            ],
            [
                '<span>This is a test sentance</span>',
                4,
                '...',
                '<span>This is a test</span>...'
            ],
            [
                '<span>This is a test </span><strong>for a sentance</strong>',
                5,
                '...',
                '<span>This is a test </span><strong>for</strong>...'
            ],
            [
                '<span>This is a test</span><strong> for a sentance</strong>',
                5,
                '...',
                '<span>This is a test</span><strong> for</strong>...'
            ],
            [
                '<p> раз два три четыре пять </p> <p> шесть семь восемь девять десять</p>',
                6,
                '...',
                '<p> раз два три четыре пять </p> <p> шесть</p>...'
            ],
            [
                '<span><img src="image.png" />This is a test sentance</span>',
                4,
                '...',
                '<span><img src="image.png" />This is a test</span>...'
            ],
            [
                '<span><img src="image.png" />This is a test </span><strong>for a sentance</strong>',
                5,
                '...',
                '<span><img src="image.png" />This is a test </span><strong>for</strong>...'
            ],
            [
                '<span><img src="image.png" />This is a test</span><strong> for a sentance</strong>',
                5,
                '...',
                '<span><img src="image.png" />This is a test</span><strong> for</strong>...'
            ],
        ];
    }

    /**
     * @dataProvider dataProviderTruncateWords
     */
    public function testTruncateWords($content, $length, $suffix, $expected)
    {
        $this->assertEquals($expected, HtmlPurifier::truncateWords($content, $length, $suffix, 'utf-8', function (\HTMLPurifier_Config $config) {
            $config->set('Cache.SerializerPath', $this->runtimePath);
            $config->set('Cache.SerializerPermissions', 0775);
        }));
    }
}
