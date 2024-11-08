<?php

class ms_CDEK2
{
    /** @var modX $modx */
    public $modx;


    /**
     * @param modX $modx
     * @param array $config
     */
    function __construct(modX &$modx, array $config = [])
    {
        $this->modx =& $modx;
        $corePath = MODX_CORE_PATH . 'components/ms_cdek2/';
        $assetsUrl = MODX_ASSETS_URL . 'components/ms_cdek2/';

        $this->config = array_merge([
            'corePath' => $corePath,
            'modelPath' => $corePath . 'model/',
            'processorsPath' => $corePath . 'processors/',

            'connectorUrl' => $assetsUrl . 'connector.php',
            'assetsUrl' => $assetsUrl,
            'cssUrl' => $assetsUrl . 'css/',
            'jsUrl' => $assetsUrl . 'js/',

            'login' => $this->modx->getOption('ms_cdek2_login', null, 'EMscd6r9JnFiQ3bLoyjJY6eM78JrJceI', true),
            'password' => $this->modx->getOption('ms_cdek2_password', null, 'PjLZkKBHEiLK3YsjtNrt3TGNG0ahs3kG', true),
            'api_url' => $this->modx->getOption('ms_cdek2_login') ? 'https://api.cdek.ru/v2/' : 'https://api.edu.cdek.ru/v2/',
            'tariffs' => $this->modx->getOption('ms_cdek2_tariffs'),

            'sender_index' => $this->modx->getOption('ms_cdek2_sender_index', null, '127006', true),

            'size_multiplier' => $this->modx->getOption('ms_cdek2_size_multiplier', null, 1, true),
            'weight_multiplier' => $this->modx->getOption('ms_cdek2_weight_multiplier', null, 1000, true),
            
            'debug' => $this->modx->getOption('ms_cdek2_debug', null, false, true),
        ], $config);

        $this->modx->addPackage('ms_cdek2', $this->config['modelPath']);
        $this->modx->lexicon->load('ms_cdek2:default');
        $this->miniShop2 = $this->modx->getService('miniShop2');
    }
    
    function calc()
    {
        $price = 0;
        $this->miniShop2->loadServices($this->miniShop2->config['ctx']);
        $order = $this->miniShop2->order->get();
        
        /** @var msDelivery $delivery */
        if ($delivery = $this->modx->getObject('msDelivery', ['id' => $order['delivery'], 'active' => 1])) {
            $price = $this->miniShop2->order->getCost(false, true);
            /*
            $this->modx->log(1, '$delivery_cost ' . $delivery_cost);
            if ($delivery_cost) {
                $status = $this->miniShop2->cart->status();
                $this->modx->log(1, 'status ' . print_r($status, true));
                $price = $delivery_cost - $status['total_cost'];
                if ($price < 0) {
                    $price = 0;
                }
            } else {
                $this->modx->log(1, 'no delivery cost');
            }
            */
        }
        
        return $price;
    }
    
    function makeRequest($path = '', array $data = [])
    {
        $log = [];

        $post_paths = [
            'calculator/tarifflist',
            'calculator/tariff'
        ];
        $api_url = $this->config['api_url'];
        if ($path == 'location/cities') {
            $api_url = 'https://api.cdek.ru/v2/';
        }
        if ($path) {
            $log[] = '[ms_CDEK2] =====================';
            $log[] = 'Make request:';
            $client = $this->modx->getService('rest', 'rest.modRest');
            $client->setOption('timeout', 2);
            $headers = ['Content-Type' => 'application/json', 'Authorization' => 'Bearer ' . $this->getToken()];
            $log[] = 'Headers: ' . print_r($headers, true);
            $log[] = 'Data: ' . print_r($data, true);
            if (in_array($path, $post_paths)) {
                $client->setOption('format', 'json');
                $client->setOption('suppressSuffix', 'true');
                $log[] = 'POST: ' . $api_url . $path;
                $response = $client->post($api_url . $path, $data, $headers)->process();
            } else {
                $log[] = 'GET: ' . $api_url . $path . '?' . http_build_query($data);
                $response = $client->get($api_url . $path . '?' . http_build_query($data), [], $headers)->process();
            }
            foreach ($response as $key => $item) {
                if (!empty($item['postal_codes'])) {
                    $response[$key]['postal_codes'] = [ array_shift($item['postal_codes']) ];
                }
            }
            $log[] = 'Response: ' . print_r($response, true);
            
            if ($this->config['debug'] && !empty($log)) {
                $this->modx->log(MODX::LOG_LEVEL_ERROR, implode(PHP_EOL, $log));
            }
            if (isset($response['errors'])) {
                $this->modx->log(
                    MODX::LOG_LEVEL_ERROR,
                    '[ms_CDEK2] Request error:' . PHP_EOL .
                    $api_url . $path . PHP_EOL .
                    print_r($data, true) . PHP_EOL .
                    print_r($response, true)
                );
            }
            return $response;
        }
    }
    
