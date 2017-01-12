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

error_reporting(E_ALL);
ini_set('display_errors', true);

require_once 'Message/Message.php';
require_once 'Message/Parser.php';

$parser = new Message\Parser();
$parser->setLogDir('/home/nepda/.purple/logs');

$minDate = null;

if (!empty($argv[1])) {
    $minDate = new \DateTime($argv[1]);
    echo 'Parsing since ' . $minDate->format('Y-m-d H:i:s') . PHP_EOL;
}

/** @var \Message\Message[] $messages */
$messages = [];
try {
    $parser->parse($messages, $minDate);
} catch (\Throwable $t) {
    echo $t->getMessage();
}


$dbFilename = 'logs.sqlite';
$dbHandle = new \PDO('sqlite:' . $dbFilename);

$res = $dbHandle->query(
    'CREATE TABLE IF NOT EXISTS logs (
`id` VARCHAR(50) UNIQUE,
`date_created` INTEGER,
`sender` VARCHAR(255),
`receiver` VARCHAR(255),
`chat_group` VARCHAR(255),
`message` TEXT
)'
);

$cnt = count($messages);
$i = 0;
foreach ($messages as $message) {

    $i++;

    $find = $dbHandle->prepare('SELECT count(*) FROM logs where id = :id');
    $res = $find->execute(['id' => $message->getId()]);
    $res = $find->fetchColumn();

    echo 'Saving message (' . $i . '/' . $cnt . ') ' . $message->getId() . PHP_EOL;

    if ($res > 0) {
        $stmt = $dbHandle->prepare(
            'UPDATE logs SET 
            `date_created` = :dateCreated, 
            `sender` = :sender,
            `receiver` = :receiver,
            `chat_group` = :chatGroup,
            `message` = :message
            WHERE `id` = :id
        '
        );
    } else {
        $stmt = $dbHandle->prepare(
            'INSERT INTO logs (`id`, `date_created`, `sender`, `receiver`, `chat_group`, `message`) 
        VALUES (:id, :dateCreated, :sender, :receiver, :chatGroup, :message)'
        );
    }

    if (!$stmt) {
        var_dump($dbHandle->errorInfo());
        continue;
    }

    $group = [$message->getFrom(), $message->getTo()];
    sort($group, SORT_STRING);
    $group = implode(', ', $group);

    $stmt->execute(
        [
            'id' => $message->getId(),
            'dateCreated' => $message->getDate()->getTimestamp(),
            'sender' => $message->getFrom(),
            'receiver' => $message->getTo(),
            'message' => $message->getMessage(),
            'chatGroup' => $group,
        ]
    );
}
