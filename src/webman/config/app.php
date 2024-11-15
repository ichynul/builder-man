<?php

return [
    'enable' => true,
    //builder 配置
    //see vendor/ichynul/tpextbuilder/src/config.php
    'tpext_builder_common_module' => [
        'search_open' => 1,
        'layer_size' => '1000px,auto',
        'max_size' => 20,
        'is_rand_name' => 1,
        'file_by_date' => 5,
        'storage_driver' => \tpext\builder\logic\LocalStorage::class,
        'image_water' => '',
        'image_water_position' => 'bottom-right',
        'image_size_limit' => '1024,1024',
        'allow_suffix' =>
        //
        "jpg,jpeg,gif,wbmp,webpg,png,ico," .
            //
            "flv,swf,mkv,avi,rm,rmvb,mpeg,mpg,ogv,mov,wmv,mp4,webm," .
            //
            "ogg,mp3,wav,mid," .
            //
            "rar,zip,tar,gz,7z,bz2,cab,iso," .
            //
            "doc,docx,xls,xlsx,ppt,pptx,pdf,txt,md",
        //
        // '__hr__' => '地图api，按需配置',
        'table_empty_text' => '<div class="text-center"><img src="/assets/tpextbuilder/images/empty.png" /><p>暂无相关数据~</p></div>',
        'export_only_choosed_columns' => 1,
        'amap_js_key' => '//webapi.amap.com/maps?v=1.4.15&key=您申请的key&jscode=你的jscode',
        'baidu_map_js_key' => '//api.map.baidu.com/api?v=3.0&ak=您的密钥',
        'google_map_js_key' => '//maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&libraries=places&key=您申请的key值',
        'tcent_map_js_key' => '//map.qq.com/api/js?v=2.exp&libraries=place&key=您申请的key值',
        'yandex_map_js_key' => '//api-maps.yandex.ru/2.1/?lang=ru_RU',
    ]
];
