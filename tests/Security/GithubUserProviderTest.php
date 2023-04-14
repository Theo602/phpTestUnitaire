<?php

namespace App\Tests\Security;

use App\Entity\User;
use App\Security\GithubUserProvider;
use PHPUnit\Framework\TestCase;

class GithubUserProviderTest extends TestCase
{
    public function testLoadUserByUsernameReturningAUser()
    {
        $client = $this->getMockBuilder('GuzzleHttp\Client')
            ->disableOriginalConstructor()
            ->getMock();

        $serializer = $this->getMockBuilder('JMS\Serializer\SerializerInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $response =  $this->getMockBuilder('Psr\Http\Message\ResponseInterface')
            ->getMock();

        $client->expects($this->once())
            ->method('get')
            ->willReturn($response);

        $streamedResponse = $this->getMockBuilder('Psr\Http\Message\StreamInterface')
            ->getMock();

        $response->expects($this->once())
            ->method('getBody')
            ->willReturn($streamedResponse);

        $streamedResponse->expects($this->once())
            ->method('getContents')
            ->willReturn('foo');

        $userData = ['login' => 'a login', 'name' => 'user name', 'email' => 'adress@mail.com', 'avatar_url' => 'url to the avatar', 'html_url' => 'url to profile'];

        $serializer->expects($this->once())
            ->method('deserialize')
            ->willReturn($userData);

        $GithubUserProvider = new GithubUserProvider($client, $serializer);
        $user = $GithubUserProvider->loadUserByUsername('token');

        $expectedUser = new User($userData['login'], $userData['name'], $userData['email'], $userData['avatar_url'], $userData['html_url']);

        $this->assertEquals($expectedUser, $user);
        $this->assertEquals('App\Entity\User', get_class($user));
    }
}
