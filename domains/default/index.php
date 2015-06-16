<?php
error_reporting(E_ALL);

$suffix = "\.local";
$dir = "/vagrant/domains/";

/**
 * read directories
 */
$dh = opendir($dir);
$filenames = [];
while (false !== ($filename = readdir($dh))) {

    if (preg_match("'" . $suffix . "$'", $filename)) {

# domain, url, code, date
        $localDomain = $filename;
        $externalDomain = preg_replace("'" . $suffix . "'", '', $filename);

        $baseurl = '/' . (file_exists($dir . $filename . '/web/app_dev.php') ? 'app_dev.php' : '');
        $localUrl = 'http://' . $localDomain . $baseurl;
        $externalUrl = 'http://' . $externalDomain;

        $realPath = readlink($dir . $filename);

        $code = basename(dirname($realPath));

        $mtime = filemtime($dir . $filename);

        $tags = file_exists(dirname($realPath) . '/tags.txt') ? explode("\n", trim(file_get_contents(dirname($realPath) . '/tags.txt'))) : [];

        $domains[] = (object)[
            'code' => $code,
            'url' => $localUrl,
            'externalUrl' => $externalUrl,
            'name' => $externalDomain,
            'date' => date('Y-m-d H:i:s', $mtime),
            'tags' => $tags,
        ];

        $hosts[] = $filename;
    }
}

?>
<!DOCTYPE html>
<html>
    <head>
        <title>VServer</title>
        <meta charset="UTF-8">
        <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
        <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap-theme.min.css">
        <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/bootstrap-table/1.8.1/bootstrap-table.min.css">
    </head>
    <body>
        <nav class="navbar navbar-default">
            <div class="container">
                <div class="navbar-header">
                    <a class="navbar-brand" href="/">
                        <span class="glyphicon glyphicon-fire"></span>
                        VServer
                    </a>
                    <? if (!empty($domains)): ?>
                        <a class="navbar-toggle" data-toggle="modal" href="#hostsConfig">
                            <span class="glyphicon glyphicon-cog"></span>
                        </a>
                    <? endif; ?>
                </div>
                <? if (!empty($domains)): ?>
                    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                        <ul class="nav navbar-nav navbar-right">
                            <li>
                                <a data-toggle="modal" href="#hostsConfig">
                                    <span class="glyphicon glyphicon-cog"></span>
                                </a>
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
                        <table id="searchlist"
                               data-classes="table table-hover table-condensed table-striped"
                               data-toggle="table"
                               data-search="true"
                               data-pagination="true"
                               data-sort-name="date"
                               data-sort-order="desc">
                            <thead>
                                <tr>
                                    <th data-field="name" data-sortable="true">
                                        <em class="glyphicon glyphicon-file"></em> Name
                                    </th>
                                    <th data-field="code" data-sortable="true">
                                        <em class="glyphicon glyphicon-tasks"></em> Code
                                    </th>
                                    <th>
                                        <em class="glyphicon glyphicon-tag"></em> Tags
                                    </th>
                                    <th data-field="date" data-sortable="true">
                                        <em class="glyphicon glyphicon-calendar"></em> Date
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <? foreach ($domains as $i => $domain): ?>
                                    <tr id="tr-id-<?= $i ?>" class="tr-class-<?= $i ?>">
                                        <td>
                                            <a href="<?= $domain->url ?>">
                                                <?= $domain->name ?>
                                            </a>
                                            <a href="<?= $domain->externalUrl ?>">
                                                <em class="glyphicon glyphicon-new-window"></em>
                                            </a>
                                        </td>
                                        <td>
                                            <?= $domain->code ?>
                                        </td>
                                        <td>
                                            <?= implode(', ', $domain->tags) ?>
                                        </td>
                                        <td>
                                            <?= $domain->date ?>
                                        </td>
                                    </tr>
                                <? endforeach ?>
                            </tbody>
                        </table>
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
                    <? endif; ?>
                </div>
            </div>
        </section>
        <script src="//code.jquery.com/jquery-2.1.4.min.js"></script>
        <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
        <script src="//cdnjs.cloudflare.com/ajax/libs/bootstrap-table/1.8.1/bootstrap-table.js"></script>
    </body>
</html>
