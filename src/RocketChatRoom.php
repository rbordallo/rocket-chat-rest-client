<?php

namespace RocketChat;

use Httpful\Request;
use RocketChat\Client;

class Room extends Client {

	public $id;
	public $name;

	public function __construct(){
		parent::__construct();
		if( is_string($name) ) {
			$this->name = $name;
		} else if( isset($name->_id) ) {
			$this->name = $name->name;
			$this->id = $name->_id;
		}
	}

	/**
	* List the channels the caller has access to.
	*/
	public function list_rooms() {
		$response = Request::get( $this->api . 'rooms.get' )->send();

		if( $response->code == 200 && isset($response->body->success) && $response->body->success == true ) {
			$groups = array();
			foreach($response->body->channels as $group){
				$groups[] = new Channel($group);
			}
			return $groups;
		} else {
			echo( $response->body->error . "\n" );
			return false;
		}
	}

	function create_room ($visitor_token) {
    $response = Request::get( $this->api . 'livechat/room	?token=' . $visitor_token )->send();

    if( $response->code == 200 && isset($response->body->success) && $response->body->success == true ) {
      return $response->body->room->_id;
    } else {
      echo( $response->body->message . "\n" );
      return false;
    }
  }

  public function send_message($visitor_token, $room_id, $message) {
    $response = Request::post( $this->api . 'livechat/message' )
      ->body(array('token' => $visitor_token, 'rid' => $room_id, 'msg' => $message ))
      ->send();

    if( $response->code == 200 && isset($response->body->success) && $response->body->success == true ) {
      return true;
    } else {
      echo( $response->body->message . "\n" );
      return false;
    }
  }


}
