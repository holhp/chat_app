
# Chat app using php SocketIO & Workerman 
A server side alternative implementation of [socket.io](https://github.com/socketio/socket.io) in PHP based on [Workerman](https://github.com/walkor/Workerman.
<br>
Client side: Javascript with libraries: socketio, mustache (template makeup), vex (dialog)

## Requiments

1. PHP > 7.0, MySQL > 4

2. Check your these two ports and close them if thery are being used
3000 & 2020

3. Please check out this link to know that your browser supports websocket or not

``https://crossbar.io/docs/Browser-Support/``

## Step to run

1.Run composer update

2. Export data in
```database/chat_app.sql```

3. Start php web server
```php -S localhost:3000 -t public```

4. Start Websocket server
```php socket_server.php start``` for debug mode

```php socket_server.php start -d ``` for daemon mode

```php socket_server.php stop``` Stop Websocket server

```php socket_server.php status``` Status

5. Open your browser and access

```localhost:3000```

6. Signup or login with default accounts
- user1@example.com/123456
- user2@example.com/123456
- user3@example.com/123456

# Deliverables:
- Time spent: 10 hours


