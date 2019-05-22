<?php

namespace RocketChat;

use Httpful\Request;
use RocketChat\Client;

class LiveChat extends Client {

  public $id;
  public $fname;
  public $agent;
  public $visitor_id;
  public $visitor_username;
  public $visitor_token;
  public $visitor_status;
  public $messages;

  public function __construct($room, $agent){
    parent::__construct();
    if( isset($room->_id) ) {
      $this->id = $room->_id;
      $this->fname = $room->fname;
      $this->agent = $agent;
      $this->visitor_id = $room->v->_id;
      $this->visitor_username = $room->v->username;
      $this->visitor_token = $room->v->token;
      $this->visitor_status = $room->v->status;
      $this->messages = [];
    }
  }

  public function messages_history($user_id, $user_auth_token) {

    $headers = ['X-Auth-Token' => $user_auth_token, 'X-User-Id' => $user_id];

    $url_parameters = $this->id . '?token=' . $this->visitor_token;
    $response = Request::get( $this->api . 'livechat/messages.history/' . $url_parameters)->addHeaders($headers)->send();

    $messages = [];

    if( $response->code == 200 && isset($response->body->success) && $response->body->success == true ) {
      foreach($response->body->messages as $message) {
        // $response->body->messages[1]->ts
        $messages[] = $message->u->name . ': '. $message->msg;
      }
    }

    return $messages;

  }

}
