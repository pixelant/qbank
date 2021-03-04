<?php
/**
 * Created by PhpStorm.
 * User: ian
 * Date: 1/7/15
 * Time: 12:09 PM
 */

namespace QBNK\Tests\GuzzleOAuth2;

use QBNK\Tests\ClientTestCase;

use QBNK\GuzzleOAuth2\OAuth2Subscriber as Plugin;
use GuzzleHttp\Subscriber\Mock;
use GuzzleHttp\Message\Response;
use GuzzleHttp\Subscriber\History;

class OAuth2SubscriberTest extends ClientTestCase
{
    public function setup()
    {
        parent::setup();

    }

    private function recurseForMethodName($event, $plugin)
    {
        if (is_array($event[0])) {
            foreach($event as $e) {
                $this->recurseForMethodName($e, $plugin);
            }
        } else {
            $this->assertTrue(method_exists($plugin, $event[0]), $event[0] . ' method does not exist');
        }
    }


    public function testCanGetEvents()
    {
        $plugin = new Plugin();
        $this->assertTrue(is_array($plugin->getEvents()));
    }

    public function testMethodsExistInGetEvents()
    {
        $plugin = new Plugin();
        foreach($plugin->getEvents() as $event) {
            $this->recurseForMethodName($event, $plugin);
        }
        $this->assertTrue(is_array($plugin->getEvents()));
    }
}