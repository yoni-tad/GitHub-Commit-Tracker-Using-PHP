<?php

include 'assets/db.php';
include 'assets/bot.php';

function validateFormat($text)
{
    if (preg_match('/^username:([a-zA-Z0-9\-]+)\ntoken:([a-zA-Z0-9_\-]+)$/', $text, $matches)) {
        return [
            'username' => $matches[1],
            'token' => $matches[2]
        ];
    }
    return false;
}

function getTodayCommitCount($username, $token)
{
    $today = date('Y-m-d');
    $url = "https://api.github.com/user/repos";
    $headers = [
        "Authorization: token $token",
        "User-Agent: PHP"
    ];

    $opts = [
        'http' => [
            'method' => 'GET',
            'header' => implode("\r\n", $headers)
        ]
    ];
    $context = stream_context_create($opts);
    $repos = json_decode(file_get_contents($url, false, $context), true);

    if (!$repos) return false;

    $totalCommits = 0;

    foreach ($repos as $repo) {
        $repoOwner = $repo['owner']['login'];
        $repoName = $repo['name'];
        $commitsUrl = "https://api.github.com/repos/$repoOwner/$repoName/commits?since={$today}T00:00:00Z&until={$today}T23:59:59Z";

        $repoContext = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => implode("\r\n", $headers)
            ]
        ]);

        $commits = json_decode(file_get_contents($commitsUrl, false, $repoContext), true);

        if ($commits) {
            $totalCommits += count($commits);
        }
    }

    return $totalCommits;
}

// -------------------- BOT -----------------------

if ($text == '/start') {
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "Welcome to GitHub Commit Tracker Bot! \n\n👋 Hi there! I'm here to help you keep track of your daily commits on GitHub.\n\n📅 Every day, I'll notify you with the total number of commits you've made across all your repositories, both public and private.\n\n🔒 Rest assured, your data is secure with me.\n\nTo get started, please share your GitHub username and personal access token in the following format:\n\n```\nusername:your-github-username\ntoken:your-github-token\n```\nFor example:\n```\nusername:yoni-tad\ntoken:ghp_16pWRWSPZTW25huvSc9FWSqNvBe9ZMMLtisx\n```",
        'parse_mode' => 'Markdown',
        'reply_markup' => json_encode([
            'resize_keyboard' => true,
            'keyboard' => [
                [['text' => "Check Today Status🚀"]],
            ],
        ]),
    ]);
} elseif ($text == "/about") {
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "✅ *GitHub Commit Tracker Bot*\n\nThis bot helps you keep track of your daily commits on GitHub.\n\nDeveloped by: Yoni Tad\n\nFor any queries or support, contact me at @yonitad0.",
        'parse_mode' => 'Markdown'
    ]);
} elseif ($text == "Check Today Status🚀") {
    $query = mysqli_query($con, "SELECT * FROM `commit` WHERE `telegram_id` = '$chat_id' AND `status` = 'Active'");
    if (mysqli_num_rows($query) > 0) {
        $row = mysqli_fetch_assoc($query);
        $username = $row["user_name"];
        $token = $row["token"];

        $response = bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "Please wait ... 🛠",
        ]);

        $commitCount = getTodayCommitCount($username, $token);
        if ($commitCount !== false) {
            $message_id = $response->result->message_id;
            bot('deleteMessage', [
                'chat_id' => $chat_id,
                'message_id' => $message_id,
            ]);

            bot('sendMessage', [
                'chat_id' => $chat_id,
                'text' => "✅ Today's commit count for $username: $commitCount commits 📈",
            ]);
        } else {
            bot('sendMessage', [
                'chat_id' => $chat_id,
                'text' => "❌ There was an error fetching your commit data. Please ensure your GitHub token is correct and has the required permissions."
            ]);
        }
    } else {
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "❌ Null credential! Please send your GitHub username and personal access token in the following format:\n\n```\nusername:your-github-username\ntoken:your-github-token\n```\nFor example:\n```\nusername:yoni-tad\ntoken:ghp_16pWRWSPZTW25huvSc9FWSqNvBe9ZMMLtisx\n```",
            'parse_mode' => 'Markdown'
        ]);
    }
} else {
    $credentials = validateFormat($text);
    if ($credentials) {
        $username = $credentials['username'];
        $token = $credentials['token'];

        $response = bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "Please wait ...🛠"
        ]);

        $commitCount = getTodayCommitCount($username, $token);
        if ($commitCount !== false) {
            $query = mysqli_query($con, "SELECT * FROM `commit` WHERE `telegram_id` = '$chat_id' AND `status` = 'Active'");
            if (mysqli_num_rows($query) > 0) {
                $sql = mysqli_query($con, "UPDATE `commit` SET `user_name` = '$username', `token` = '$token', `status` = 'Active' WHERE `telegram_id` = '$chat_id'");
            } else {
                $sql = mysqli_query($con, "INSERT INTO `commit`(`telegram_id`, `user_name`, `token`, `status`) VALUES ('$chat_id','$username','$token','Active')");
            }
            if ($sql) {
                $message_id = $response->result->message_id;
                bot('deleteMessage', [
                    'chat_id' => $chat_id,
                    'message_id' => $message_id,
                ]);

                bot('sendMessage', [
                    'chat_id' => $chat_id,
                    'text' => "✅ Your credentials have been saved successfully!\n\nToday's commit count for $username: $commitCount commits 📈",
                    'reply_markup' => json_encode([
                        'resize_keyboard' => true,
                        'keyboard' => [
                            [['text' => "Check Today Status🚀"]],
                        ],
                    ]),
                ]);
            } else {
                bot('sendMessage', [
                    'chat_id' => $chat_id,
                    'text' => "❌ There was an error. Please try again."
                ]);
            }
        } else {
            bot('sendMessage', [
                'chat_id' => $chat_id,
                'text' => "❌ There was an error fetching your commit data. Please ensure your GitHub token is correct and has the required permissions."
            ]);
        }
    } else {
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "❌ Invalid format! Please send your GitHub username and personal access token in the following format:\n\n```\nusername:your-github-username\ntoken:your-github-token\n```\nFor example:\n```\nusername:yoni-tad\ntoken:ghp_16pWRWSPZTW25huvSc9FWSqNvBe9ZMMLtisx\n```",
            'parse_mode' => 'Markdown'
        ]);
    }
}
