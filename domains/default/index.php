<?php
error_reporting(E_ALL);

/**
 * config
 */
$orders = [
    (object)[
        'key' => 'name',
        'direction' => 'asc',
        'icon' => 'tasks',
    ],
    (object)[
        'key' => 'name',
        'direction' => 'desc',
        'icon' => 'tasks',
    ],
    (object)[
        'key' => 'date',
        'direction' => 'asc',
        'icon' => 'calendar',
    ],
    (object)[
        'key' => 'date',
        'direction' => 'desc',
        'icon' => 'calendar',
    ],

];

$suffix = "\.local";
$dir = "/vagrant/domains/";

/**
 * read directories
 */
$dh = opendir($dir);
$filenames = [];
while (false !== ($filename = readdir($dh))) {

    if (preg_match("'" . $suffix . "$'", $filename)) {

        # file url
        if (file_exists($dir . $filename . '/web/app_dev.php')) {
            $baseurl = '/app_dev.php';
        } else {
            $baseurl = '/';
        }

        # mod time
        $mtime = filemtime($dir . $filename);
        switch ($_GET['k']) {
            default:
            case 'name':
                $key = $filename;
                break;
            case 'date':
                $key = $mtime . $filename;
                break;
        }

        # name, domain, url
        $name = $filename;
        $url = 'http://' . $filename . $baseurl;
        $domain = preg_replace("'" . $suffix . "'", '', $url);

        # tags
        $tags = explode('.', $filename);
        unset($tags[count($tags) - 1]);
        foreach ($tags as $tag) {
            $domainTags[$tag][] = $key;
        }
        $hosts[] = $filename;

        if ((!empty($_GET['t']) && in_array($_GET['t'], $tags)) || empty($_GET['t'])) {
            $domains[$key] = (object)[
                'code' => basename(dirname(readlink($dir . $filename))),
                'url' => $url,
                'domain' => $domain,
                'name' => $name,
                'tags' => $tags,
                'date' => date('Y-m-d H:i:s', $mtime),
            ];
        }
    }
}

/**
 * sort data
 */
ksort($domainTags);
switch ($_GET['d']) {
    default:
    case 'asc':
        ksort($domains);
        break;
    case 'desc':
        krsort($domains);
        break;
}
?>
<!DOCTYPE html>
<html>
    <head>
        <title>VServer</title>
        <meta charset="UTF-8">
        <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
        <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap-theme.min.css">
        <script src="//code.jquery.com/jquery-2.1.4.min.js"></script>
        <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
    </head>
    <body>
    <section class="container">
        <div class="row">
            <div class="col-sm-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">
                            <span class="glyphicon glyphicon-certificate"></span>
                            VServer
                        </h3>
                    </div>
                    <? if(empty($domains)): ?>
                        <div class="panel-body">
                            <em class="glyphicon glyphicon-info"></em>
                            No directories configured. Create one with suffix <code><?= stripslashes($suffix) ?></code> in <code><?= $dir ?></code> on virtual machine and add it to hosts file in local machine.
                        </div>
                    <? else: ?>
                        <div class="panel-body">
                            <div class="btn-group" role="group">
                                <? foreach ($orders as $order): ?>
                                    <a class="btn btn-default<? if ($_GET['k'] == $order->key AND $_GET['d'] == $order->direction): ?> active<? endif; ?>" href="?k=<?= $order->key ?>&d=<?= $order->direction ?>&t=<?= $_GET['t'] ?>">
                                        <span class="glyphicon glyphicon-<?= $order->icon ?>"></span>
                                        <span class="glyphicon glyphicon-chevron-<?= ($order->direction == "asc") ? "up" : "down" ?>"></span>
                                    </a>
                                <? endforeach; ?>
                            </div>
                            <div class="btn-group pull-right" role="group">
                                <a class="btn btn-default" data-toggle="modal" data-target="#myModal">
                                    <span class="glyphicon glyphicon-cog"></span>
                                </a>

                                <div class="modal fade" id="myModal">
                                    <div class="modal-dialog modal-sm">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                <h4 class="modal-title">Hosts config</h4>
                                            </div>
                                            <div class="modal-body">
                                                <code>
                                                    <?= $_SERVER['SERVER_ADDR'] ?>
                                                    <?= implode("\t", $hosts) ?>
                                                </code>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <a class="btn btn-default dropdown-toggle<? if (!empty($_GET['t'])): ?> active<? endif; ?>" data-toggle="dropdown">
                                    <span class="glyphicon glyphicon-tag"></span>
                                    <span class="caret"></span>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-right" role="menu">
                                    <? if (!empty($_GET['t'])): ?>
                                        <li>
                                            <a href="?k=<?= $_GET['k'] ?>&d=<?= $_GET['d'] ?>&t=">
                                                reset
                                            </a>
                                        </li>
                                        <li class="divider"></li>
                                    <? endif; ?>
                                    <? foreach ($domainTags as $tag => $keys): ?>
                                        <li<? if ($_GET['t'] == $tag): ?> class="active"<? endif; ?>>
                                            <a href="?k=<?= $_GET['k'] ?>&d=<?= $_GET['d'] ?>&t=<?= $tag ?>">
                                                <?= $tag ?>
                                            </a>
                                        </li>
                                    <? endforeach; ?>
                                </ul>

                            </div>
                        </div>
                        <div class="list-group">
                            <? foreach ($domains as $domain): ?>
                                <a href="<?= $domain->url ?>" class="list-group-item">
                                    <? foreach ($domain->tags as $tag): ?>
                                        <span class="text"><?= $tag ?></span>
                                    <? endforeach ?>
                                    <!--                            <span class="text">--><? //= $domain->name ?><!--</span>-->
                                    <span class="label label-success"><?= $domain->code ?></span>
                                    <span class="badge"><?= $domain->date ?></span>
                                </a>
                            <? endforeach ?>
                        </div>
                    <? endif; ?>
                </div>
            </div>
        </div>
    </body>
</html>
