<?php


class Api {
    public function __construct($caller, $ws) {
        $this->ws = $ws;
        $this->caller = $caller;
        $this->base = "https://faraday.mobilada.net/~yalazi/fetfrip/index.php";
    }
    private function getCookie() {
        try {
            return $this->ws->headers['cookie'];
        } catch (Exception $w) {
            return "";
        }
    }

    public function command($command) {
      $commands = explode('|', $command);
      return $this->__call($commands[0], $commands);
    }

    private function _command_last($commands) {
       $id = $commands[1];
       $cid = $commands[2];
       $cookie = $this->getCookie();
       $c = $this->getUrlContent(sprintf("%s?r=%s&model=%s&id=%d&cid=%d", $this->base, 'comment/comment/apicomment','Post', $id, $cid), $cookie);

       return json_encode(['command' => 'last', 'post_id' => $id, 'startafter' => $cid, 'status'=> 'success', 'data' => json_decode($c)]);
    }

    private function _command_count($commands) {
        $id = $commands[1];
        $cookie = $this->getCookie();
        $c = $this->getUrlContent(sprintf("%s?r=%s&model=%s&id=%d", $this->base, 'comment/comment/apicount','Post', $id), $cookie);
        return json_encode([ 'command' => 'count', 'post_id' => $id, 'status' => 'success', 'data' => $c]);
    }
    private function _default_command() {
        return json_encode([ 'status' => 'fail']);
    }

    public function __call($method, $args) {
        if (method_exists($this, "_command_" . $method)) {
            return $this->{"_command_" . $method}($args);
        } else {
            return $this->_default_command();
        }
    }
    
    private function getUrlContent($url, $cookie){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (X11; Linux x86_64; rv:31.0) Gecko/20100101 Firefox/31.0 Iceweasel/31.5.0");
        curl_setopt($ch, CURLOPT_REFERER, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_COOKIE, $cookie);
        $data = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return $data;
    }


}

?>
