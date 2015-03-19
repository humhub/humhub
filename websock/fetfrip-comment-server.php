<?php 

require_once(__DIR__ . '/websockets.php');
require_once(__DIR__ . '/fetfrip-api.php');


class commentServer extends WebSocketServer {
  protected $maxBufferSize = 104857600;
  private $clients = [];

  public function setClient($id, $key, $value) {
    if (isset($this->clients[$id])) {
        $this->clients[$id][$key] = $value;
    } else {
        $this->clients[$id] = [$key => $value];
    }
  }
  public function getClient($id, $key) {
    if (isset($this->clients[$id]) && isset($this->clients[$id][$key])) {
        return $this->clients[$id][$key];
    } else {
        return null;
    }
  }

  protected function process ($ws, $data) {
    $api = new Api($this, $ws);
    $commands = explode('#', $data);
    if (count($commands)) {
        foreach ($commands as $command) {
            if (strlen($command)) {
                $this->send($ws, $api->command($command));
            }
        }
    }
  }

  protected function connected ($ws) {
      $this->clients[$ws->id] = null;
  }

  protected function closed ($ws) {
      unset($this->clients[$ws->id]);
  }
}

