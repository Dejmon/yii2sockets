<?php
/**
 * @var $nodeSockets \dejmon\yii2sockets\YiiNodeSocket
 * @var $redis yii\redis\Connection
 */
?>
{
    "scheme": "<?= $nodeSockets->nodeJsScheme ?>",
    "port": <?= $nodeSockets->nodeJsPort ?>,
    "hostname": "<?= $nodeSockets->nodeJsHost ?>",
    "cookieName": "<?= $nodeSockets->sessionVarName ?>",
    "sessionKeyPrefix": "<?= $nodeSockets->sessionKeyPrefix ?>",
    "redis": {
        "hostname": "<?= $redis->hostname ?>",
        "port": <?= $redis->port ?>,
        "db": <?= $redis->database ?>
    },
    "serviceKey": "<?= $nodeSockets->serviceKey ?>",
    "debug": true,
    "sslKeyPath": "<?= $nodeSockets->sslKeyPath ?>",
    "sslCaPath": "<?= $nodeSockets->sslCaPath ?>",
    "sslCertPath": "<?= $nodeSockets->sslCertPath ?>"
}