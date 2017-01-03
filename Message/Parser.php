<?php
/**
 * nepda Internetdienstleistungen
 * Nepomuk Fraedrich
 * http://nepda.eu/
 *
 * PHP Version >= 7.0
 *
 * @author    Nepomuk Fraedrich <info@nepda.eu>
 * @copyright 2017 Nepomuk Fraedrich
 */

namespace Message;

/**
 * Class Parser
 */
class Parser
{
    protected $dir = '/home/nepda/.purple/logs';

    /**
     * @param Message[] $messages
     * @param \DateTime $minDate
     */
    public function parse(array &$messages, \DateTime $minDate = null)
    {
        $dir = new \DirectoryIterator($this->dir);
        foreach ($dir as $f) {

            if ($f->isDir() && $f->getBasename() != '.' && $f->getBasename() != '..') {
                $protocol = $f->getBasename();

                $protocols = new \DirectoryIterator($f->getRealPath());
                foreach ($protocols as $d) {

                    if ($d->isDir() && $d->getBasename() != '.' && $d->getBasename() != '..') {

                        $account = $d->getBasename();
                        echo sprintf('Parse account %s', $account) . PHP_EOL;

                        $buddies = new \DirectoryIterator($d->getRealPath());
                        foreach ($buddies as $b) {

                            if ($b->isDir() && $b->getBasename() != '.' && $b->getBasename() != '..') {

                                $buddy = $b->getBasename();
                                echo sprintf('  Buddy %s', $buddy) . PHP_EOL;

                                $logs = new \DirectoryIterator($b->getRealPath());

                                foreach ($logs as $l) {
                                    if ($l->isFile()) {
                                        if (substr($l->getBasename(), -5) !== '.html') {
                                            continue;
                                        }
                                        $filename = $l->getRealPath();
                                        echo sprintf('    Parsing logfile %s', $filename) . PHP_EOL;
                                        $this->parseLog($filename, $messages, $minDate);
                                    }
                                }
                            }
                        }
                        echo PHP_EOL;
                    }
                }
            }
        }
    }

    /**
     * @param string $filename
     * @param Message[] $messages
     * @param \DateTime $minDate
     * @return Message[]
     */
    public function parseLog(string $filename, array &$messages, \DateTime $minDate = null)
    {
        $content = file_get_contents($filename);
        $date = new \DateTime(basename($filename, '.html'));

        $lines = explode(PHP_EOL, $content);
        $firstLine = array_shift($lines);

        preg_match(
            '/Conversation with (?P<from>[0-9a-z\@\.\-\ ]+) at (?P<date>.*) on (?P<to>.*) \(/i',
            $firstLine,
            $matches
        );

        $from = null;
        if (!empty($matches['from'])) {
            $from = $matches['from'];
        }
        $to = null;
        if (!empty($matches['to'])) {
            $to = $matches['to'];
        }

        /** @var Message|null $lastMsg */
        $lastMsg = null;
        foreach ($lines as $line) {
            $line = strip_tags($line);
            if (empty($line)) {
                continue;
            }
            // Check if first chars are a timestamp, if not append the line to the last message
            if (!preg_match('/^\(([0-9]{2})([0-9:\.-_\ ]+)\)/', $line)) {
                if ($lastMsg) {
                    $line = $lastMsg->getMessage() . PHP_EOL . $line;
                    $msgId = md5($date->format('Y-m-d H:i:s') . $line);
                    $lastMsg->setMessage($line)
                        ->setId($msgId);
                }
                continue;
            }

            $msgId = md5($date->format('Y-m-d H:i:s') . $line);
            $time = substr($line, 1, strpos($line, ')') - 1);

            $ts = null;
            try {
                $ts = new \DateTime($date->format('Y-m-d') . ' ' . $time);
            } catch (\Throwable $t) {
                #echo $t->getMessage() . PHP_EOL;
            }
            if (!$ts) {
                try {
                    $ts = new \DateTime($time);
                } catch (\Throwable $t) {
                    #   echo $t->getMessage() . PHP_EOL;
                }
            }

            if (!$ts) {
                continue;
            }

            if ($minDate) {
                if ($ts < $minDate) {
                    continue;
                }
            }

            $msg = substr($line, strpos($line, ') ') + 2);

            $msg = trim($msg);
            $entity = new Message();
            $entity
                ->setDate($ts)
                ->setFrom($from)
                ->setId($msgId)
                ->setMessage($msg)
                ->setTo($to);

            $lastMsg = $entity;

            $messages[] = $entity;
        }
        return $messages;
    }
}
