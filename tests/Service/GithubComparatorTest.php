<?php

namespace App\Tests\Service;

use App\AppException;
use App\Service\GithubComparator;
use Github\Api\PullRequest;
use Github\Api\Repo;
use Github\Api\Repository\Commits;
use Github\Api\Repository\Releases;
use Github\Client;
use Mockery;
use PHPUnit\Framework\TestCase;

class GithubComparatorTest extends TestCase
{
    /**
     * @throws AppException
     */
    public function testShouldCompareByName()
    {
        $username = 'username';
        $token = 'token';

        $pullRequestMock = Mockery::mock(PullRequest::class)
            ->shouldReceive('all')
            ->withArgs([
                'user', 'repository', Mockery::on(function () {
                    return true;
                })
            ])
            ->andReturn([[]])
            ->getMock()
            ->shouldReceive('all')
            ->withArgs([
                'user2', 'repository2', Mockery::on(function () {
                    return true;
                })
            ])
            ->andReturn([
                [1]
            ])
            ->getMock();
        $commitsMock = Mockery::mock(Commits::class)
            ->shouldReceive('all')
            ->withArgs(['user', 'repository', []])
            ->andReturn([
                [1]
            ])
            ->getMock()
            ->shouldReceive('all')
            ->withArgs(['user2', 'repository2', []])
            ->andReturn([
                [1]
            ])
            ->getMock();

        $releaseMock = Mockery::mock(Releases::class)
            ->shouldReceive('latest')
            ->withArgs(['user', 'repository'])
            ->once()
            ->andReturn([
                'created_at' => 2
            ])
            ->getMock()
            ->shouldReceive('latest')
            ->withArgs(['user2', 'repository2'])
            ->once()
            ->andReturn([
                'created_at' => 1
            ])
            ->getMock();

        $repoMock = Mockery::mock(Repo::class)
            ->shouldReceive('show')
            ->once()
            ->withArgs(['user', 'repository'])
            ->andReturn([
                'watchers_count' => 1,
                'subscribers_count' => 2,
                'forks_count' => 3
            ])
            ->getMock()
            ->shouldReceive('show')
            ->once()
            ->withArgs(['user2', 'repository2'])
            ->andReturn([
                'watchers_count' => 11,
                'subscribers_count' => 22,
                'forks_count' => 33
            ])
            ->getMock()
            ->shouldReceive('releases')
            ->times(2)
            ->andReturn($releaseMock)
            ->getMock()
            ->shouldReceive('commits')
            ->times(2)
            ->andReturn($commitsMock)
            ->getMock();

        $clientMock = Mockery::mock(Client::class)
            ->shouldReceive('api')
            ->with('repo')
            ->andReturn($repoMock)
            ->getMock()
            ->shouldReceive('api')
            ->with('pull_request')
            ->andReturn($pullRequestMock)
            ->getMock();

        /** @var Client $clientMock */
        $githubComparator = new GithubComparator($clientMock, $username, $token);
        $compared = $githubComparator->compareByName('user', 'repository', 'user2', 'repository2');

        $this->assertEquals([
            'user/repository' => [
                'watchers_count' => 1,
                'subscribers_count' => 2,
                'forks_count' => 3,
                'latest_release' => 2,
                'open_pull_requests' => 1,
                'closed_pull_requests' => 1,
                'total_commits' => 1,
            ],
            'user2/repository2' => [
                'watchers_count' => 11,
                'subscribers_count' => 22,
                'forks_count' => 33,
                'latest_release' => 1,
                'open_pull_requests' => 1,
                'closed_pull_requests' => 1,
                'total_commits' => 1,
            ]
        ], $compared);
        $repoMock->shouldHaveReceived('show')->times(2);
        $repoMock->shouldHaveReceived('releases')->times(2);
        $releaseMock->shouldHaveReceived('latest')->times(2);
        $commitsMock->shouldHaveReceived('all')->times(2);
    }

    public function testShouldCompareByLinks(){

        $username = 'username';
        $token = 'token';

        $pullRequestMock = Mockery::mock(PullRequest::class)
            ->shouldReceive('all')
            ->withArgs([
                'user', 'repository', Mockery::on(function () {
                    return true;
                })
            ])
            ->andReturn([[]])
            ->getMock()
            ->shouldReceive('all')
            ->withArgs([
                'user2', 'repository2', Mockery::on(function () {
                    return true;
                })
            ])
            ->andReturn([
                [1]
            ])
            ->getMock();
        $commitsMock = Mockery::mock(Commits::class)
            ->shouldReceive('all')
            ->withArgs(['user', 'repository', []])
            ->andReturn([
                [1]
            ])
            ->getMock()
            ->shouldReceive('all')
            ->withArgs(['user2', 'repository2', []])
            ->andReturn([
                [1]
            ])
            ->getMock();

        $releaseMock = Mockery::mock(Releases::class)
            ->shouldReceive('latest')
            ->withArgs(['user', 'repository'])
            ->once()
            ->andReturn([
                'created_at' => 2
            ])
            ->getMock()
            ->shouldReceive('latest')
            ->withArgs(['user2', 'repository2'])
            ->once()
            ->andReturn([
                'created_at' => 1
            ])
            ->getMock();

        $repoMock = Mockery::mock(Repo::class)
            ->shouldReceive('show')
            ->once()
            ->withArgs(['user', 'repository'])
            ->andReturn([
                'watchers_count' => 1,
                'subscribers_count' => 2,
                'forks_count' => 3
            ])
            ->getMock()
            ->shouldReceive('show')
            ->once()
            ->withArgs(['user2', 'repository2'])
            ->andReturn([
                'watchers_count' => 11,
                'subscribers_count' => 22,
                'forks_count' => 33
            ])
            ->getMock()
            ->shouldReceive('releases')
            ->times(2)
            ->andReturn($releaseMock)
            ->getMock()
            ->shouldReceive('commits')
            ->times(2)
            ->andReturn($commitsMock)
            ->getMock();

        $clientMock = Mockery::mock(Client::class)
            ->shouldReceive('api')
            ->with('repo')
            ->andReturn($repoMock)
            ->getMock()
            ->shouldReceive('api')
            ->with('pull_request')
            ->andReturn($pullRequestMock)
            ->getMock();

        /** @var Client $clientMock */
        $githubComparator = new GithubComparator($clientMock, $username, $token);
        $compared = $githubComparator->compareByLinks('https://github.com/user/repository.git', 'https://github.com/user2/repository2.git');

        $this->assertEquals([
            'https://github.com/user/repository.git' => [
                'watchers_count' => 1,
                'subscribers_count' => 2,
                'forks_count' => 3,
                'latest_release' => 2,
                'open_pull_requests' => 1,
                'closed_pull_requests' => 1,
                'total_commits' => 1,
            ],
            'https://github.com/user2/repository2.git' => [
                'watchers_count' => 11,
                'subscribers_count' => 22,
                'forks_count' => 33,
                'latest_release' => 1,
                'open_pull_requests' => 1,
                'closed_pull_requests' => 1,
                'total_commits' => 1,
            ]
        ], $compared);
        $repoMock->shouldHaveReceived('show')->times(2);
        $repoMock->shouldHaveReceived('releases')->times(2);
        $releaseMock->shouldHaveReceived('latest')->times(2);
        $commitsMock->shouldHaveReceived('all')->times(2);
    }
}
