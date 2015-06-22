<?php
error_reporting(E_ALL);

$suffix = "\.dev";
$dir = "/vagrant/domains/";

/**
 * read directories
 */
$dh = opendir($dir);
$filenames = [];
while (false !== ($filename = readdir($dh))) {

    if (preg_match("'" . $suffix . "$'", $filename)) {

        $data = [
            'code' => null,
            'name' => null,
            'url' => null,
            'devUrl' => null,
            'stageUrl' => null,
            'liveUrl' => null,
            'repoUrl' => null,
            'databaseUrl' => null,
            'crmUrl' => null,
            'redmineUrl' => null,
            'date' => null,
            'tags' => [],
        ];

        # domain and paths
        $localDomain = $filename;
        $externalDomain = preg_replace("'" . $suffix . "'", '', $filename);
        $symlinkPath = $dir . $filename;
        $realPath = realpath($dir . readlink($symlinkPath));
        $baseurl = '/' . (file_exists($symlinkPath . '/web/app_dev.php') ? 'app_dev.php' : '');

        # last modification time from repo
        $mtime = file_exists($symlinkPath . '/.git/') ? filemtime($symlinkPath . '/.git/') : filemtime($symlinkPath);

        # meta data
        $data['name'] = $externalDomain;
        $data['code'] = basename(dirname($realPath));
        $data['date'] = date('Y-m-d H:i:s', $mtime);

        # local and live URLs
        $data['url'] = 'http://' . $localDomain . $baseurl;
        if(preg_match("'\.'", $externalDomain)){
            $data['liveUrl'] = 'http://' . $externalDomain;
        }

        # repo URL
        if (file_exists($symlinkPath . '/.git/config')) {
            $gitConfig = parse_ini_file($symlinkPath . '/.git/config');
            if (!empty($gitConfig['url'])) {
                $data['repoUrl'] = preg_replace("'^git@(.*):(.*)\.git$'", "https://$1/$2", $gitConfig['url']);
            }
        }

        # data from description file
        $descriptionFile = null;
        if (empty($descriptionFile) && file_exists(dirname($realPath) . '/DESCRIPTION-'.$externalDomain)) {
            $descriptionFile = dirname($realPath) . '/DESCRIPTION-'.$externalDomain;
        }
        if (empty($descriptionFile) && file_exists(dirname($realPath) . '/DESCRIPTION')) {
            $descriptionFile = dirname($realPath) . '/DESCRIPTION';
        }

        if(!empty($descriptionFile)){
            $config = parse_ini_file($descriptionFile);
            if (!empty($config['devUrl'])) {
                $data['devUrl'] = $config['devUrl'];
            }
            if (!empty($config['stageUrl'])) {
                $data['stageUrl'] = $config['stageUrl'];
            }
            if (!empty($config['liveUrl'])) {
                $data['liveUrl'] = $config['liveUrl'];
            }
            if (!empty($config['repoUrl'])) {
                $data['repoUrl'] = $config['repoUrl'];
            }
            if (!empty($config['databaseUrl'])) {
                $data['databaseUrl'] = $config['databaseUrl'];
            }
            if (!empty($config['crmUrl'])) {
                $data['crmUrl'] = $config['crmUrl'];
            }
            if (!empty($config['redmineUrl'])) {
                $data['redmineUrl'] = $config['redmineUrl'];
            }
            if (!empty($config['tags'])) {
                $data['tags'] = explode(',', $config['tags']);
            }
        }

        $domains[] = (object)$data;

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
        <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">
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
                            <span class="fa fa-fw fa-cog"></span>
                        </a>
                    <? endif; ?>
                </div>
                <? if (!empty($domains)): ?>
                    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                        <ul class="nav navbar-nav navbar-right">
                            <li>
                                <a data-toggle="modal" href="#hostsConfig">
                                    <span class="fa fa-fw fa-cog"></span>
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
                            <em class="fa fa-fw fa-info-circle"></em>
                            No directories configured. Create one with suffix <code><?= stripslashes($suffix) ?></code> in <code><?= $dir ?></code> on virtual machine and add it to hosts file in local machine.
                        </div>
                    <? else: ?>
                        <table id="searchlist"
                               data-classes="table table-hover table-condensed table-striped"
                               data-toggle="table"
                               data-search="true"
                               data-pagination="true"
                               data-page-size="20"
                               data-sort-name="date"
                               data-sort-order="desc"
                            >
                            <thead>
                                <tr>
                                    <th data-field="name" data-sortable="true">
                                        <em class="fa fa-file-o"></em> Name
                                    </th>
                                    <th data-field="code" data-sortable="true">
                                        <em class="fa fa-fw fa-flash"></em> Code
                                    </th>
                                    <th>
                                        <em class="fa fa-fw fa-tags"></em> Tags
                                    </th>
                                    <th data-field="date" data-sortable="true">
                                        <em class="fa fa-fw fa-calendar"></em> Date
                                    </th>
                                    <th>
                                        <em class="fa fa-fw fa-link"></em> Links
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
                                        </td>
                                        <td>
                                            <?= $domain->code ?>
                                        </td>
                                        <td class="tags">
                                            <? if (!empty($domain->tags)): ?>
                                                <a><?= implode('</a>, <a>', $domain->tags) ?></a>
                                            <? endif; ?>
                                        </td>
                                        <td>
                                            <?= $domain->date ?>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a class="btn btn-primary btn-xs" href="<?= $domain->url ?>" title="Local development page">
                                                    <em class="fa fa-fw fa-code"></em>
                                                </a>
                                                <? if (!empty($domain->devUrl)): ?>
                                                    <a class="btn btn-warning btn-xs" href="<?= $domain->devUrl ?>" title="Development page">
                                                      <em class="fa fa-globe"> dev</em>
                                                    </a>
                                                <? endif; ?>
                                                <? if (!empty($domain->stageUrl)): ?>
                                                    <a class="btn btn-warning btn-xs" href="<?= $domain->stageUrl ?>" title="Stage page">
                                                        <em class="fa fa-globe"> stage</em>
                                                    </a>
                                                <? endif; ?>
                                                <? if (!empty($domain->liveUrl)): ?>
                                                    <a class="btn btn-success btn-xs" href="<?= $domain->liveUrl ?>" title="Live page">
                                                        <em class="fa fa-globe"> prod</em>
                                                    </a>
                                                <? endif; ?>
                                            </div>
                                             <div class="btn-group">
                                                <? if (!empty($domain->repoUrl)): ?>
                                                    <a class="btn btn-info btn-xs" href="<?= $domain->repoUrl ?>" title="GIT repository">
                                                        <em class="fa fa-code-fork"> GIT</em>
                                                    </a>
                                                <? endif; ?>
                                                <? if (!empty($domain->databaseUrl)): ?>
                                                    <a class="btn btn-info btn-xs" href="<?= $domain->databaseUrl ?>" title="Database manager">
                                                        <em class="fa fa-database"> SQL</em>
                                                    </a>
                                                <? endif; ?>
                                                <? if (!empty($domain->crmUrl)): ?>
                                                    <a class="btn btn-info btn-xs" href="<?= $domain->crmUrl ?>" title="CRM project">
                                                        <em class="fa fa-user-md"> CRM</em>
                                                    </a>
                                                <? endif; ?>
                                                <? if (!empty($domain->redmineUrl)): ?>
                                                    <a class="btn btn-info btn-xs" href="<?= $domain->redmineUrl ?>" title="Redmine project">
                                                        <em class="fa fa-tasks"> RM</em>
                                                    </a>
                                                <? endif; ?>
                                            </div>
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
