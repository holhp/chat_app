var socket = io(socket_host);
vex.defaultOptions.className = 'vex-theme-default';
vex.defaultOptions.hasCallback = true;
vex.defaultOptions.escapeButtonCloses = true;
vex.defaultOptions.overlayClosesOnClick = true;

var signUpForm = signInForm = {};

function signIn() {
    var template = document.getElementById('sign-in-form').innerHTML;
    var message = Mustache.render(template, signInForm);
    var vexObj = vex.dialog.open({
        message: 'Sign In',
        input: message,
        buttons: [
            Object.assign({}, vex.dialog.buttons.YES, { text: 'Join' }),
            Object.assign({}, vex.dialog.buttons.NO, {
                text: 'Back',
                click: function() {
                    vexObj.options.hasCallback = false;
                    this.close()
                    signOptions()
                }
            })
        ],
        callback: function(data) {
            signInForm = data;
            if (vexObj.options.hasCallback) {
                socket.emit('signin', data, signInResponse);
            }
        }
    });
}

function signInResponse(data) {
    if (data.error)
        return appMessage(data.error, signIn);

}

function signUp() {
    var template = document.getElementById('sign-up-form').innerHTML;
    var message = Mustache.render(template, signUpForm);
    var vexObj = vex.dialog.open({
        message: 'Sign Up',
        input: message,
        buttons: [
            Object.assign({}, vex.dialog.buttons.YES, {
                text: 'Sign Up'
            }),
            Object.assign({}, vex.dialog.buttons.NO, {
                text: 'Back',
                click: function() {
                    vexObj.options.hasCallback = false;
                    this.close()
                    signOptions()
                }
            })
        ],
        callback: function(data) {
            signUpForm = data;
            if (vexObj.options.hasCallback) {
                socket.emit('signup', data, signUpResponse);
            }
        }
    });
}

function signUpResponse(data) {
    if (data.error)
        return appMessage(data.error, signUp);
}

function setUserStorage(userData) {
    localStorage.setItem('user', JSON.stringify(userData))
}

function getUserStorage() {
    var user = localStorage.getItem('user');
    return JSON.parse(user)
}

function isLoggedIn() {
    return getUserStorage() != null
}

function signOptions() {
    vex.dialog.open({
        message: 'Welcome',
        escapeButtonCloses: false,
        overlayClosesOnClick: false,
        buttons: [
            Object.assign({}, vex.dialog.buttons.YES, {
                text: 'Sign Up',
                className: 'inline-btn',
                click: function() {
                    signUp()
                }
            }),
            Object.assign({}, vex.dialog.buttons.YES, {
                text: 'Sign In',
                className: 'inline-btn',
                click: function() {
                    signIn()
                }
            })
        ]
    });
}

function welcome() {
    return isLoggedIn() ? login() : signOptions()
}

function appMessage(message, callback) {
    var opts = {
        message: message,
        buttons: [vex.dialog.buttons.YES]
    };

    if (callback) {
        opts.callback = callback;
    }

    vex.dialog.open(opts);
}

function joinRoom(response) {
    var user = JSON.parse(response.text);
    var data = {
        "token": user.token
    }
    setUserStorage(user);
    socket.emit('join', data, roomResponse);
}

function login() {
    var user = getUserStorage();
    if (user == null) {
        return welcome()
    }
    var data = {
        'token': user.token
    };
    socket.emit('join', data, roomResponse);

}

function roomResponse(data) {
    if (data.error)
        return appMessage(data.error, welcome);

    document.getElementById('msg-body').focus();
    console.log(data.success);
}

function sendMessage(e) {
    e.preventDefault();
    var text = document.getElementById('msg-body');

    var message = {
        text: text.value,
    };

    socket.emit('createMessage', message, function(resp) {
        if (resp.noUser)
            return appMessage(resp.error, welcome);

        console.log(resp);
        text.value = '';
        document.getElementById('msg-body').focus();
    });
}

function renderMessage(response) {
    var messageData = {
        from: response.from,
        text: response.text,
        createdAt: messageTimestamp(response.createdAt),
        opts: response.opts
    };

    var template = document.getElementById('message-template').innerHTML;
    var message = Mustache.render(template, messageData);

    var list = document.getElementById('messages');
    list.innerHTML += message;

    scrollToBottom(list);
}

function messageTimestamp(date) {
    var options = {
        hour: 'numeric',
        minute: 'numeric',
        hour12: true
    };

    return new Intl.DateTimeFormat(['en-US'], options)
        .format(date)
        .toLocaleLowerCase();
}

function sendLocation(e) {
    if (!navigator.geolocation)
        return appMessage('Browser not supported');

    var btnShareLocation = this;

    btnShareLocation.innerText = 'Sending...';
    btnShareLocation.setAttribute('disabled', 'disabled')

    navigator.geolocation.getCurrentPosition(
        function(position) {
            var coords = {
                latitude: position.coords.latitude,
                longitude: position.coords.longitude
            };

            socket.emit('shareLocation', coords, function(resp) {
                if (resp.noUser)
                    return appMessage(resp.error, welcome);

                document.getElementById('msg-body').focus();
            });

            btnShareLocation.innerText = 'Send Location';
            btnShareLocation.removeAttribute('disabled');
        },
        function() {
            btnShareLocation.innerText = 'Send Location';
            btnShareLocation.removeAttribute('disabled');
            appMessage('Unable to fetch location');
        }
    );
}

