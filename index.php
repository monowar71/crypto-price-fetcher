<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$symbols = isset($_GET['symbols']) ? explode(',', $_GET['symbols']) : [];
$format = isset($_GET['format']) ? strtolower($_GET['format']) : 'json';
$source = isset($_GET['source']) ? strtolower($_GET['source']) : 'binance';

if (empty($symbols)) {
    sendResponse(["error" => "No symbols specified"], $format);
}

$fetchUsdRub = in_array('USDTRUB', $symbols);
$results = array_fill_keys($symbols, null);

if ($fetchUsdRub) {
    $results['USDTRUB'] = fetchUsdRub();
}
$marketData = fetchMarketData($source, $symbols);
$results = array_merge($results, $marketData);

$results = array_values(array_filter($results));

sendResponse($results, $format);

function fetchMarketData($source, $symbols) {
    $apiUrls = [
        'binance' => "https://api.binance.com/api/v3/ticker/24hr",
        'bybit'   => "https://api.bybit.com/v5/market/tickers?category=spot"
    ];

    if (!isset($apiUrls[$source])) {
        return [];
    }

    $data = fetchData($apiUrls[$source]);
    if (!$data) {
        return [];
    }

    $results = [];

    if ($source === 'binance') {
        foreach ($data as $ticker) {
            if (in_array($ticker['symbol'], $symbols) && $ticker['symbol'] !== 'USDTRUB') {
                $results[$ticker['symbol']] = [
                    'symbol'               => $ticker['symbol'],
                    'price'                => $ticker['lastPrice'],
                    'price_change_24h_percent' => $ticker['priceChangePercent']
                ];
            }
        }
    } elseif ($source === 'bybit' && isset($data['result']['list'])) {
        foreach ($data['result']['list'] as $ticker) {
            if (in_array($ticker['symbol'], $symbols) && $ticker['symbol'] !== 'USDTRUB') {
                $results[$ticker['symbol']] = [
                    'symbol'               => $ticker['symbol'],
                    'price'                => $ticker['lastPrice'],
                    'price_change_24h_percent' => ($ticker['price24hPcnt']) * 100
                ];
            }
        }
    }

    foreach ($symbols as $symbol) {
        if (!isset($results[$symbol]) && $symbol !== 'USDTRUB') {
            $results[$symbol] = [
                'symbol'               => $symbol,
                'price'                => null,
                'price_change_24h_percent' => null
            ];
        }
    }

    return $results;
}

function fetchUsdRub() {
    $url = "https://api.coinbase.com/v2/exchange-rates?currency=USDT";
    $data = fetchData($url);

    return isset($data['data']['rates']['RUB']) ? [
        'symbol' => 'USDTRUB',
        'price' => $data['data']['rates']['RUB'],
        'price_change_24h_percent' => null
    ] : null;
}

function fetchData($url) {
    $ch = curl_init();

    curl_setopt_array($ch, [
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_HEADER         => false
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    return $response ? json_decode($response, true) : null;
}

function sendResponse($data, $format) {
    switch ($format) {
        case 'xml':
            header('Content-Type: application/xml; charset=utf-8');
            echo arrayToXml($data);
            break;
        case 'csv':
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="data.csv"');
            echo arrayToCsv($data);
            break;
        case 'json':
        default:
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($data, JSON_PRETTY_PRINT);
            break;
    }
    exit;
}

function arrayToXml($data) {
    $xml = new SimpleXMLElement('<root/>');
    foreach ($data as $item) {
        $entry = $xml->addChild('ticker');
        foreach ($item as $key => $value) {
            $entry->addChild($key, htmlspecialchars($value));
        }
    }
    return $xml->asXML();
}

function arrayToCsv($data) {
    if (empty($data)) return '';

    $output = fopen('php://output', 'w');
    fputcsv($output, array_keys($data[0])); 
    foreach ($data as $row) {
        fputcsv($output, $row);
    }
    fclose($output);
}
?>
