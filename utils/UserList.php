<?php 

namespace Utils;
use function Utils\Helpers\arrayFind;
use function Utils\Helpers\isRealString;
/**
* Users utils
*/
class UserList
{
    public $users;
    public $config;

    function __construct()
    {
        $this->users = [];
    }

    /**
     * @param array $userData input submitted by the user
     */

    public function isValidToken($token)
    {
        return !empty($token) && isRealString($token);
    }


    public function addUser($userData)
    {
        $userId = $userData->user_id;
        $newUser = (object) $userData;
        
        $this->users[$userId] = $newUser;
        return $newUser;
    }

    public function getUser($userId)
    {
        return isset($this->users[$userId]) ? $this->users[$userId] : NULL;
    }

    public function getUserBySocketId($socketId)
    {
        foreach ($this->users as $user) {
            if ($user->socket_id == $socketId) {
                return $user;
            }
        }
        return NULL;
    }

    public function getRoomUsers($room)
    {
        $filter_func = function($user) use($room) {
            return $user->room === $room;
        };

        $filteredUsers = array_filter($this->users, $filter_func);
        if( empty($filteredUsers) )
            return NULL;

        $uniqueUserList = [];
        foreach ($filteredUsers as $user) {
            $uniqueUserList[$user->user_id] = [
                'user_id'   => $user->user_id,
                'name'      => $user->name
            ];
        };
        return $uniqueUserList;
    }

    public function removeUser($userId)
    {
        unset($this->users[$userId]);
        return $this->users;
    }
}