    function getToken()
    {
        $log = [];

        if (empty($this->config['token'])) {
            $this->config['token'] = $this->modx->cacheManager->get('ms_cdek2/token');
            if (!$this->config['token']) {
                $data = [
                    'grant_type' => 'client_credentials',
                    'client_id' => $this->config['login'],
                    'client_secret' => $this->config['password'],
                ];
                $path = 'oauth/token';
                $log[] = 'Data: ' . print_r($data, true);
                $log[] = 'POST: ' . $this->config['api_url'] . $path;
                $client = $this->modx->getService('rest', 'rest.modRest');
                $client->setOption('timeout', 2);
                $response = $client->post($this->config['api_url'] . $path, $data);
                $data = $response->process();
                $log[] = 'Response: ' . print_r($data, true);
                
                if ($this->config['debug'] && !empty($log)) {
                    $this->modx->log(MODX::LOG_LEVEL_ERROR, implode(PHP_EOL, $log));
                }
                if (!empty($data['access_token'])) {
                    $this->config['token'] = $data['access_token'];
                    $expires_in = 3600;
                    if ($data['expires_in']) {
                        $expires_in = $data['expires_in'];
                    }
                    $this->modx->cacheManager->set('ms_cdek2/token', $this->config['token'], $expires_in);
                } else {
                    $this->modx->log(MODX::LOG_LEVEL_ERROR, '[ms_CDEK2] Ccould not get API token. Please check API login and password');
                }
            }
        }
        return $this->config['token'];
    }
    
    function getLocation($query)
    {
        $code = 0;
        $response = $this->makeRequest('location/cities', $query);
        foreach ($response as $item) {
            if (!$code || $item['code'] < $code) {
                $code = $item['code'];
            }
        }
        if (!$code) {
            $this->modx->log(
                MODX::LOG_LEVEL_ERROR,
                '[ms_CDEK2] getLocation error:' . PHP_EOL .
                $this->config['api_url'] . 'location/cities' . PHP_EOL .
                print_r($query, true) . PHP_EOL .
                print_r($response, true)
            );
            $code = 44;
        }
        return ['code' => $code];
    }
    
    function getSenderCity()
    {
        $response = $this->makeRequest('location/cities', [
            'postal_code' => $this->config['sender_index']
        ]);
        $response = array_shift($response);
        return $response['city'];
    }

    function getDeliveryCity()
    {
        $response = [];
        if (!empty($_SESSION['minishop2']['order']['city'])) {
            $response = $this->makeRequest('location/cities', [
                'city' => $_SESSION['minishop2']['order']['city']
            ]);
            $response = array_shift($response);
        }
        if (empty($response['city'])) {
            $index = $this->config['sender_index'];
            if (!empty($_SESSION['minishop2']['order']['index'])) {
                $index = $_SESSION['minishop2']['order']['index'];
            }
            $response = $this->makeRequest('location/cities', [
                'postal_code' => $index
            ]);
            $response = array_shift($response);
        }
        return $response;
    }

    function getPointIndex()
    {
        $data = $this->makeRequest('deliverypoints', [
            'city_code' => $_POST['city_code']
        ]);

        foreach($data as $point) {
            if ($point['code'] == $_POST['point']) {
                $this->modx->log(1, print_r($point, 1));
                if (!empty($point['location']['postal_code'])) {
                    return $point['location']['postal_code'];
                }
            }
        }

        return $this->config['sender_index'];
    }

    function getPointAddress()
    {
        $data = $this->makeRequest('deliverypoints', [
            'city_code' => $_POST['city_code']
        ]);

        foreach($data as $point) {
            if ($point['code'] == $_POST['point']) {
                if (!empty($point['location']['postal_code'])) {
                    return [
                        'city' => $point['location']['city'],
                        'city_code' => $point['location']['city_code'],
                        'region' => $point['location']['region'],
                        'index' => $point['location']['postal_code'],
                    ];
                }
            }
        }

        return [];
    }

}
