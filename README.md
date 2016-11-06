# Telegram Notify
[![Latest Stable Version](https://poser.pugx.org/rakshazi/telegram-notify/v/stable)](https://packagist.org/packages/rakshazi/telegram-notify) [![Total Downloads](https://poser.pugx.org/rakshazi/telegram-notify/downloads)](https://packagist.org/packages/rakshazi/telegram-notify) [![Latest Unstable Version](https://poser.pugx.org/rakshazi/telegram-notify/v/unstable)](https://packagist.org/packages/rakshazi/telegram-notify) [![License](https://poser.pugx.org/rakshazi/telegram-notify/license)](https://packagist.org/packages/rakshazi/telegram-notify)

This library collects data from multiple services, converts (transforms) it and send you as Telegram Message.

# Usage

1. [Create Telegram Bot](https://core.telegram.org/bots#creating-a-new-bot) - you need only token here
2. Add bot to any chat or send it a message directly
2. Open URL `https://api.telegram.org/bot<BOT TOKEN>/getUpdates` (replace `<BOT TOKEN>` with your bot token) and get your chat id(-s).
3. Write configuration array for Telegram Notify library:

```php
<?php
$config = [
    'token' => '<BOT TOKEN>',
    'notifications' => [
        [
            'chat_id' => '<YOUR CHAT ID>',
            'parse_mode' => 'HTML', //currently supported only 'HTML'
            'notify' => [ //List of integrations, you can add multiple accounts for each service and multiple services
                [
                    //RSS integration will post you messages with new items in feed
                    'type' => 'RSS', //Integration type
                    'source' => 'https://www.teamoctos.com/category/changelog/feed/' //Feed source URL
                ],
                [
                    'type' => 'Email', //Works with any IMAP server
                    'source' => [
                        'mailbox' => '{imap.gmail.com:993/imap/ssl}INBOX', //@link https://secure.php.net/manual/ru/function.imap-open.php
                        'user' => 'example@gmail.com', // your login
                        'password' => 'example-password' // your password
                    ]
                ],
                [
                    'type' => 'Trello', //Get all unread notifications from trello
                    'source' => [
                        'api_key' => 'trello-app-key', //https://trello.com/app-key
                        'token' => 'trello-user-token' //https://developers.trello.com/authorize
                    ],
                ]
            ],
        ],
    ],
];
```

Good! Now you need create function, which will check if message was already sent.

I use [mauris/packer](https://github.com/mauris/Packer-PHP) (you need **dev-master** version for `exists` function)
as storage for already sent messages, here is it:

```php
<?php
//Arguments: chat id, message text
//Must return bool
$isSent = function (string $chatId, string $message) {
    $key = md5($chatId.$message);
    $sent = new \Packer\Packer('./sent.pack');
    if ($sent->exists($key)) {
        return true;
    }
    $sent->write($key, $message);

    return false;
};
```

Very good! Final run script must be something like this (I use it for my private integration):

```php
<?php
require './vendor/autoload.php';
$config = [
    'token' => 'toke',
    'notifications' => [
        [
            'chat_id' => 'chat_id',
            'parse_mode' => 'HTML',
            'notify' => [
                [
                    'type' => 'RSS',
                    'source' => 'https://www.teamoctos.com/category/changelog/feed/'
                ],
                [
                    'type' => 'Email',
                    'source' => [
                        'mailbox' => '{imap.gmail.com:993/imap/ssl}INBOX',
                        'user' => 'example@gmail.com',
                        'password' => 'example-password'
                    ]
                ],
                [
                    'type' => 'Trello',
                    'source' => [
                        'api_key' => 'key',
                        'token' => 'token'
                    ],
                ]
            ],
        ],
    ],
];

$isSent = function (string $chatId, string $message) {
    $key = md5($chatId.$message);
    $sent = new \Packer\Packer('./sent.pack');
    if ($sent->exists($key)) {
        return true;
    }
    $sent->write($key, $message);

    return false;
};

$notify = new \Rakshazi\TelegramNotify($config, $isSent);
$notify->run();
```
