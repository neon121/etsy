const DEBUG = true;
const API = 'https://myvds.ml/etsy/api.php';
$(() => {
    E.get('glb').then(response => {
        return new Promise(resolve => {
            if (typeof response === 'undefined' || true)
                E.callAPI('getGlobals').then(response => {
                    E.set({glb: response});
                    resolve(response);
                });
            else resolve(response);
        });
    }).then(response => {
        E.glb = response;
        for (let i in E.glb.User.regex)
            E.glb.User.regex[i] = new RegExp(E.glb.User.regex[i].substr(1).slice(0, -1));
        for (let i in E.glb.Shop.regex)
            E.glb.Shop.regex[i] = new RegExp(E.glb.Shop.regex[i].substr(1).slice(0, -1));
    });
    E.get(['hash', 'role', 'login']).then(response => {
        if (response.hash) {
            E.toggleAuth(true);
            E.callAPI('checkAuth', response).then(response => {
                if (!response || response === 'AUTH_EXPIRED') {
                    E.msg("Авторизация потеряна", 'error');
                    E.toggleAuth(false);
                }
            });
        }
    });
    $.ajax('css/img/gear.svg').then(response => {
        $('.tabs li[data-hash="opts"]').html(response.documentElement.outerHTML);
    });
    
    $('#message').click(() => E.msg(''));
    
    let screenLogin = $('.screen.login');
    screenLogin.find('input').keypress(function() {E.clearInputError($(this))});
    screenLogin.find('button').click(event => {
        event.preventDefault();
        let form = $('.screen.login');
        let loginInput = form.find('[name=login]');
        let login = loginInput.val();
        let pwdInput = form.find('[name=password]');
        let password = pwdInput.val();
        E.msg('');
        if (E.glb.User.regex.login.test(login) === false) {
            E.msg('Логин указан неверно', 'error');
            loginInput.addClass('error');
            return;
        }
        if (E.glb.User.regex.password.test(password) === false) {
            E.msg('Пароль указан неверно', 'error');
            pwdInput.addClass('error');
            return;
        }
        E.toggleLoadingScreen(true);
        E.callAPI('checkLogin', {login: login, password: password}).then(response => {
            E.toggleLoadingScreen(false);
            if (response === 'NO_SUCH_USER') {
                E.msg('Такой пользователь не найден', 'error');
                loginInput.addClass('error');
            }
            else if (response === 'WRONG_PASSWORD') {
                E.msg('Пароль неверен', 'error');
                pwdInput.addClass('error');
            }
            else {
                E.set({'hash': response.hash, role: response.role, login: login});
                E.toggleAuth(true);
            }
        });
    });
    let screens = $('.screen');
    screens.find('.add').click(function() {
        E.toggleForm($(this).parents(".tab").attr('data-hash'));
    });
    E.appendEntityEvents(screens.find('.model'));
    screens.find('.exit').click(() => {
        E.toggleLoadingScreen(true);
        E.callAPI('destroySession').then(() => {
            E.toggleAuth(false);
            E.toggleLoadingScreen(false);
        });
    });
    screens.find('.tabs li').click(function() {
        E.toggleTab($(this));
    });
    
    let form = $('.form');
    form.find('input, select').keypress(function() {E.clearInputError($(this))});
    form.find('.close').click(() => E.toggleForm());
    form.find('.ok').click(() => E.saveForm());
});