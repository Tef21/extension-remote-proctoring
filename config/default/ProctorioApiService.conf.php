<?php

use oat\remoteProctoring\model\ProctorioApiService;

return new ProctorioApiService([
    ProctorioApiService::OPTION_PERSISTENCE => 'default_kv',
    ProctorioApiService::OPTION_OAUTH_KEY => '',
    ProctorioApiService::OPTION_OAUTH_SECRET => '',
]);