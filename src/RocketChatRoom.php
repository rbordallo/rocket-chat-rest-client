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

}
