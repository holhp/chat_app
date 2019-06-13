<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="/css/styles.css">
    <link rel="stylesheet" href="/css/vex.css">
    <link rel="stylesheet" href="/css/vex-theme-default.css">
    <script type="text/javascript">
        var socket_host = "<?= $socket_host ?>";
    </script>
    <title>Chat | ChatApp</title>
</head>
<body class="chat">

    <div class="chat__sidebar">
        <h3>People</h3>
        <div id="users"></div>
    </div>

    <div id="chat__main" class="chat__main">
        <ol id="messages" class="chat__messages"></ol>

        <div class="chat__footer">
            <form id="message-form">
                <input type="text" name="message" id="msg-body" 
                    placeholder="Message" autocomplete="off">
                <button id="msg-send">Send</button>
            </form>
            <button id="send-location">Send Location</button>
        </div>
        
    </div>

</body>

<script id="message-template" type="x-tmpl-mustache">
    <li class="message">
        <div class="message__title">
            <h4>{{from}}</h4>
            <span>{{createdAt}}</span>
        </div>
        <div class="message__body">
            <p>
                {{#opts.plain}}
                    {{ text }}
                {{/opts.plain}}

                {{#opts.anchor}}
                    <a href="{{opts.href}}" target="_blank">{{text}}</a>
                {{/opts.anchor}}
            </p>
        </div>
    </li>
</script>

<script id="sign-up-form" type="x-tmpl-mustache">
    <label>Display name</label>
    <input type="text" name="name" value="{{name}}" autofocus/>
    <label>Email</label>
    <input type="text" name="email" value="{{email}}"/>
    <label>Password</label>
    <input type="password" name="password" value=""/>
    <label>Cofirm password</label>
    <input type="password" name="confirm_password" value=""/>
</script>

<script id="sign-in-form" type="x-tmpl-mustache">
    <label>Email</label>
    <input type="text" name="email" value="{{email}}" autofocus/>
    <label>Password</label>
    <input type="password" name="password" value=""/>
</script>

<script id="dm-box-template" type="x-tmpl-mustache">
    <div id="dmBox_{{userId}}" class="dm_box">
        <form class="form_message" data-user-id="{{userId}}" onsubmit="return sendDmMessage(event)">
            <div class="name_container">
                {{userName}} 
                <span data-message-remove-btn="dmBox_{{userId}}">x<span>
            </div>
            <div class="message_container">

            </div>
            <div class="input_container">
                <input type=text name="dm_input"/>
                <button type="submit">Send</button>
            </div>
        </form>
    </div>
</script>

<script src="/js/vendors/mustache.min.js"></script>
<script src="/js/vendors/vex.combined.min.js"></script>
<script src="/js/vendors/socket.io.js"></script>
<script src="/js/polyfill.js"></script>
<script src="/js/chat.js?v=1491313943549"></script>

</html>