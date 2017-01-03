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

if (!$groups) {
    var_dump($dbHandle->errorInfo());
}

$groups->execute();

?>
    <div style="float:left;width: 300px;font-size: small; border: 1px solid black;">
        <ul>
            <?php
            foreach ($groups->fetchAll() as $group) {
                echo '<li><a href="?group=' . urlencode(
                        $group['chat_group']
                    ) . '">' . $group['chat_group'] . '</a> (' . $group['log_count'] . ')</li>';
            }
            ?>
        </ul>
    </div>
<?php

if (isset($_GET['group'])) {

    $params = ['group' => $_GET['group']];
    $search = '';
    if (!empty($_GET['search'])) {
        $logs = $dbHandle->prepare('SELECT * FROM logs WHERE chat_group = :group AND message like :msg ORDER BY `date_created` DESC LIMIT 1000');
        $params['msg'] = $_GET['search'];
        $search = $_GET['search'];
    } else {
        $logs = $dbHandle->prepare('SELECT * FROM logs WHERE chat_group = :group ORDER BY `date_created` DESC LIMIT 1000');
    }
    $logs->execute($params);
    ?>
    <div style="margin-left: 320px; border: 1px solid black; font-size: small;">
        <form action="" method="get">
            <input type="hidden" name="group" id="group" value="<?php echo htmlspecialchars($_GET['group']); ?>">
            <input type="text" name="search" id="search" value="<?php echo htmlspecialchars($search); ?>">
            <input type="submit" value="Search">
        </form>
    <table border="1" style="font-size: small">
        <thead>
        <tr>
            <th><div style="width:130px;">Date</div></th>
            <th>Message</th>
        </tr>
        </thead>
        <tbody>
        <?php
        foreach ($logs as $log) {
            ?>
            <tr>
                <td><?php echo date('Y-m-d&\n\b\s\p;H:i:s', $log['date_created']); ?></td>
                <td><?php echo nl2br($log['message']); ?></td>
            </tr>
            <?php
        }
        ?>
        </tbody>
    </table>
    </div>
    <?php
}
