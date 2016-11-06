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
        $string = file_get_contents($url);
        $this->xml = new \SimpleXMLElement($string);
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
