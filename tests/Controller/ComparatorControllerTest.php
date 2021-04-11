<?php

namespace App\Tests\Controller;

use App\Service\GithubComparator;
use Mockery;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ComparatorControllerTest extends WebTestCase
{
    public function testShouldInvokeCompareSuccess()
    {
        $githubComparatorMock = Mockery::mock(GithubComparator::class)
            ->shouldReceive('compareByName')
            ->withArgs(['user', 'repository', 'user2', 'repository2'])
            ->andReturn([
                'test' => 1
            ])
            ->getMock();

        $client = static::createClient();
        $client->getKernel()->getContainer()->set(GithubComparator::class, $githubComparatorMock);

        $client->request('GET', 'compare/user/repository/user2/repository2');

        $this->assertResponseIsSuccessful();
        $this->assertEquals(json_encode(['test' => 1], JSON_PRETTY_PRINT), $client->getResponse()->getContent());
        $githubComparatorMock->shouldHaveReceived('compareByName');
    }

    public function testShouldInvokeCompareByLinksSuccess()
    {
        $first = 'https://github.com/kamslosarz/tetris.git';
        $second = 'https://github.com/kamslosarz/app.git';
        $githubComparatorMock = Mockery::mock(GithubComparator::class)
            ->shouldReceive('compareByLinks')
            ->withArgs([$first, $second])
            ->andReturn([
                'test' => 1
            ])
            ->getMock();

        $client = static::createClient();
        $client->getKernel()->getContainer()->set(GithubComparator::class, $githubComparatorMock);

        $client->request('GET', sprintf('compare-links/first/%s/second/%s', $first, $second));

        $this->assertResponseIsSuccessful();
        $this->assertEquals(json_encode(['test' => 1], JSON_PRETTY_PRINT), $client->getResponse()->getContent());
        $githubComparatorMock->shouldHaveReceived('compareByLinks');
    }
}
