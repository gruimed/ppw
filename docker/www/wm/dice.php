<?php

use GuzzleHttp\Client;

class Dice {


    function __construct() {
    }

    public function roll($rolls) {
        $result = [];
        for ($i = 0; $i < $rolls; $i++) {
            $result[] = $this->rollOnce();
        }
        return $result;
    }

    private function rollOnce() {
        $client = new Client();
        $res = $client->request('GET', 'http://echo-server:8088/payload?io_msec=10');
//        usleep(10_000);
        $result = random_int(1, 6);
        return $result;
    }
}