Requirements
- `PHP 8.0`
- `Composer version 2.0.12`

Configuration

When app exceeded a api.github limits you can pass credentials for authenticated requests.
add in .env file:
- GITHUB_AUTH_METHOD=\<client_id_header|access_token_header|jwt>\
- GITHUB_USERNAME=\<git username\>
- GITHUB_SECRET=\<github password/token\>

How to run

- `composer install`
- `symfony run serve`

Api

|Method|endpoint|response|
|---|---|---|
|GET|/compare/{firstUser}/{firstRepo}/{secondUser}/{secondRepo}|basic statistics in JSON format|
|GET|/compare-links/first/{first}/second/{second}|basic statistics in JSON format|

exaples: 

`GET /compare/kamslosarz/tetris/kamslosarz/app`
```json
HTTP\/1.1 200 OK
{
    "kamslosarz\/tetris": {
        "watchers_count": 0,
        "subscribers_count": 0,
        "forks_count": 0,
        "latest_release": null,
        "open_pull_requests": 0,
        "closed_pull_requests": 0
    },
    "kamslosarz\/app": {
        "watchers_count": 1,
        "subscribers_count": 1,
        "forks_count": 0,
        "latest_release": null,
        "open_pull_requests": 0,
        "closed_pull_requests": 0
    }
}
```
exaples: 

`GET /compare-links/first/https://github.com/kamslosarz/devops.git/second/https://github.com/kamslosarz/tetris.git`
```json
HTTP\/1.1 200 OK
{
    "https:\/\/github.com\/kamslosarz\/devops.git": {
        "watchers_count": 0,
        "subscribers_count": 1,
        "forks_count": 0,
        "latest_release": null,
        "open_pull_requests": 0,
        "closed_pull_requests": 0,
        "total_commits": 30
    },
    "https:\/\/github.com\/kamslosarz\/tetris.git": {
        "watchers_count": 0,
        "subscribers_count": 0,
        "forks_count": 0,
        "latest_release": null,
        "open_pull_requests": 0,
        "closed_pull_requests": 0,
        "total_commits": 9
    }
}
```
