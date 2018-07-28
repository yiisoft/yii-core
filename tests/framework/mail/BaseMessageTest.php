<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\tests\framework\mail;

use yii\helpers\Yii;
use yii\tests\data\mail\TestMailer;
use yii\tests\TestCase;

/**
 * @group mail
 */
class BaseMessageTest extends TestCase
{
    public function setUp()
    {
        $this->mockApplication([
            'components' => [
                'mailer' => $this->createTestEmailComponent()
            ]
        ]);
    }

    /**
     * @return TestMailer test email component instance.
     */
    protected function createTestEmailComponent()
    {
        $component = new TestMailer();

        return $component;
    }

    /**
     * @return TestMailer mailer instance.
     */
    protected function getMailer()
    {
        return Yii::$app->get('mailer');
    }

    // Tests :

    public function testSend()
    {
        $mailer = $this->getMailer();
        $message = $mailer->compose();
        $message->send($mailer);
        $this->assertEquals($message, $mailer->sentMessages[0], 'Unable to send message!');
    }

    public function testToString()
    {
        $mailer = $this->getMailer();
        $message = $mailer->compose();
        $this->assertEquals($message->toString(), '' . $message);
    }
}
