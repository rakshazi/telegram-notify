<?php
namespace Rakshazi\TelegramNotify\Observer;

class Trello implements \SplObserver
{
    protected $config;
    protected $curl;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->curl = new \Curl\Curl;
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
                '',
                $title
            );
        }

        $this->markAsRead();
    }

    protected function getNotifications()
    {
        $this->curl->get('https://api.trello.com/1/members/me/notifications', [
            'key' => $this->config['api_key'],
            'token' => $this->config['token'],
            'unread' => 'true',
            'fields' => '',
            'memberCreator' => 'false',
            'entities' => 'true',
        ]);

        return $this->curl->response;
    }

    protected function markAsRead()
    {
        $this->curl->post('https://api.trello.com/1/notifications/all/read', [
            'key' => $this->config['api_key'],
            'token' => $this->config['token']
        ]);
    }
}
