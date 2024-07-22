<?php

$owner = 'yoni-tad';
$token = '';

// Function to get all repositories for the owner with pagination
function getAllRepositories($token) {
    $repos = [];
    $page = 1;
    do {
        $url = "https://api.github.com/user/repos?per_page=100&page=$page";
        $headers = [
            "Authorization: token $token",
            "User-Agent: request"
        ];

        $options = [
            'http' => [
                'header' => implode("\r\n", $headers),
                'method' => 'GET',
                'timeout' => 10 // Set a timeout for the request
            ]
        ];

        $context = stream_context_create($options);
        $response = @file_get_contents($url, false, $context);

        if ($response === FALSE) {
            die('Error: Unable to fetch repositories. Check your token and permissions.');
        }

        $pageRepos = json_decode($response, true);
        $repos = array_merge($repos, $pageRepos);
        $page++;
    } while (count($pageRepos) === 100); // GitHub API returns up to 100 items per page

    return $repos;
}

// Function to get commits for a specific day for a specific repository with pagination
function getCommitsForDay($repoOwner, $repoName, $date, $token) {
    $commits = [];
    $page = 1;
    do {
        $url = "https://api.github.com/repos/$repoOwner/$repoName/commits?since=" . $date . "T00:00:00Z&until=" . $date . "T23:59:59Z&per_page=100&page=$page";
        $headers = [
            "Authorization: token $token",
            "User-Agent: request"
        ];

        $options = [
            'http' => [
                'header' => implode("\r\n", $headers),
                'method' => 'GET',
                'timeout' => 10 // Set a timeout for the request
            ]
        ];

        $context = stream_context_create($options);
        $response = @file_get_contents($url, false, $context);

        if ($response === FALSE) {
            die('Error: Unable to fetch commits. Check your token and permissions.');
        }

        $pageCommits = json_decode($response, true);
        $commits = array_merge($commits, $pageCommits);
        $page++;
    } while (count($pageCommits) === 100); // GitHub API returns up to 100 items per page

    return count($commits);
}

// Get the date for which to fetch commits (e.g., today)
$date = new DateTime('now', new DateTimeZone('UTC'));
$dateString = $date->format('Y-m-d');

// Fetch all repositories
$repos = getAllRepositories($token);

if (!is_array($repos)) {
    die('Error: Unable to fetch repositories. Please check your token and permissions.');
}

// Fetch commits for the specific day
$totalCommitCount = 0;
foreach ($repos as $repo) {
    $repoName = $repo['name'];
    $repoOwner = $repo['owner']['login'];
    $totalCommitCount += getCommitsForDay($repoOwner, $repoName, $dateString, $token);
}

echo "Date: $dateString, Total Commits: $totalCommitCount\n";

?>
