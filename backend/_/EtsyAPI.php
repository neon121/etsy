<?php
class EtsyAPI {
    const API_KEY = 'API_KEY_MUST_BE_HERE';
    const TEST_MODE = true;
    const URL_BASE = 'https://openapi.etsy.com/v2/';
    //const URL_BASE = 'https://myvds.ml/etsy/';
    
    /**
     * @param $action
     * @param array $array
     * @param string $method
     * @return mixed
     * @throws BadRequest_API_Exception
     * @throws Forbidden_API_Exception
     * @throws NotFound_API_Exception
     * @throws ServerError_API_Exception
     * @throws ServerUnavailable_API_Exception
     * @throws UnknownError_API_Exception
     */
    private static function _do($action, $array = [], $method = 'GET') {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_URL => self::URL_BASE . $action . '?' .
                implode('&', array_map(function($k, $v) {return "$k=$v";}, array_keys($array), $array))
        ]);
        $array['method'] = 'GET';
        $array['api_key'] = self::API_KEY;
        $url = self::URL_BASE . $action . '?' .
            implode('&', array_map(function($k, $v) {return "$k=$v";}, array_keys($array), $array));
        curl_setopt($ch, CURLOPT_URL, $url);
        $result = curl_exec($ch);
        $position = strpos($result, "\r\n\r\n");
        //$header = substr($result, 0, $position);
        $body = substr($result, $position + 4);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($code === 200 OR ($code === 201 && $method === 'POST')) return json_decode($body);
        else switch ($code) {
            case 400: throw new BadRequest_API_Exception($body, $url);
            case 403: throw new Forbidden_API_Exception($body, $url);
            case 404: throw new NotFound_API_Exception($body, $url);
            case 500: throw new ServerError_API_Exception($body, $url);
            case 503: throw new ServerUnavailable_API_Exception($body, $url);
            default:
                throw new UnknownError_API_Exception($code, $body, $url);
        }
    }
    
    /**
     * @return mixed
     * @throws BadRequest_API_Exception
     * @throws Forbidden_API_Exception
     * @throws NotFound_API_Exception
     * @throws ServerError_API_Exception
     * @throws ServerUnavailable_API_Exception
     * @throws UnknownError_API_Exception
     */
    public static function test() {
        return self::_do("", ['somename' => 'someval'], 'POST');
    }
}

abstract class API_Exception extends Exception {
    public $httpCode, $txt, $response, $url;
    
    public function __construct(
        string $response, string $url,
        string $message = "", int $code = 0, Throwable $previous = null
    ) {
        $this->response = $response;
        $this->url = $url;
        $message = $this->txt;
        parent::__construct($message, $code, $previous);
    }
    
    public function arr() {
        return ['API_Exception' => [
            'httpCode' => $this->code,
            'txt' =>      $this->txt,
            'response' => $this->response,
            'url' =>      $this->url
        ]];
    }
}
class BadRequest_API_Exception        extends API_Exception {public $httpCode = 400, $txt = "Bad Request";}
class Forbidden_API_Exception         extends API_Exception {public $httpCode = 403, $txt = "Forbidden";}
class NotFound_API_Exception          extends API_Exception {public $httpCode = 404, $txt = "Not Found";}
class ServerError_API_Exception       extends API_Exception {public $httpCode = 500, $txt = "Server Error";}
class ServerUnavailable_API_Exception extends API_Exception {public $httpCode = 503, $txt = "Service Unavailable";}
class UnknownError_API_Exception extends API_Exception {
    public function __construct(
        int $httpCode, string $response, string $url,
        string $message = "", int $code = 0, Throwable $previous = null)
    {
        $this->httpCode = $httpCode;
        $this->txt = "Неизвестная ошибка";
        parent::__construct($response, $url, $message, $code, $previous);
    }
}