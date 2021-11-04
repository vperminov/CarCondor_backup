<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$shortOpts = 'u:';
$shortOpts .= 'p:';
$shortOpts .= 'd:';
$shortOpts .= 'f:';
$shortOpts .= 'h::';
$shortOpts .= 'o::';

$longOpts = [
    'user:',
    'password:',
    'database:',
    'folder:',
    'host::',
    'port::'
];

$options = getopt($shortOpts, $longOpts);


$dbUser = $options['u'] ?? $options['user'] ?? false;
$dbPass = $options['p'] ?? $options['password'] ?? false;
$dbName = $options['d'] ?? $options['database'] ?? false;
$folder = $options['f'] ?? $options['folder'] ?? false;
$dbHost = $options['h'] ?? $options['host'] ?? '127.0.0.1';
$dbPort = $options['o'] ?? $options['port'] ?? '3306';

if (!$dbUser || !$dbPass || !$dbName || !$folder) {
    echo "Not all params provided \n";
    exit;
}

if (!is_dir($folder)) {
    echo "Folder not exist \n";
    exit;
}

$host = $dbHost . ':' . $dbPort;

$backUp = new Backup($host, $dbUser, $dbPass, $dbName, $folder);
$backUp->connect();
$backUp->makeBackup();

class Backup
{
    /**
     * @var string
     */
    private $host;

    /**
     * @var string
     */
    private $dbUser;

    /**
     * @var string
     */
    private $dbPass;

    /**
     * @var string
     */
    private $dbName;

    /**
     * @var string
     */
    private $folder;

    /**
     * @var mysqli
     */
    private $connection;

    /**
     * @param string $host
     * @param string $dbUser
     * @param string $dbPass
     * @param string $dbName
     * @param string $folder
     */
    public function __construct(string $host, string $dbUser, string $dbPass, string $dbName, string $folder)
    {
        $this->host = $host;
        $this->dbUser = $dbUser;
        $this->dbPass = $dbPass;
        $this->dbName = $dbName;
        $this->folder = $folder;
    }


    /**
     * @throws Exception
     */
    public function connect(): void
    {
        $this->connection = new mysqli($this->host, $this->dbUser, $this->dbPass, $this->dbName);
        if ($this->connection->connect_errno) {
            throw new RuntimeException('mysqli connection error: ' . $this->connection->connect_error);
        }
    }


    public function makeBackup(): void
    {
        $this->connection->query("SET NAMES 'utf8'");

        $tables = [];
        $result = $this->connection->query('SHOW TABLES');

        while ($row = $result->fetch_row()) {
            $tables[] = $row[0];
        }

        $res = '';

        foreach ($tables as $table) {
            $result = $this->connection->query('SELECT * FROM ' . $table);
            $numRows = mysqli_num_rows($result);

            $res .= 'DROP TABLE IF EXISTS ' . $table . ';';

            $rows = $this->connection->query('SHOW CREATE TABLE ' . $table)->fetch_row();

            $res .= "\n\n" . $rows[1] . ";\n\n";
            $counter = 1;

            for ($i = 0; $i < $result->field_count; $i++) {   //Over rows
                while ($row = $result->fetch_row()) {
                    if ($counter == 1) {
                        $res .= 'INSERT INTO ' . $table . ' VALUES(';
                    } else {
                        $res .= '(';
                    }

                    for ($j = 0; $j < $result->field_count; $j++) {
                        $row[$j] = addslashes($row[$j]);
                        $row[$j] = str_replace("\n", "\\n", $row[$j]);
                        if (isset($row[$j])) {
                            $res .= '"' . $row[$j] . '"';
                        } else {
                            $res .= '""';
                        }
                        if ($j < ($result->field_count - 1)) {
                            $res .= ',';
                        }
                    }

                    if ($numRows == $counter) {
                        $res .= ");\n";
                    } else {
                        $res .= "),\n";
                    }
                    $counter++;
                }
            }
            $res .= "\n\n\n";
        }
        $this->saveFile($res);
    }


    /**
     * @param string $res
     */
    private function saveFile(string $res): void
    {
        $fileName = $this->folder . '/db-backup-' . date('d.m.Y:H.i.s') . '.sql';

        $handle = fopen($fileName, 'w+');

        fwrite($handle, $res);
        if (fclose($handle)) {
            echo "\nBackup done, the file name is: " . $fileName . "\n";
            exit;
        }
    }
}
