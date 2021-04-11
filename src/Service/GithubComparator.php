<?php

namespace App\Service;

use App\AppException;
use Github\Api\AbstractApi;
use Github\Api\PullRequest;
use Github\Api\Repo;
use Github\Client;
use Http\Discovery\Exception\NotFoundException;
use JetBrains\PhpStorm\ArrayShape;
use Throwable;

class GithubComparator
{
    const GIT_NAME_PATTERN = '[a-z\d](?:[a-z\d]|-(?=[a-z\d])){0,38}';

    public function __construct(private Client $client, string $username, string $token)
    {
        $this->client->authenticate($username, $token, Client::AUTH_CLIENT_ID);
    }

    /**
     * @param string $firstUser
     * @param string $firstRepo
     * @param string $secondUser
     * @param string $secondRepo
     * @return array
     * @throws AppException
     */
    public function compareByName(string $firstUser, string $firstRepo, string $secondUser, string $secondRepo): array
    {
        $format = '%s/%s';
        return [
            sprintf($format, $firstUser, $firstRepo) => $this->fetch($firstUser, $firstRepo),
            sprintf($format, $secondUser, $secondRepo) => $this->fetch($secondUser, $secondRepo)
        ];
    }

    /**
     * @param $first
     * @param $second
     * @return array
     * @throws AppException
     */
    public function compareByLinks($first, $second): array
    {
        return [
            $first => $this->fetch(...$this->explodeUrl($first)),
            $second => $this->fetch(...$this->explodeUrl($second))
        ];
    }

    /**
     * @param string $user
     * @param string $repository
     * @return array
     * @throws AppException
     */
    #[ArrayShape([
        'watchers_count' => "mixed", 'subscribers_count' => "mixed", 'forks_count' => "mixed",
        'latest_release' => "mixed|null", 'open_pull_requests' => "int", 'closed_pull_requests' => "int",
        'total_commits' => "int"
    ])]
    private function fetch(string $user, string $repository): array
    {
        $repo = $this->fetchRepo($user, $repository);
        $repositoryBasicData = $repo->show($user, $repository);
        $releases = $this->getReleases($repo, $user, $repository);
        $totalCommits = $this->getTotalCommits($repo, $user, $repository);
        list($openPullRequests, $closedPullRequests) = $this->getPullRequestsCounter($user, $repository);

        return [
            'watchers_count' => $repositoryBasicData['watchers_count'],
            'subscribers_count' => $repositoryBasicData['subscribers_count'],
            'forks_count' => $repositoryBasicData['forks_count'],
            'latest_release' => $releases['created_at'] ?? null,
            'open_pull_requests' => $openPullRequests,
            'closed_pull_requests' => $closedPullRequests,
            'total_commits' => $totalCommits
        ];
    }

    private function getTotalCommits(Repo $repo, string $user, string $repository): int
    {
        try
        {
            return count($repo->commits()->all($user, $repository, []));
        }
        catch(Throwable)
        {
            return 0;
        }
    }

    private function getPullRequestsCounter(string $user, string $repository): array
    {
        /** @var PullRequest $pullRequests */
        try
        {
            $pullRequests = $this->client->api('pull_request');
            $closedPullRequests = count($pullRequests->all($user, $repository, ['state' => 'closed']));
            $openPullRequests = count($pullRequests->all($user, $repository, ['state' => 'open']));
        }
        catch(Throwable)
        {
            $closedPullRequests = 0;
            $openPullRequests = 0;
        }
        return [$closedPullRequests, $openPullRequests];
    }

    private function getReleases(Repo $repo, $user, $repository): array
    {
        try
        {
            return $repo->releases()->latest($user, $repository);
        }
        catch(Throwable)
        {
            return [];
        }
    }

    /**
     * @param string $user
     * @param string $repository
     * @return Repo|AbstractApi
     * @throws AppException
     */
    private function fetchRepo(string $user, string $repository): Repo|AbstractApi
    {
        try
        {
            /** @var Repo $repo */
            return $this->client->api('repo');
        }
        catch(Throwable $exception)
        {
            throw new AppException(sprintf('[%s/%s] Exception from api.github.com: %s.', $user, $repository, $exception->getMessage()));
        }
    }

    /**
     * @param $url
     * @return array
     */
    private function explodeUrl($url): array
    {
        $regex = sprintf('/(%s)\/(%s).git$/', self::GIT_NAME_PATTERN, self::GIT_NAME_PATTERN);
        $matches = [];
        if(preg_match($regex, $url, $matches))
        {
            return array_slice($matches, 1);
        }

        throw new NotFoundException(sprintf('Repo "%s" not found', $url));
    }
}
