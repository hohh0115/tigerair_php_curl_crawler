
<style>
	table, td {
	    border: 2px solid black;
	    border-collapse: collapse;
	}

	td {
	    padding: 12px;
	    text-align: left;
	}
</style>

<!-- https://booking.tigerairtw.com/?dlType=fltsrch&culture=zh-TW&ms=RoundTrip&psgr=2_0_0&depDate=2018-06-01&retDate=2018-06-08&origin=TPE&dest=KIX&pc=

https://tiger-wkgk.matchbyte.net/wkapi/v1.0/flightsearch?adults=2&children=0&infants=0&originStation=TPE&originStation=KIX&destinationStation=KIX&destinationStation=TPE&departureDate=2018-06-01&departureDate=2018-06-08&includeoverbooking=false&daysBeforeAndAfter=4&locale=zh-TW -->

<?php

// 人數都先設為兩人
// 都是來回機票
// 出發地皆為 台北

// dest_code:
// AKJ  旭川
// FUK  福岡
// HKD  函館
// HNA  花卷
// IBR  茨城
// KMQ  小松
// NGO  名古屋
// OKJ  岡山
// OKA  沖繩
// SDJ  仙台
// HND  東京羽田
// XX1  Tokyo - All Airports
// KIX  大阪
// NRT  東京成田

set_time_limit(0); // depends on your need
header('Content-Type: text/html; charset=utf-8');

// setting
$dest_code = 'KIX';
$journey_begin_date = '2018-05-19';  // 出發日期
$journey_duration_day = 5;   // 旅遊天數(最後一天為回程日)
$last_journey_begin_date = '2018-05-20';  // 最後的出發日期
$below_price = 3000;
$duration_date_array = array();
$label_array = ['出發', '返程'];
$dest_array = array(
	'AKJ' => '旭川',
	'FUK' => '福岡',
	'HKD' => '函館',
	'HNA' => '花卷',
	'IBR' => '茨城',
	'KMQ' => '小松',
	'NGO' => '名古屋',
	'OKJ' => '岡山',
	'OKA' => '沖繩',
	'SDJ' => '仙台',
	'HND' => '東京羽田',
	'XX1' => 'Tokyo - All Airports',
	'KIX' => '大阪',
	'NRT' => '東京成田'
);

echo '<h1>目的地：'.$dest_array[$dest_code].'</hr>';

// let's begin
$date   = new DateTime($last_journey_begin_date);
$day    = new DateInterval('P1D');
$date->add($day);
$tmp_last_journey_begin_date = $date->format('Y-m-d');


$begin_date_array = new DatePeriod(
     new DateTime($journey_begin_date),
     new DateInterval('P1D'),
     new DateTime($tmp_last_journey_begin_date)
);

foreach ($begin_date_array as $key => $value) {
    $duration_date_array[$key]['begin'] = $value->format('Y-m-d');

	$date   = new DateTime($value->format('Y-m-d'));
	$day    = new DateInterval('P'.($journey_duration_day-1).'D'); // P開頭代表日期，10D 代表 10 天
	$date->add($day);

    $duration_date_array[$key]['end'] = $date->format('Y-m-d');
}

foreach ($duration_date_array as $key => $value) {

	$url = 'https://tiger-wkgk.matchbyte.net/wkapi/v1.0/flightsearch?adults=2&children=0&infants=0&originStation=TPE&originStation='.$dest_code.'&destinationStation='.$dest_code.'&destinationStation=TPE&departureDate='.$value['begin'].'&departureDate='.$value['end'].'&includeoverbooking=false&daysBeforeAndAfter=4&locale=zh-TW';
	$isHttps = 'Y';

	$ch  = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	if ( $isHttps == 'Y' ) {
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	}
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,0);
	curl_setopt($ch, CURLOPT_TIMEOUT, 5); // 逾時設置

	$result = json_decode(curl_exec($ch),TRUE); // 將回傳的資料 轉換成 JSON
	// $json_string = json_encode($result, JSON_PRETTY_PRINT);

	curl_close($ch);

	$search_url = '<a href="https://booking.tigerairtw.com/?dlType=fltsrch&culture=zh-TW&ms=RoundTrip&psgr=2_0_0&depDate='.$value['begin'].'&retDate='.$value['end'].'&origin=TPE&dest='.$dest_code.'&pc=" target="_blank">';
	$search_url .= 'https://booking.tigerairtw.com/?dlType=fltsrch&culture=zh-TW&ms=RoundTrip&psgr=2_0_0&depDate='.$value['begin'].'&retDate='.$value['end'].'&origin=TPE&dest='.$dest_code.'&pc=';
	$search_url .= '</a>';
	
	$stat = '出發日期：'.$value['begin'].'<br>返程日期：'.$value['end'];

	echo '<h2>'.$stat.'</h2>';

	if (empty($result['journeyDateMarkets'])) {
		echo '<h3 style="color: red">No Record Found! Use the URL!</h3>';
		echo '<p>URL: '.$search_url.'</p>';
	} else {
		foreach ($result['journeyDateMarkets'] as $key1 => $value1) {

			$html = '';
			$html .= '<h3>'.($key1+1).'. '.$label_array[$key1].'</h3>';


			$html .= '<table width="100%">';
			$html .= '<tr>';
			$html .= '<td colspan="'.count($value1['journeys']).'">';
			$html .= '<p>URL: '.$search_url.'</p>';
			$html .= '<p>Depart Station: '.$value1['departStation']['displayName'].'</p>';
			$html .= '<p>Arrival Station: '.$value1['arrivalStation']['displayName'].'</p>';
			$html .= '</td>';
			$html .= '</tr>';
			$html .= '<tr>';
			foreach ($value1['journeys'] as $key2 => $value2) {

				$html .= '<td>';
				$html .= '<p>Depart Time: '.$value2['departDateTime'].'</p>';
				$html .= '<p>Arrive Time: '.$value2['arriveDateTime'].'</p>';

				foreach ($value2['fares'] as $key3 => $value3) {

					if ((int)$value3['price'] <= $below_price) {
						$alert = '<span style="color:red;font-weight:bold">(低於3000的價格!)</span>';
					} else {
						$alert = '';
					}

					$html .= '<p>Class: '.$value3['classOfService'].' / <span style="color:blue;">Price: '.$value3['currencyCode'].' '.number_format($value3['price']).'  '.$alert.'</span></p>';
				}

				$html .= '</td>';
			}
			$html .= '</tr>';

			$html .= '</table>';
			echo $html;
		}
	}

	echo '<br><br><hr><br>';
}


?>
