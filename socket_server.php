<?php  
require 'vendor/autoload.php';

use Workerman\Worker;
use PHPSocketIO\SocketIO;
use Utils\Message;
use Utils\UserList;
use Singleton\UserFactory;

use function Utils\Helpers\isRealString;

$io = new SocketIO(2020);
$io->userList = new UserList;

$io->on('connection', function($socket) use($io){
    echo "New user connected\n";

    //Emit event to let the user know connection was succesfull.
    //Which is completely unnecessary
    $socket->emit('connect');

    $socket->on('join', function($data, $callback) use($socket, $io) {
        if(empty($data['token']) || !$io->userList->isValidToken($data['token']))
        {
            $callback([
                'error' => 'Token is invalid'
            ]);
            return;
        }

        $tokenDecoded = base64_decode($data['token']);
        $userId = substr($tokenDecoded, 0, strlen($tokenDecoded) - 14); // YmdHis format has 14 characters
        $factory = UserFactory::Instance();
        $user = $factory->select('users', '*' , ['user_id' => $userId]);
        if (empty($user)) {
            $callback([
                'error' => 'User not found'
            ]);
            return;
        }
        $user = reset($user);
        $user['room'] = 'public';

        $messageType = 'joined';
        if ($io->userList->getUser($userId)) {
            $messageType = 'back';
        }
        $user['socket_id'] = $socket->id;
        $user = (object) $user;
        //Assign user to public room first
        $io->userList->addUser($user);

        //Join a room
        $socket->join($user->room);

        //Send message to all users in the room except sender
        $socket->to('public')->broadcast
            ->emit('newMessage', Message::fromAdmin($user->name, $messageType));
        //Send message to user
        $socket->emit('welcome', Message::fromAdmin($user->name, 'greeting'));

        //Send message to all users in the room
        $io->in($user->room)->emit('updateUserList', $io->userList->getRoomUsers($user->room));

        $callback([
            'success' => 'Welcome to the chat room'
        ]);
    });

    $socket->on('createMessage', function($data, $callback) use($socket, $io) {
        $user = $io->userList->getUserBySocketId($socket->id);

        if($user === NULL)
        {
            $callback([
                'error' => 'You must log in', 
                'noUser' => TRUE
            ]);
            return;
        }

        if( !isRealString($data['text']) )
        {
            $callback([
                'error' => 'Please send a valid message'
            ]);
            return;
        }

        $io->in($user->room)
            ->emit('newMessage', Message::create($user->name, $data['text']));

        $callback([
            'success' => 'Message send succesfully'
        ]);
    });

    $socket->on('sendDM', function($data, $callback) use($socket, $io) {
        $user = $io->userList->getUserBySocketId($socket->id);
        if($user === NULL)
        {
            $callback([
                'error' => 'You must log in', 
                'noUser' => TRUE
            ]);
            return;
        }

        
        if( !isRealString($data['text']) )
        {
            $callback([
                'error' => 'Please send a valid message'
            ]);
            return;
        }

        $toUser = $io->userList->getUser($data['to']);

        if ($toUser != null) {
            //Send message to user
            $socket->to($toUser->socket_id)->emit('newDM', Message::fromUser($user, $data['text']));
        } else {
            $socket->to($socket->id)->emit('dmUserOffline', 'Your friend is currently offline.');
        }
        
    });

    $socket->on('shareLocation', function($data, $callback) use($socket, $io) {
        $user = $io->userList->getUserBySocketId($socket->id);

        if($user === NULL)
        {
            $callback([
                'error' => 'You must log in', 
                'noUser' => TRUE
            ]);
            return;
        }

        $io->in($user->room)
            ->emit(
                'newLocationUrl', 
                Message::shareUserLocation(
                    $user->name, 
                    $data['latitude'], 
                    $data['longitude']
                )
            )
        ;
    });

    $socket->on('disconnect', function() use($socket, $io){
        echo "User disconnected\n";

        $user = $io->userList->getUserBySocketId($socket->id);
        if($user === NULL)
            return;

        $socket->leave($user->room);
        $io->userList->removeUser($user->user_id);

        $io->in($user->room)
            ->emit('newMessage', Message::fromAdmin($user->name, 'leave'));

        $io->in($user->room)
            ->emit(
                'updateUserList', 
                $io->userList->getRoomUsers($user->room)
            )
        ;
    });

    $socket->on('signup', function($data, $callback) use($socket, $io) {
        echo "User signing up\n";
        if (empty($data['name'])) {
            $callback([
                'error' => 'Please enter your name'
            ]);
            return;
        }

        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $callback([
                'error' => 'Please enter a valid email'
            ]);
            return;
        }

        $factory = UserFactory::Instance();
        $isExisting = $factory->has('users', [
            'email' => $data['email']
        ]);

        if ($isExisting) {
            $callback([
                'error' => 'Your entered email is being in used. Please choose a different email'
            ]);
            return;
        }

        if (empty($data['password']) || strlen($data['password']) < 6) {
            $callback([
                'error' => 'Password can not empty and 6 characters at least'
            ]);
            return;
        }

        if ($data['password'] != $data['confirm_password']) {
            $callback([
                'error' => 'Confirm password does not match'
            ]);
            return;
        }

        try {
            $factory = UserFactory::Instance();
            $createdAt = (new \DateTime)->format('YmdHis');
            $factory->insert('users', [
                'name'      => $data['name'],
                'email'     => $data['email'],
                'password'  => md5($data['password']),
                'created_at'=> $createdAt
            ]);

            $userId = $factory->id();
        } catch (Exception $ex) {
            $callback([
                'error' => $ex->getMessage()
            ]);
            return;
        }
        $text = [
            'token'     => base64_encode($userId . $createdAt),
            'user_id'   => $userId,
            'name'      => $data['name'],
            'room'      => 'public'
        ];

        //Send message to user
        $socket->emit('login_success', Message::fromAdmin($data['name'], json_encode($text)));
    });

    $socket->on('signin', function($data, $callback) use($socket, $io) {
        echo "User signing up\n";
       
        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $callback([
                'error' => 'Please enter a valid email'
            ]);
            return;
        }

        if (empty($data['password']) || strlen($data['password']) < 6) {
            $callback([
                'error' => 'Password can not empty and 6 characters at least'
            ]);
            return;
        }

        $factory = UserFactory::Instance();
        $user = $factory->select('users', '*' , [
            'email'     => $data['email'],
            'password'  => md5($data['password'])
        ]);
        if (empty($user)) {
            $callback([
                'error' => 'Your login credential is incorrect'
            ]);
            return;
        }
        $user = reset($user);
        $text = [
            'token'     => base64_encode($user['user_id'] . (new \DateTime($user['created_at']))->format('YmdHis')),
            'user_id'   => $user['user_id'],
            'name'      => $user['name'],
            'room'      => 'public'
        ];

        //Send message to user
        $socket->emit('login_success', Message::fromAdmin($user['name'], json_encode($text)));

    });
});

Worker::runAll();

?>