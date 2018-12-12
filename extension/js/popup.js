const DEBUG = true;
const API = 'https://myvds.ml/etsy/api.php';
$(() => {
    E.get('glb').then(response => {
        return new Promise(resolve => {
            if (typeof response === 'undefined')
                E.callAPI('getGlobals').then(response => {
                    E.set({glb: response});
                    resolve(response);
                });
            else resolve(response);
        });
    }).then(response => {
        E.glb = response;
        for (let i in E.glb.user.regex)
            E.glb.user.regex[i] = new RegExp(E.glb.user.regex[i].substr(1).slice(0, -1));
    });
    E.get(['hash', 'role', 'login']).then(response => {
        if (response.hash !== undefined) {
            $('.screen.login').removeClass('visible');
            if (response.role === 'manager') $('.screen.manager').addClass('visible');
            else $('.screen.admin').addClass('visible');
        }
        if (response.login !== undefined) {
            $('.screen.login [name=login]').val(response.login);
            $('.hello .login').text(response.login);
            $('.hello .role').text(response.role);
        }
    });
    
    $('#message').click(() => E.msg(''));
    
    let screenLogin = $('.screen.login');
    screenLogin.find('input').keypress(function() {
        if ($(this).hasClass('error')) {
            $(this).removeClass('error');
            E.msg('');
        }
    });
    screenLogin.find('button').click(event => {
        event.preventDefault();
        let form = $('.screen.login');
        let loginInput = form.find('[name=login]');
        let login = loginInput.val();
        let pwdInput = form.find('[name=password]')
        let password = pwdInput.val();
        E.msg('');
        if (E.glb.user.regex.login.test(login) === false) {
            E.msg('Логин указан неверно', 'error');
            loginInput.addClass('error');
            return;
        }
        if (E.glb.user.regex.password.test(password) === false) {
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
                $('.screen.login').removeClass('visible');
                if (response.role === 'manager') $('.screen.manager').addClass('visible');
                else $('.screen.admin').addClass('visible');
                $('.hello .login').text(login);
                $('.hello .role').text(response.role);
            }
        });
    });
    
    $('.screen .exit').click(() => {
        E.toggleLoadingScreen(true);
        E.callAPI('destroySession').then(() => {
            $('.screen.visible').removeClass('visible');
            $('.screen.login').addClass('visible');
            E.rm(['hash', 'role']);
            E.toggleLoadingScreen(false);
        });
    });
});