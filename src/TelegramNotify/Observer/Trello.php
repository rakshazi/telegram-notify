<?php
namespace Rakshazi\TelegramNotify\Observer;

class Trello implements \SplObserver
{
    protected $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function update(\SplSubject $bot)
    {
        $notifications = $this->getNotifications();

        foreach ($notifications as $notification) {
            $title = '';
            foreach ($notification->entities as $entity) {
                $title.=$entity->text.' ';
            }
            $bot->sendMessage(
                'Trello',
                $title,
                ''
            );
        }

        $this->markAsRead();
    }

    protected function getNotifications()
    {
        $url = 'https://api.trello.com/1/members/me/notifications?';
        $url.=http_build_query([
            'key' => $this->config['api_key'],
            'token' => $this->config['token'],
            'unread' => 'true',
            'fields' => '',
            'memberCreator' => 'false',
            'entities' => 'true',
        ]);

        $raw = file_get_contents($url);
        return json_decode($raw);
    }

    protected function markAsRead()
    {
        $fields = http_build_query([
            'key' => $this->config['api_key'],
            'token' => $this->config['token']
        ]);
        $ch = curl_init('https://api.trello.com/1/notifications/all/read');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);
    }
}
