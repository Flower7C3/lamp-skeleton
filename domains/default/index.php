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
    </head>
    <body>
        <nav class="navbar navbar-default">
            <div class="container">
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <a class="navbar-brand" href="/">
                        <span class="glyphicon glyphicon-fire"></span>
                        VServer
                    </a>
                </div>
                <? if (!empty($domains)): ?>
                    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                        <ul class="nav navbar-nav">
                            <? foreach ($orders as $order): ?>
                                <li<? if ($_GET['k'] == $order->key AND $_GET['d'] == $order->direction): ?> class="active"<? endif; ?>>
                                    <a href="?k=<?= $order->key ?>&d=<?= $order->direction ?>&t=<?= $_GET['t'] ?>">
                                        <span class="glyphicon glyphicon-<?= $order->icon ?>"></span>
                                        <span class="glyphicon glyphicon-chevron-<?= ($order->direction == "asc") ? "up" : "down" ?>"></span>
                                        <span class="hidden-sm hidden-md hidden-lg">Order by <?= $order->key ?> <?= $order->direction ?></span>
                                    </a>
                                </li>
                            <? endforeach; ?>
                        </ul>
                        <form class="navbar-form navbar-left" role="search">
                            <div class="form-group">
                                <div class="form-group has-feedback">
                                    <span class="glyphicon glyphicon-filter form-control-feedback" aria-hidden="true"></span>
                                    <input class="form-control" id="searchinput" type="search" placeholder="Filter by domain..."/>
                                </div>
                            </div>
                        </form>
                        <ul class="nav navbar-nav navbar-right">
                            <li>
                                <a data-toggle="modal" href="#hostsConfig">
                                    <span class="glyphicon glyphicon-cog"></span>
                                    <span class="hidden-sm hidden-md hidden-lg">Show hosts config</span>
                                </a>

                                <div class="modal fade" id="hostsConfig">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                <h4 class="modal-title">Hosts config</h4>
                                            </div>
                                            <div class="modal-body">
                                                Copy following code and paste into <code>/etc/hosts</code> file.
                                                <textarea style="width: 100%;height: 400px; resize: none"><?= $_SERVER['SERVER_ADDR'] . "\t" . implode("\t", $hosts) ?></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            <li class="dropdown<? if (!empty($_GET['t'])): ?> active<? endif; ?>">
                                <a class="dropdown-toggle" data-toggle="dropdown">
                                    <span class="glyphicon glyphicon-tag"></span>
                                    <span class="hidden-sm hidden-md hidden-lg">Show tags</span>
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
                            </li>
                        </ul>
                    </div>
                <? endif; ?>
            </div>
        </nav>

        <section class="container">
            <div class="row">
                <div class="col-sm-12">
                    <? if (empty($domains)): ?>
                        <div class="alert alert-info">
                            <em class="glyphicon glyphicon-info-sign"></em>
                            No directories configured. Create one with suffix <code><?= stripslashes($suffix) ?></code> in <code><?= $dir ?></code> on virtual machine and add it to hosts file in local machine.
                        </div>
                    <? else: ?>
                        <div class="panel panel-default">
                            <div class="list-group" id="searchlist">
                                <? foreach ($domains as $domain): ?>
                                    <a href="<?= $domain->url ?>" class="list-group-item">
                                        <? foreach ($domain->tags as $tag): ?>
                                            <span class="text"><?= $tag ?></span>
                                        <? endforeach ?>
                                        <span class="hidden name"><?= $domain->name ?></span>
                                        <span class="label label-success"><?= $domain->code ?></span>
                                        <span class="badge"><?= $domain->date ?></span>
                                    </a>
                                <? endforeach ?>
                            </div>
                        </div>
                    <? endif; ?>
                </div>
            </div>
        </section>
        <script src="//code.jquery.com/jquery-2.1.4.min.js"></script>
        <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
        <script src="//labs.easyblog.it/bootstrap-list-filter/bootstrap-list-filter.src.js"></script>
        <script>
            $('#searchlist').btsListFilter('#searchinput', {itemChild: '.name', initial: false});
        </script>
    </body>
</html>
