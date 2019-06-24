<?php

namespace RocketChat;

use Httpful\Request;
use RocketChat\Client;

class LiveChat extends Client {

  public $id;
  public $fname;
  public $agent;
  public $agent_mail;
  public $visitor_id;
  public $visitor_username;
  public $visitor_token;
  public $visitor_status;
  // public $visitor_mail;
  public $room_update;
  public $topic;
  public $department;
  public $messages;
  public $status;

  public function __construct($room, $agent){
    parent::__construct();
    if( isset($room->_id) ) {
      $this->id = $room->_id;
      $this->fname = $room->fname;
      $this->agent = $agent;
      $this->visitor_id = $room->v->_id;
      $this->visitor_username = $room->v->username;
      $this->visitor_token = $room->v->token;
      // $this->visitor_mail = $room->v->mail;
      $this->visitor_status = $room->v->status;
      $this->room_update = $room->_updatedAt;
      $this->messages = [];
      if ($room->lastMessage->t == 'livechat-close') {
        $this->topic = $room->lastMessage->msg;
      }
      if (isset($room->open) && $room->open == true) {
        $this->status = 'open';
      }
      else {
        $this->status = 'closed';
      }
      $this->department = $room->departmentId;
    }
  }

  public function messages_history($user_id, $user_auth_token) {

    $ordered_messages = [];

    $headers = ['X-Auth-Token' => $user_auth_token, 'X-User-Id' => $user_id];

    if (isset($this->id) && isset($this->visitor_token)) {
      $url_parameters = $this->id . '?token=' . $this->visitor_token;
      $response = Request::get($this->api . 'livechat/messages.history/' . $url_parameters)
        ->addHeaders($headers)
        ->send();

      $messages = [];

      if ($response->code == 200 && isset($response->body->success) && $response->body->success == TRUE) {
        foreach ($response->body->messages as $message) {
          $message_name = isset($message->alias) ? $message->alias : $message->u->username;
          if (isset($message->attachments)) {
            $messages[] = [
              "message_name" => $message_name,
              "message_msg" => $message->attachments[0]->title . ' - ' . $message->attachments[0]->description . ' - ' . variable_get('rocketchat_api_endpoint') . $message->attachments[0]->title_link,
              "message_time" => $message->ts
            ];
          }
          else {
            $messages[] = [
              "message_name" => $message_name,
              "message_msg" => $message->msg,
              "message_time" => $message->ts
            ];
          }
        }
      }

      $ordered_messages = array_reverse($messages);
    }

    return $ordered_messages;

  }

  public function get_all_departments() {
    $response = Request::get( $this->api . 'livechat/department/' )->send();
    if( $response->code == 200 && isset($response->body->success) && $response->body->success == true ) {
      $departments = $response->body->departments;
      return $departments;
    } else {
      $this->lastError = $response->body->error;
      return false;
    }
  }

  public function get_department() {
    $response = Request::get( $this->api . 'livechat/department/' . $this->department )->send();
    if( $response->code == 200 && isset($response->body->success) && $response->body->success == true ) {
      $department = $response->body->department;
      return $department;
    } else {
      $this->lastError = $response->body->error;
      return false;
    }
  }

  /**
   * Gets a user’s information, limited to the caller’s permissions.
   */
  public function get_agent_mail() {
    $response = Request::get( $this->api . 'users.info?userId=' . $this->agent )->send();

    if( $response->code == 200 && isset($response->body->success) && $response->body->success == true ) {
      // $this->id = $response->body->user->_id;
      // $this->nickname = $response->body->user->name;
      // $this->agent_email = $response->body->user->emails[0]->address;
      return $response->body->user->emails[0]->address;
    } else {
      echo( $response->body->error . "\n" );
      return false;
    }
  }

}
