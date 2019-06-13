<?php

use PHPUnit\Framework\TestCase;
use Utils\UserList;

/**
 * @covers \Utils\Helpers
 */
class UserListTest extends TestCase {

    private $userList;

    public function setUp()
    {
        $this->userList = new UserList;

        $this->userList->users = [
            (object) [
                'socket_id' => '1',
                'user_id'   => '1',
                'name'      => 'User 1',
                'email'     => 'user1@example.com',
                'room'      => 'Some Room'
            ], (object)[
                'socket_id' => '2',
                'user_id'   => '2',
                'name'      => 'User 2',
                'email'     => 'user2@example.com',
                'room'      => 'SomeOtherRoom'
            ], (object) [
                'socket_id' => '3',
                'user_id'   => '3',
                'name'      => 'User 3',
                'email'     => 'user3@example.com',
                'room'      => 'Some Room'
            ]
        ];
    }

    public function tearDown()
    {
       $this->userList = NULL;
    }

    public function testAddUsers()
    {
        $user = (object) [
            'socket_id' => '4',
            'user_id'   => '4',
            'name'      => 'User 4',
            'email'     => 'ho.lehoangphi@gmail.com',
            'room'      => 'A room for now'
        ];

        $newUser = $this->userList->addUser($user);
        $this->assertEquals($user, $newUser);
        $this->assertCount(4, $this->userList->users);
    }

    public function testGetUser()
    {
        $user = $this->userList->getUser('123');

        $this->assertNull($user);

        $user = $this->userList->getUser('2');
        $expected = $this->userList->users[2];

        $this->assertEquals($expected, $user);
    }

    public function testGetRoomUsers()
    {
        $room = 'Some Room';

        $userList = $this->userList->getRoomUsers($room);
        $expected = [
            $this->userList->users[0]->user_id,
            $this->userList->users[2]->user_id
        ];

        $this->assertEquals($this->userList->users[0]->user_id, reset($userList)['user_id']);
        $this->assertEquals($this->userList->users[2]->user_id, end($userList)['user_id']);
    }

    public function testRemoveUser()
    {
        $userId = '3';

        $this->userList->removeUser($userId);

        $this->assertCount(3, $this->userList->users);

        $user = $this->userList->getUser($userId);

        $this->assertNull($user);
    }
}
