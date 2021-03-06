<?php


declare(strict_types=1);

/** @var \KED\Services\Event\EventDispatcher $eventDispatcher */
/** @var \KED\Services\Di\Container $container */

use Symfony\Component\Filesystem\Filesystem;

$eventDispatcher->addListener(
        "admin_menu",
        function (array $items) {
            return array_merge($items, [
                [
                    "id" => "setting",
                    "sort_order" => 60,
                    "url" => null,
                    "title" => "Setting",
                    "parent_id" => null
                ],
                [
                    "id" => "setting_general",
                    "sort_order" => 10,
                    "url" => \KED\generate_url("setting.general"),
                    "title" => "General",
                    "icon" => "cogs",
                    "parent_id" => "setting"
                ],
                [
                    "id" => "setting_catalog",
                    "sort_order" => 20,
                    "url" => \KED\generate_url("setting.catalog"),
                    "title" => "Catalog",
                    "icon" => "sliders-h",
                    "parent_id" => "setting"
                ],
                [
                    "id" => "setting_payment",
                    "sort_order" => 30,
                    "url" => \KED\generate_url("setting.payment"),
                    "title" => "Payment",
                    "icon" => "money-check-alt",
                    "parent_id" => "setting"
                ],
                [
                    "id" => "setting_shipment",
                    "sort_order" => 40,
                    "url" => \KED\generate_url("setting.shipment"),
                    "title" => "Shipment",
                    "icon" => "shipping-fast",
                    "parent_id" => "setting"
                ]
            ]);
        },
        0
);

function createConfigCache(\KED\Services\Di\Container $container) {
    $promise = new GuzzleHttp\Promise\Promise(function () use (&$promise){
        try {
            $cacheTemplate = <<< 'EOT'
<?php
/**
 * Copyright ?? Nguyen Huu The <the.nguyen@KED.com>.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);
/**
 * This configuration cache file was generated at %s
 */
return %s;
EOT;

            $settingTable = \KED\_mysql()->getTable('setting');
            while ($row = $settingTable->fetch()) {
                if ($row['json'] == 1)
                    $configuration[$row['name']] = json_decode($row['value'], true);
                else
                    $configuration[$row['name']] = $row['value'];
            }
            $file_system = new Filesystem();
            $cacheContent = sprintf(
                $cacheTemplate,
                date('c'),
                var_export($configuration, true)
            );
            $file_system->dumpFile(CACHE_PATH . DS . 'config_cache.php', $cacheContent);
            $promise->resolve(true);
        } catch (Exception $e) {
            $promise->reject($e);
        }
    });
    $container->get(\KED\Services\PromiseWaiter::class)->addPromise('saveSettingToCache', $promise);
}

$eventDispatcher->addListener('after_insert_setting', function () {
    createConfigCache(\KED\the_container());
});
$eventDispatcher->addListener('after_update_setting',  function () {
    createConfigCache(\KED\the_container());
});
$eventDispatcher->addListener('after_insert_on_update_setting',  function () {
    createConfigCache(\KED\the_container());
});