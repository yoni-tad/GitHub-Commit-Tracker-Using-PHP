# GitHub Commit Tracker

## Overview

GitHub Commit Tracker is a PHP script that allows you to fetch and count commits from both public and private repositories for a specific day. It handles pagination and provides accurate commit counts for a given date.

## Features

- Fetch commits for public and private repositories.
- Handle pagination to ensure all commits are counted.
- Easy setup with a GitHub personal access token.

## Getting Started

### Prerequisites

- PHP 7.4 or higher
- A GitHub personal access token with the `repo` scope

### Setup

1. **Clone the Repository**

- Clone the repository to your local machine:
   ```bash
   git clone https://github.com/your-username/github-commit-tracker.git
   cd github-commit-tracker

2. **Update the Script**

- Open the index.php file in a text editor and replace the following placeholders with your GitHub details:
   ```bash
   $owner = 'your-username-or-organization';
   $token = 'your-github-token';
- $owner: Your GitHub username or organization name.
- $token: Your GitHub personal access token.

3. **Run the Script**

- You can execute the script from the command line using:
   ```bash
   php index.php


### Code Explanation

- getAllRepositories($token): Fetches all repositories for the authenticated user, handling pagination to ensure all repositories are retrieved.

- getCommitsForDay($repoOwner, $repoName, $date, $token): Retrieves commits for a specific repository on a given date, handling pagination to ensure all commits are counted.

### Example Output

- When running the script, you will get output similar to the following:

  ```bash
  Date: 2024-07-22, Total Commits: 123
- This output shows the date and the total number of commits made across all repositories for that date.

### Contributing

- We welcome contributions to this project! If you have improvements, bug fixes, or feature requests, please feel free to:

- Open an issue to discuss changes or report problems.
- Submit a pull request with your proposed changes.





   
