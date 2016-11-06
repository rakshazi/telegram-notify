<?php
namespace Rakshazi\TelegramNotify\Observer;

class Email implements \SplObserver
{
    /**
     * Email configuration
     * @var array
     */
    protected $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function update(\SplSubject $bot)
    {
        foreach ($this->getUnread() as $message) {
            $bot->sendMessage('Email', $message['title'], $message['text']);
        }
    }

    protected function getUnread()
    {
        $return = [];
        $inbox = imap_open(
            $this->config['mailbox'],
            $this->config['user'],
            $this->config['password']
        );
        if (!$inbox) {
            $return[] = [
                'title' => $this->config['user'].' error',
                'text' => 'Error connecting to '.$this->config['mailbox'].': '.imap_last_error()
            ];

            return $return;
        }
        $emails = imap_search($inbox, 'UNSEEN');
        if ($emails) {
            rsort($emails); //Put new mail on top
            foreach ($emails as $id) {
                $return[] = [
                    'title' => $this->getTitle($inbox, $id, 0),
                    'text' => $this->getText($inbox, $id, 2),
                ];
            }
        }
        imap_close($inbox);

        return $return;
    }

    protected function getText($inbox, int $id, int $offset): string
    {
        $raw = imap_fetchbody($inbox, $id, $offset);
        $raw = quoted_printable_decode($raw);
        $raw = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $raw);
        $raw = preg_replace('#<style(.*?)>(.*?)</style>#is', '', $raw);
        $raw = strip_tags($raw, '<a><b><strong><i><em><code><pre>');
        $raw = preg_replace('%<(.*?)[^>]*>\ \\s*</\\1>%', '', $raw);
        $raw = str_replace('href="#', 'href="http://example.com', $raw);
        $raw = str_replace('&laquo;', '"', $raw);
        $raw = str_replace('&raquo;', '"', $raw);
        return preg_replace('/(\r\n|\r|\n|\s+\n|\&nbsp\;)+/', "\n", $raw);
    }

    protected function getTitle($inbox, int $id, int $offset): string
    {
        $header = imap_headerinfo($inbox, $id, $offset);

        $title = iconv_mime_decode($header->subject);
        $title.= ' from '.iconv_mime_decode($header->from[0]->personal);
        $title.= ' ('.$header->from[0]->mailbox.'@'.$header->from[0]->host.')';

        return $title;
    }
}