function scrollToBottom(list) {
    var listTotalHeight = list.scrollHeight;
    var listVisiblePortion = list.clientHeight;

    if (listTotalHeight <= listVisiblePortion)
        return;

    var addedItem = list.lastElementChild;
    var lastItem = addedItem.previousElementSibling;

    var pixelsFromTop = list.scrollTop;
    var addedItemHeight = addedItem.clientHeight;
    var lastItemHeight = lastItem.clientHeight;

    var total = listVisiblePortion + pixelsFromTop + addedItemHeight + lastItemHeight;

    if (total >= listTotalHeight)
        list.scrollTop = listTotalHeight;
}

function updateUserList(users) {
    var list = document.createElement('ol');
    var currentUser = getUserStorage();
    for (var key in users) {
        if (users[key].user_id == currentUser.user_id) {
            continue;
        }
        var li = document.createElement('li');
        var userId = users[key].user_id;
        li.setAttribute('id', getUserLiTagByUserId(userId))
        li.setAttribute('title', 'Click to send direct message.')
        li.setAttribute('data-message-user-id', userId);
        li.setAttribute('data-message-user-name', users[key].name);
        li.innerText = users[key].name;
        list.appendChild(li);
    }

    var userList = document.getElementById('users');
    userList.innerHTML = '';
    userList.appendChild(list);
}

function getUserLiTagByUserId(userId) {
    return 'li' + userId;
}

document.getElementById('message-form').addEventListener('submit', sendMessage);
document.getElementById('send-location').addEventListener('click', sendLocation);

document.addEventListener('click', function(e) {

    // USER LIST: Open DM box
    if (e.target && e.target.getAttribute('data-message-user-id') != null) {
        openDmBoxFromUserId(e.target.getAttribute('data-message-user-id'))
    }

    // Close DM box
    if (e.target && e.target.getAttribute('data-message-remove-btn') != null) {
        var boxId = e.target.getAttribute('data-message-remove-btn');
        // Go to parent node then delete it
        var element = document.getElementById(boxId);
        element.parentNode.removeChild(element);
    }
});

function sendDmMessage(e) {
    e.preventDefault();
    var form = e.target;
    var text = form.getElementsByTagName('input')[0].value;
    if (!text) {
        return;
    }
    var user = getUserStorage();
    var toUserId = e.target.getAttribute('data-user-id')
    var message = {
        text: text,
        from: user.user_id,
        to: toUserId

    };
    form.getElementsByTagName('input')[0].value = "";
    socket.emit('sendDM', message, function(resp) {
        if (resp.noUser)
            return appMessage(resp.error, welcome)
    });
    var dmBox = document.getElementById(getDmBoxId(toUserId));
    var template = document.getElementById('message-template').innerHTML;
    var messageData = {
        from: 'You',
        text: text,
        createdAt: messageTimestamp(new Date()),
        opts: { plain: true }
    };

    var message = Mustache.render(template, messageData);
    var list = dmBox.getElementsByClassName('message_container')[0];
    list.innerHTML += message;

    scrollToBottom(list);
}

function setActiveDmBox(boxId) {
    document.getElementById(boxId).getElementsByTagName("input")[0].focus();
}


function renderDmMessage(response) {
    var fromUserId = response.fromUserId;
    var messageData = {
        fromUserId: fromUserId,
        from: response.from,
        text: response.text,
        createdAt: messageTimestamp(response.createdAt),
        opts: response.opts
    };
    var dmBox = openDmBoxFromUserId(fromUserId)
    var template = document.getElementById('message-template').innerHTML;
    var message = Mustache.render(template, messageData);
    var list = dmBox.getElementsByClassName('message_container')[0];
    list.innerHTML += message;

    scrollToBottom(list);
}

function getDmBoxId(userId) {
    return 'dmBox_' + userId;
}

function openDmBoxFromUserId(userId) {
    var boxId = getDmBoxId(userId);
    // Open a new tab
    if (document.getElementById(boxId) == null) {
        var userLiElement = document.getElementById(getUserLiTagByUserId(userId));
        var userId = userLiElement.getAttribute('data-message-user-id');
        var userName = userLiElement.getAttribute('data-message-user-Name');
        var boxData = { "userId": userId, "userName": userName };
        var template = document.getElementById('dm-box-template').innerHTML;
        var dmBoxHtml = Mustache.render(template, boxData);
        document.getElementById('chat__main').innerHTML += dmBoxHtml;
    }

    setActiveDmBox(boxId)

    return document.getElementById(boxId);
}

welcome();

socket.on('connect', function() {
    console.log('Connected to server');
});

socket.on('login_success', joinRoom);
socket.on('newMessage', renderMessage);
socket.on('welcome', renderMessage);
socket.on('updateUserList', updateUserList);
socket.on('newLocationUrl', renderMessage);
socket.on('newDM', renderDmMessage);

socket.on('disconnect', function() {
    console.log('Disconnected from server');
})