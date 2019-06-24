<?php

namespace RocketChat;

use Httpful\Request;

class Client{

	public $api;

	function __construct(){
		$this->api = ROCKET_CHAT_INSTANCE . REST_API_ROOT;

		// set template request to send and expect JSON
		$tmp = Request::init()
			->sendsJson()
			->expectsJson();
		Request::ini( $tmp );
	}

	/**
	* Get version information. This simple method requires no authentication.
	*/
	public function version() {
		$response = \Httpful\Request::get( $this->api . 'info' )->send();
		return $response->body->info->version;
	}

	/**
	* Quick information about the authenticated user.
	*/
	public function me() {
		$response = Request::get( $this->api . 'me' )->send();

		if( $response->body->status != 'error' ) {
			if( isset($response->body->success) && $response->body->success == true ) {
				return $response->body;
			}
		} else {
			echo( $response->body->message . "\n" );
			return false;
		}
	}

	/**
	* List all of the users and their information.
	*
	* Gets all of the users in the system and their information, the result is
	* only limited to what the callee has access to view.
	*/
	public function list_users(){
		$response = Request::get( $this->api . 'users.list' )->send();

		if( $response->code == 200 && isset($response->body->success) && $response->body->success == true ) {
			return $response->body->users;
		} else {
			echo( $response->body->error . "\n" );
			return false;
		}
	}

	/**
	* List the private groups the caller is part of.
	*/
	public function list_groups() {
		$response = Request::get( $this->api . 'groups.list' )->send();

		if( $response->code == 200 && isset($response->body->success) && $response->body->success == true ) {
			$groups = array();
			foreach($response->body->groups as $group){
				$groups[] = new Group($group);
			}
			return $groups;
		} else {
			echo( $response->body->error . "\n" );
			return false;
		}
	}

	/**
	* List the channels the caller has access to.
	*/
	public function list_channels() {
		$response = Request::get( $this->api . 'channels.list' )->send();

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

  /**
   * List the channels the caller has access to.
   */
  public function list_rooms($user_id, $user_auth_token, $updatedSince = NULL) {

    if ($updatedSince != NULL ) {
      $updatedSince = "?updatedSince=" .  $updatedSince;
    }

    $headers = ['X-Auth-Token' => $user_auth_token, 'X-User-Id' => $user_id];
    $response = Request::get( $this->api . 'rooms.get' . $updatedSince )->addHeaders($headers)->send();

    if( $response->code == 200 && isset($response->body->success) && $response->body->success == true ) {
      $chats = array();
      foreach($response->body->update as $room){
        $chats[] = new LiveChat($room, $user_id);
      }
      return $chats;
    } else {
      echo( $response->body->error . "\n" );
      return false;
    }
  }


  /**
   * List all livechat users
   * @return array
   */
  public function list_user_managers($type = TYPE_AGENT)
  {
    $response = Request::get($this->api . 'livechat/users/'.$type)->send();
    if( $response->code == 200 && isset($response->body->success) && $response->body->success == true ) {
      $list = array();
      foreach($response->body->users as $livechatUserData){
        $list[] = $livechatUserData;
      }
      return $list;
    } else {
      $this->lastError = $response->body->error;
      return false;
    }
  }

  public function register_visitor($visitor) {
    $response = Request::post( $this->api . 'livechat/visitor' )
      ->body(array('visitor' => array('name' => $visitor['name'], 'email' => $visitor['email'], 'token' => $visitor['token'], 'department' => $visitor['department'] )))
      ->send();

    if( $response->code == 200 && isset($response->body->success) && $response->body->success == true ) {
      return true;
    } else {
      echo( $response->body->message . "\n" );
      return false;
    }
  }

  public function get_department_agents($department) {
    $response = Request::get( $this->api . 'livechat/department/' . $department->_id )->send();
    if( $response->code == 200 && isset($response->body->success) && $response->body->success == true ) {
      $agents = $response->body->agents;
      return $agents;
    } else {
      $this->lastError = $response->body->error;
      return false;
    }
  }

  public function get_presence($user_id) {

    $response = Request::get( $this->api . 'users.getPresence?userId=' . $user_id)->send();

    if( $response->code == 200 && isset($response->body->presence) ) {
      return $response->body->presence;
    } else {
      echo( $response->raw_body->error . "\n" );
      return false;
    }
  }

}
