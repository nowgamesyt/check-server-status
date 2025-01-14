<?php
if (isset($_POST['name']) && isset($_POST['host'])) {
    $port = 80;

    if (!empty($_POST['port'])) {
        $port = $_POST['port'];
    }

    addServer($_POST['name'], $_POST['host'], $port);

    header('Location: index.php');
} elseif (isset($_GET['del'])) {
    $index = (int) $_GET['del'];
    if ($index >= 0) {
        deleteServer($index);
    }
}
?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta name="robots" content="noindex">
        <meta charset="utf-8">
        <title>Server(s) Status</title>
        <link href="css/bootstrap.css" rel="stylesheet">
        <link href="css/bootstrap-theme.css" rel="stylesheet">
    </head>
    <body>
    <div class="container">
        <h3>Server(s) Status</h3>
        <form class="form-inline" role="form" action="index.php" method="post">
            <div class="form-group">
                <input type="text" class="form-control" id="name" name="name" placeholder="Name">
            </div>
            <div class="form-group">
                <input type="text" class="form-control" onkeyup="javascript:checkForm(this)" id="host" name="host"
                       placeholder="Domain / IP">
            </div>
            <div class="form-group">
                <input type="text" size="5" class="form-control" id="port" name="port" placeholder="Port">
            </div>
            <button type="submit" class="btn btn-default" id="add-button">Add</button>
        </form>
        <br>
        <footer>
            <a href="https://github.com/liluoao">@liluoao</a>
        </footer>
    </div>
    <script src="js/jquery.js" type="text/javascript"></script>
    <script src="js/app.js" type="text/javascript"></script>
    <script src="js/bootstrap.min.js" type="text/javascript"></script>
    </body>
    </html>
<?php

function getStatus($ip, $port)
{
    $socket = @fsockopen($ip, $port, $errorNo, $errorStr, 2);
    if (!$socket) {
        return false;
    } else {
        return true;
    }
}

function addServer($name, $host, $port)
{
    // TODO : rewrite the opening part correctly (better errors management)
    $i = 0;
    $filename = 'servers.xml';

    $servers = file_get_contents('servers.xml');
    if (trim($servers) == '') {
        exit();
    } else {
        $servers = simplexml_load_file('servers.xml');
        foreach ($servers as $server) {
            $i++;
        }
    }

    $servers = simplexml_load_file($filename);
    $server = $servers->addChild('server');

    $server->addAttribute('id', (string) $i);
    if (strlen($name) == 0) {
        $name = $host;
    }
    $server->addChild('name', (string) $name);
    $server->addChild('host', (string) $host);
    $server->addChild('port', (string) $port);
    $servers->asXML($filename);
}

function parser()
{
    //TODO : Fix errors when no valid XML content inside file.
    $file = 'servers.xml';
    if (file_exists($file)) {
        $servers = file_get_contents('servers.xml');
        if (trim($servers) == '') { //File exists but empty
            $content = '<?xml version="1.0" encoding="UTF-8"?><servers></servers>';
            file_put_contents($file, $content, FILE_APPEND | LOCK_EX);
        } else {
            $servers = simplexml_load_file('servers.xml');
            foreach ($servers as $server) {
                echo '<tr>';
                echo '<td>'.$server->name.'</td>';
                if (filter_var($server->host, FILTER_VALIDATE_IP)) {
                    echo '<td class="text-center">N/A</td><td class="text-center">'.$server->host.'</td>';
                } else {
                    echo '<td class="text-center">'.$server->host.'</td><td class="text-center">'.gethostbyname($server->host).'</td>';
                }

                echo '<td class="text-center">'.$server->port.'</td>';

                if (getStatus((string) $server->host, (string) $server->port)) {
                    echo '<td class="text-center"><span class="label label-success">Online</span></td>';
                } else {
                    echo '<td class="text-center"><span class="label label-danger">Offline</span></td>';
                }
                echo '<td class="text-center deleteMode"><a href="index.php?del='.$server->attributes().'" style="text-decoration:none"><b style="color:red;">X</b></a></td>';
                echo '</tr>';
            }
        }
    } else {
        // TODO : detect creation errors (ex : permissions)
        $content = '<?xml version="1.0" encoding="UTF-8"?><servers></servers>';
        file_put_contents($file, $content, FILE_APPEND | LOCK_EX);
    }
}

function deleteServer($index)
{
    $file = 'servers.xml';

    $serverFile = new DOMDocument();
    $serverFile->formatOutput = true;
    $serverFile->load($file);
    $servers = $serverFile->documentElement;
    $list = $servers->getElementsByTagName('server');
    $nodeToRemove = null;

    foreach ($list as $server) {
        $attrValue = $server->getAttribute('id');
        if ((int) $attrValue == $index) {
            $nodeToRemove = $server;
        }
    }

    if ($nodeToRemove != null) {
        $servers->removeChild($nodeToRemove);
    }

    $serverFile->save($file);
    header('Location: index.php');
}
