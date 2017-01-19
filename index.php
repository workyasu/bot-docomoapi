<?php
require_once __DIR__ . '/vendor/autoload.php';

Predis\Autoloader::register();
$redis = new Predis\Client(getenv('REDIS_URL'));

$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient(getenv('CHANNEL_ACCESS_TOKEN'));
$bot = new \LINE\LINEBot($httpClient, ['channelSecret' => getenv('CHANNEL_SECRET')]);

$signature = $_SERVER["HTTP_" . \LINE\LINEBot\Constant\HTTPHeader::LINE_SIGNATURE];
try {
  $events = $bot->parseEventRequest(file_get_contents('php://input'), $signature);
} catch(\LINE\LINEBot\Exception\InvalidSignatureException $e) {
  error_log("parseEventRequest failed. InvalidSignatureException => ".var_export($e, true));
} catch(\LINE\LINEBot\Exception\UnknownEventTypeException $e) {
  error_log("parseEventRequest failed. UnknownEventTypeException => ".var_export($e, true));
} catch(\LINE\LINEBot\Exception\UnknownMessageTypeException $e) {
  error_log("parseEventRequest failed. UnknownMessageTypeException => ".var_export($e, true));
} catch(\LINE\LINEBot\Exception\InvalidEventRequestException $e) {
  error_log("parseEventRequest failed. InvalidEventRequestException => ".var_export($e, true));
}

foreach ($events as $event) {
  //エラー処理
  if (!($event instanceof \LINE\LINEBot\Event\MessageEvent)) {
    error_log('Non message event has come');
    continue;
  }
  if (!($event instanceof \LINE\LINEBot\Event\MessageEvent\TextMessage)) {
    error_log('Non text message has come');
    continue;
  }

  //メッセージを送る処理
  function replyTextMessage($bot, $replyToken, $text) {
    $response = $bot->replyMessage($replyToken, new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($text));
    if (!$response->isSucceeded()) {
      error_log('Failed!'. $response->getHTTPStatus . ' ' . $response->getRawBody());
    }
  }

  //Docomoの雑談API処理
  function chat($send_message) {
      // docomo chatAPI
      $api_key = '6a712e6d62456d70774778614e35794933566141482e54644c305530657969434f70515963394d6c375933';
      $api_url = sprintf('https://api.apigw.smt.docomo.ne.jp/dialogue/v1/dialogue?APIKEY=%s', $api_key);
      $req_body = array('utt' => $text);
      $req_body['context'] = $send_message;

      $headers = array(
          'Content-Type: application/json; charset=UTF-8',
      );
      $options = array(
          'http'=>array(
              'method'  => 'POST',
              'header'  => implode("\r\n", $headers),
              'content' => json_encode($req_body),
              )
          );
      $stream = stream_context_create($options);
      $res = json_decode(file_get_contents($api_url, false, $stream));

      return $res->utt;
  }

  function chat2($message, $context) {
    $api_key = '6a712e6d62456d70774778614e35794933566141482e54644c305530657969434f70515963394d6c375933';
    $api_url = sprintf('https://api.apigw.smt.docomo.ne.jp/dialogue/v1/dialogue?APIKEY=%s', $api_key);
    $req_body = array(
        'utt' => $message,
        'context' => $context,
    );
    $req_body['context'] = $message;

    $headers = array(
        'Content-Type: application/json; charset=UTF-8',
    );
    $options = array(
        'http'=>array(
            'method'  => 'POST',
            'header'  => implode("\r\n", $headers),
            'content' => json_encode($req_body),
            )
        );
    $stream = stream_context_create($options);
    $res = json_decode(file_get_contents($api_url, false, $stream));

    return $res->utt;
  }

  // get context from Redis
  $context = $redis->get('context');
  $message = $event->getText();
  $response = chat2($message, $context);

  $redis->set('context', $context);
  $redis->expire('context',100);
  $context = $redis->get('context');
  error_log("-------- message start --------");
  error_log($context);
  error_log("-------- message end --------");

  replyTextMessage($bot, $event->getReplyToken(), $response);
}
 ?>
