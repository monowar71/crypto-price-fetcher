# Cryptocurrency Price Fetcher

PHP-скрипт для получения курсов криптовалют с Binance и Bybit, а также курса USD/RUB с Coinbase. Ответ можно получить в формате JSON, XML или CSV.
Может использоваться в приложениях, Google Таблицах

## Параметры запроса:
- **symbols** (обязательный) — список тикеров (например, `BTCUSDT,ETHUSDT`).
- **format** (необязательный) — формат ответа: `json`, `xml`, `csv` (по умолчанию `json`).
- **source** (необязательный) — источник: `binance`, `bybit` (по умолчанию `binance`).

## Общий формат ответа:

- symbol: Имя тикера (например, BTCUSDT, ETHUSDT).
- price: Текущая цена криптовалюты.
- price_change_24h_percent: Процентное изменение цены за последние 24 часа (может быть null для некоторых источников).

## Пример запроса:
```sh
your-domain/index.php?symbols=BTCUSDT,ETHUSDT&format=json&source=binance
```

## Пример ответа в формате JSON
```json
[
    {
        "symbol": "BTCUSDT",
        "price": "45000.00",
        "price_change_24h_percent": "2.3"
    },
    {
        "symbol": "ETHUSDT",
        "price": "3000.00",
        "price_change_24h_percent": "1.2"
    }
]
```

## Пример ответа в формате XML
```XML
<root>
  <ticker>
    <symbol>BTCUSDT</symbol>
    <price>45000.00</price>
    <price_change_24h_percent>2.3</price_change_24h_percent>
  </ticker>
  <ticker>
    <symbol>ETHUSDT</symbol>
    <price>3000.00</price>
    <price_change_24h_percent>1.2</price_change_24h_percent>
  </ticker>
</root>
```
## Пример ответа в формате CSV
```csv
symbol,price,price_change_24h_percent
BTCUSDT,45000.00,2.3
ETHUSDT,3000.00,1.2
```
