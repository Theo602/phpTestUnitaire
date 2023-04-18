<?php

namespace App\Tests\Security;

use App\Entity\User;
use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;
use App\Security\GithubUserProvider;
use JMS\Serializer\Serializer;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class GithubUserProviderTest extends TestCase
{
    private MockObject | Client | null $client;
    private MockObject | Serializer | null $serializer;
    private MockObject | StreamInterface | null $streamedResponse;
    private MockObject | ResponseInterface | null $response;

    public function setUp(): void
    {
        $this->client = $this->getMockBuilder('GuzzleHttp\Client')
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMock();

        $this->serializer = $this->getMockBuilder('JMS\Serializer\SerializerInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->response =  $this->getMockBuilder('Psr\Http\Message\ResponseInterface')
            ->getMock();

        $this->streamedResponse = $this->getMockBuilder('Psr\Http\Message\StreamInterface')
            ->getMock();
    }


    public function testLoadUserByUsernameReturningAUser()
    {
        $this->client
            ->expects($this->once())
            ->method('get')
            ->willReturn($this->response);

        $this->response
            ->expects($this->once())
            ->method('getBody')
            ->willReturn($this->streamedResponse);

        $this->streamedResponse
            ->expects($this->once())
            ->method('getContents')
            ->willReturn('foo');

        $userData = [
            'login' => 'a login',
            'name' => 'user name',
            'email' => 'adress@mail.com',
            'avatar_url' => 'url to the avatar',
            'html_url' => 'url to profile'
        ];

        $this->serializer
            ->expects($this->once())
            ->method('deserialize')
            ->willReturn($userData);

        $GithubUserProvider = new GithubUserProvider($this->client, $this->serializer);
        $user = $GithubUserProvider->loadUserByUsername('token');

        $expectedUser = new User($userData['login'], $userData['name'], $userData['email'], $userData['avatar_url'], $userData['html_url']);

        $this->assertEquals($expectedUser, $user);
        $this->assertEquals('App\Entity\User', get_class($user));
    }

    public function testLoadUserByUsernameNotData()
    {

        $this->client
            ->expects($this->once())
            ->method('get')
            ->willReturn($this->response);

        $this->response
            ->expects($this->once())
            ->method('getBody')
            ->willReturn($this->streamedResponse);

        $this->streamedResponse
            ->expects($this->once())
            ->method('getContents')
            ->willReturn('foo');

        $this->serializer
            ->expects($this->once())
            ->method('deserialize')
            ->willReturn([]);

        $GithubUserProvider = new GithubUserProvider($this->client, $this->serializer);
        $this->expectException('LogicException');
        $GithubUserProvider->loadUserByUsername('token');
    }

    public function tearsDown(): void
    {
        $this->client = null;
        $this->serializer = null;
        $this->streamedResponse = null;
        $this->response = null;
    }
}
