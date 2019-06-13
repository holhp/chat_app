
# Chat app using php SocketIO & Workerman 
A server side alternative implementation of [socket.io](https://github.com/socketio/socket.io) in PHP based on [Workerman](https://github.com/walkor/Workerman.
<br>
Client side: Javascript with libraries: socketio, mustache (template makeup), vex (dialog)

## Feature list

- Signin
- Signup
- Remember when logged in
- Public chat, private chat

## Requiments

1. PHP > 7.0, MySQL > 4

2. Check your these two ports ```3000 & 2020``` and close them if they are being used


3. Check out this link to know that your browser supports websocket or not

``https://crossbar.io/docs/Browser-Support/``

## Step to run

1. Run composer update <br>

2. Export data in <br>
```database/chat_app.sql```

3. Start php web server <br>
```php -S localhost:3000 -t public```

4. Start Websocket server <br>

```php socket_server.php start``` for debug mode

```php socket_server.php start -d ``` for daemon mode

```php socket_server.php stop``` Stop Websocket server

```php socket_server.php status``` Status

5. Open your browser and access <br>

```localhost:3000```

6. Signup or login with default accounts <br>
- user1@example.com/123456
- user2@example.com/123456
- user3@example.com/123456

# Deliverables:
- Time spent: 15 hours
- Testcase: added
- Live testing url: will upload soon
