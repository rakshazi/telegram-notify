<?php
namespace Rakshazi;

class TelegramNotify
{
    /**
     * Configuration data
     * @var array
     */
    protected $config;

    /**
     * Function check if message already sent
     * @var callable
     */
    protected $isSent;

    public function __construct(array $config, callable $isSent)
    {
        $this->config = $config;
        $this->isSent = $isSent;
    }

    /**
     * Run notification bod
     */
    public function run()
    {
        foreach ($this->getConfig('notifications') as $config) {
            $this->runChat($config);
        }
    }

    /**
     * Send notifications for specified chat_id
     * @param array $config
     */
    protected function runChat(array $config)
    {
        $bot = new \Rakshazi\TelegramNotify\Bot;
        $bot->setToken($this->getConfig('token'));
        $bot->setChatId($config['chat_id']);
        $bot->setParseMode($config['parse_mode']);
        $bot->setIsSent($this->isSent);
        foreach ($config['notify'] as $observerConfig) {
            $class = '\Rakshazi\TelegramNotify\Observer\\'.$observerConfig['type'];
            $bot->attach(new $class($observerConfig['source']));
        }

        $bot->notify();
    }

    /**
     * Get config
     * @param string $key
     * @return mixed|null
     */
    protected function getConfig(string $key)
    {
        return $this->config[$key] ?? null;
    }
}
