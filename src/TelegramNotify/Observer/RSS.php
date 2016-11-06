<?php
namespace Rakshazi\TelegramNotify\Observer;

class RSS implements \SplObserver
{
    /**
     * @var SimpleXMLElement
     */
    protected $xml;

    public function __construct(string $url)
    {
        $curl = new \Curl\Curl;
        $curl->get($url);
        $this->xml = new \SimpleXMLElement($curl->response);
        unset($curl);
    }

    public function update(\SplSubject $bot)
    {
        $messages = [];
        //RSS
        if (property_exists($this->xml, 'channel')) {
            foreach ($this->xml->xpath('//item') as $item) {
                $messages[] = [
                    'title' => $item->title,
                    'url' => $item->link
                ];
            }
        }

        if (property_exists($this->xml, 'entry')) {
            foreach ($this->xml->xpath('//entry') as $item) {
                $messages[] = [
                    'title' => $item->title,
                    'url' => $item->link->getAttribute('href'),
                ];
            }
        }

        foreach ($messages as $message) {
            $bot->sendMessage('RSS', $message['title'], $message['url']);
        }
    }
}
