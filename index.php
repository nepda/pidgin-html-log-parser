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

$dbFilename = 'logs.sqlite';
$dbHandle = new \PDO('sqlite:' . $dbFilename);

$groups = $dbHandle->prepare(
    'select 
        chat_group, 
        count(*) as log_count 
    from logs 
    group by chat_group 
    order by log_count desc'
);
$groups->execute();

$groupAliases = [];
if (file_exists('group-aliases.json')) {
    $groupAliases = json_decode(file_get_contents('group-aliases.json'), true);
}
$groupDisplayName = function (string $name) use ($groupAliases): string {
    $name = trim($name);
    if (!empty($groupAliases[$name])) {
        return $groupAliases[$name];
    }
    return $name;
};

?>
    <div style="float:left;width: 300px;font-size: small; border: 1px solid black;">
        <ul>
            <?php
            foreach ($groups->fetchAll() as $group) {
                echo '<li><a href="?group=' . urlencode($group['chat_group']) . '">' .
                    $groupDisplayName($group['chat_group']) . '</a> (' . $group['log_count'] . ')</li>';
            }
            ?>
        </ul>
    </div>
<?php

$params = [];

$groupFilter = null;
$messageFilter = null;

if (!empty($_GET['group'])) {
    $groupFilter = $_GET['group'];
    $params['group'] = $groupFilter;
}
if (!empty($_GET['messageFilter'])) {
    $messageFilter = $_GET['messageFilter'];
    $params['msg'] = $messageFilter;
}

if ($groupFilter && $messageFilter) {
    $logs = $dbHandle->prepare(
        'SELECT * FROM logs WHERE chat_group = :group AND message like :msg ORDER BY `date_created` DESC LIMIT 1000'
    );
} elseif ($groupFilter) {
    $logs = $dbHandle->prepare(
        'SELECT * FROM logs WHERE chat_group = :group ORDER BY `date_created` DESC LIMIT 1000'
    );
} elseif ($messageFilter) {
    $logs = $dbHandle->prepare(
        'SELECT * FROM logs WHERE message like :msg ORDER BY `date_created` DESC LIMIT 1000'
    );
} else {
    $logs = $dbHandle->prepare(
        'SELECT * FROM logs ORDER BY `date_created` DESC LIMIT 1000'
    );
}

$logs->execute($params);
?>
    <div style="margin-left: 320px; border: 1px solid black; font-size: small;">
        <form action="" method="get">
            <input type="hidden" name="group" id="group" value="<?php echo htmlspecialchars($_GET['group']); ?>">
            <input type="text" name="messageFilter" id="messageFilter"
                   value="<?php echo htmlspecialchars($messageFilter); ?>">
            <input type="submit" value="Search">
        </form>
        <table border="1" style="font-size: small">
            <thead>
            <tr>
                <th>
                    <div style="width:130px;">Date</div>
                </th>
                <th>Name</th>
                <th>Message</th>
            </tr>
            </thead>
            <tbody>
            <?php
            foreach ($logs as $log) {
                $name = '';
                $message = nl2br($log['message']);

                if (strpos($message, ': ')) {
                    $name = substr($message, 0, strpos($message, ': '));
                    $message = substr($message, strpos($message, ': ')+1);
                }

                ?>
                <tr>
                    <td><?php echo date('Y-m-d&\n\b\s\p;H:i:s', $log['date_created']); ?></td>
                    <td><?php echo $name; ?></td>
                    <td><?php echo $message; ?></td>
                </tr>
                <?php
            }
            ?>
            </tbody>
        </table>
    </div>
<?php
