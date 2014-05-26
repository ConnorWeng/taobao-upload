<?php

class StoreSession {
    public $nick;
    public $accessToken;

    public function __construct($nick, $accessToken) {
        $this->nick = $nick;
        $this->accessToken = $accessToken;
    }

    public function addStoreSession() {
        $otherStoreSessions = $this->getAllStoreSessions();
        session('other_store_sessions', $otherStoreSessions.$this->nick.','.$this->accessToken.';');
    }

    public function deleteStoreSession() {
        $array = $this->makeStoreSessionsArray();
        $newOtherStoreSessions = '';
        foreach ($array as $str) {
            if (strpos($str, $this->nick) === false) {
                $newOtherStoreSessions .= $str.';';
            }
        }
        session('other_store_sessions', $newOtherStoreSessions);
    }

    public function getAllStoreSessions() {
        if (!session('?other_store_sessions')) {
            session('other_store_sessions', '');
        }
        return session('other_store_sessions');
    }

    public function getAllStoreSessionsArray() {
        $result = array();
        $firstLevelArray = $this->makeStoreSessionsArray();
        foreach ($firstLevelArray as $str) {
            $secondLevelArray = explode(',', $str);
            if (count($secondLevelArray) > 0) {
                array_push($result, array('nick' => $secondLevelArray[0], 'accessToken' => $secondLevelArray[1]));
            }
        }
        return $result;
    }

    private function makeStoreSessionsArray() {
        $result = array();
        $storeSessions = $this->getAllStoreSessions();
        if ($storeSessions != '') {
            $storeSessions = substr($storeSessions, 0, strlen($storeSessions) - 1);
            $result = explode(';', $storeSessions);
        }
        return $result;
    }
}
