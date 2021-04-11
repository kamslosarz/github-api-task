<?php

namespace App\Controller;

use App\AppException;
use App\Service\GithubComparator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ComparatorController extends AbstractController
{
    /**
     * @Route("/compare/{firstUser}/{firstRepo}/{secondUser}/{secondRepo}", defaults={}, name="app_compare", methods={"GET"})
     * @throws AppException
     */
    public function compare(GithubComparator $githubComparator,
        string $firstUser,
        string $firstRepo,
        string $secondUser,
        string $secondRepo): Response
    {
        $results = $githubComparator->compareByName($firstUser, $firstRepo, $secondUser, $secondRepo);

        $response = new JsonResponse();
        $response->setData($results);
        $response->setEncodingOptions($response->getEncodingOptions() | JSON_PRETTY_PRINT);

        return $response;
    }

    /**
     * @Route("/compare-links/first/{first}/second/{second}", name="app_compare_links", methods={"GET"}, requirements={"first"=".+", "second"=".+"})
     */
    public function compareLinks(GithubComparator $githubComparator, string $first, string $second): Response
    {
        $results = $githubComparator->compareByLinks($first, $second);

        $response = new JsonResponse();
        $response->setData($results);
        $response->setEncodingOptions($response->getEncodingOptions() | JSON_PRETTY_PRINT);

        return $response;
    }
}
