<?php
namespace Rakshazi\TelegramNotify;

class Bot implements \SplSubject
{
    /**
     * Observers
     * @var \SplObjectStorage
     */
    protected $observers;

    /**
     * Bot token
     * @var string
     */
    private $token;

    /**
     * Chat id
     * @var string
     */
    private $chatId;

    /**
     * Parse mode
     * @var string
     */
    private $parseMode;

    /**
     * Callback function check if message already sent
     * @var callable
     */
    private $isSent;

    protected $curl;

    public function __construct()
    {
        $this->observers = new \SplObjectStorage;
        $this->curl = new \Curl\Curl;
    }

    public function attach(\SplObserver $observer)
    {
        $this->observers->attach($observer);
    }

    public function detach(\SplObserver $observer)
    {
        $this->observers->detach($observer);
    }

    public function setChatId(string $id)
    {
        $this->chatId = $id;
    }

    public function setToken(string $token)
    {
        $this->token = $token;
    }

    public function setParseMode(string $mode)
    {
        $this->parseMode = $mode;
    }

    public function setIsSent(callable $callback)
    {
        $this->isSent = $callback;
    }

    public function getParseMode(): string
    {
        return $this->parseMode;
    }

    public function notify()
    {
        /** @var \SplObserver $observer */
        foreach ($this->observers as $observer) {
            $observer->update($this);
        }
    }

    public function sendMessage(string $type, string $title, string $body)
    {
        if (!($this->isSent)($this->chatId, $body)) {
            $text = ($this->parseMode == 'HTML') ? "<b>$type</b>: " : "$type: ";
            $text.= "$title\n\r\n$body";
            $this->curl->setHeader('Content-Type', 'application/json');
            $this->curl->post('https://api.telegram.org/bot'.$this->token.'/sendMessage', [
                'chat_id' => $this->chatId,
                'parse_mode' => $this->parseMode,
                'text' => $text
            ]);
        }
    }
}
