<?php
/*
 * Copyright 2015 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Google\Auth\Tests;

use Composer\Autoload\ClassLoader;
use Exception;
use Google\Auth\HttpHandler\Guzzle5HttpHandler;
use GuzzleHttp\Message\FutureResponse;
use GuzzleHttp\Message\Response;
use GuzzleHttp\Ring\Future\CompletedFutureValue;
use GuzzleHttp\Stream\Stream;

class Guzzle5HttpHandlerTest extends BaseTest
{
    public function setUp()
    {
        $this->onlyGuzzle5();

        $this->mockPsr7Request =
            $this
                ->getMockBuilder('Psr\Http\Message\RequestInterface')
                ->getMock();
        $this->mockRequest =
            $this
                ->getMockBuilder('GuzzleHttp\Message\RequestInterface')
                ->getMock();
        $this->mockClient =
            $this
                ->getMockBuilder('GuzzleHttp\Client')
                ->disableOriginalConstructor()
                ->getMock();
        $this->mockFuture =
            $this
                ->getMockBuilder('GuzzleHttp\Ring\Future\FutureInterface')
                ->disableOriginalConstructor()
                ->getMock();
    }

    public function testSuccessfullySendsRealRequest()
    {
        $request = new \GuzzleHttp\Psr7\Request('get', 'http://httpbin.org/get');
        $client = new \GuzzleHttp\Client();
        $handler = new Guzzle5HttpHandler($client);
        $response = $handler($request);
        $this->assertInstanceOf('Psr\Http\Message\ResponseInterface', $response);
        $this->assertEquals(200, $response->getStatusCode());
        $json = json_decode((string) $response->getBody(), true);
        $this->assertArrayHasKey('url', $json);
        $this->assertEquals($request->getUri(), $json['url']);
    }

    public function testSuccessfullySendsMockRequest()
    {
        $response = new Response(
            200,
            [],
            Stream::factory('Body Text')
        );
        $this->mockClient
            ->expects($this->any())
            ->method('send')
            ->will($this->returnValue($response));
        $this->mockClient
            ->expects($this->any())
            ->method('createRequest')
            ->will($this->returnValue($this->mockRequest));

        $handler = new Guzzle5HttpHandler($this->mockClient);
        $response = $handler($this->mockPsr7Request);
        $this->assertInstanceOf('Psr\Http\Message\ResponseInterface', $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Body Text', (string) $response->getBody());
    }

    public function testAsyncWithoutGuzzlePromiseThrowsException()
    {
        // Pretend the promise library doesn't exist
        foreach (spl_autoload_functions() as $function) {
            if ($function[0] instanceof ClassLoader) {
                $newAutoloader = clone $function[0];
                $newAutoloader->setPsr4('GuzzleHttp\\Promise\\', '/tmp');
                spl_autoload_register($newAutoloadFunc = [$newAutoloader, 'loadClass']);
                spl_autoload_unregister($previousAutoloadFunc = $function);
            }
        }
        $this->mockClient
            ->expects($this->any())
            ->method('send')
            ->will($this->returnValue(new FutureResponse($this->mockFuture)));
        $this->mockClient
            ->expects($this->any())
            ->method('createRequest')
            ->will($this->returnValue($this->mockRequest));

        $handler = new Guzzle5HttpHandler($this->mockClient);
        $errorThrown = false;
        try {
            $handler->async($this->mockPsr7Request);
        } catch (Exception $e) {
            $this->assertEquals(
                'Install guzzlehttp/promises to use async with Guzzle 5',
                $e->getMessage()
            );
            $errorThrown = true;
        }

        // Restore autoloader before assertion (in case it fails)
        spl_autoload_register($previousAutoloadFunc);
        spl_autoload_unregister($newAutoloadFunc);

        $this->assertTrue($errorThrown);
    }

    public function testSuccessfullySendsRequestAsync()
    {
        $response = new Response(
            200,
            [],
            Stream::factory('Body Text')
        );
        $this->mockClient
            ->expects($this->any())
            ->method('send')
            ->will($this->returnValue(new FutureResponse(
                new CompletedFutureValue($response)
            )));
        $this->mockClient
            ->expects($this->any())
            ->method('createRequest')
            ->will($this->returnValue($this->mockRequest));

        $handler = new Guzzle5HttpHandler($this->mockClient);
        $promise = $handler->async($this->mockPsr7Request);
        $this->assertInstanceOf('Psr\Http\Message\ResponseInterface', $promise->wait());
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Body Text', (string) $response->getBody());
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage This is a test rejection message
     */
    public function testPromiseHandlesException()
    {
        $this->mockClient
            ->expects($this->any())
            ->method('send')
            ->will($this->returnValue(new FutureResponse(
                (new CompletedFutureValue(new Response(200)))
                    ->then(function () {
                        throw new Exception('This is a test rejection message');
                    })
            )));
        $this->mockClient
            ->expects($this->any())
            ->method('createRequest')
            ->will($this->returnValue($this->mockRequest));

        $handler = new Guzzle5HttpHandler($this->mockClient);
        $promise = $handler->async($this->mockPsr7Request);
        $promise->wait();
    }

    public function testCreateGuzzle5Request()
    {
        $requestHeaders = [
            'header1' => 'value1',
            'header2' => 'value2',
        ];
        $this->mockPsr7Request
            ->expects($this->once())
            ->method('getHeaders')
            ->will($this->returnValue($requestHeaders));
        $mockBody = $this->getMock('Psr\Http\Message\StreamInterface');
        $this->mockPsr7Request
            ->expects($this->once())
            ->method('getBody')
            ->will($this->returnValue($mockBody));
        $this->mockClient
            ->expects($this->once())
            ->method('createRequest')
            ->with(null, null, [
                'headers' => $requestHeaders + ['header3' => 'value3'],
                'body' => $mockBody,
            ])
            ->will($this->returnValue(
                $this->getMock('GuzzleHttp\Message\RequestInterface')
            ));
        $this->mockClient
            ->expects($this->once())
            ->method('send')
            ->will($this->returnValue(
                $this->getMock('GuzzleHttp\Message\ResponseInterface')
            ));
        $handler = new Guzzle5HttpHandler($this->mockClient);
        $handler($this->mockPsr7Request, [
            'headers' => [
                'header3' => 'value3'
            ]
        ]);
    }
}
