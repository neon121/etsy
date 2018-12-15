let E = {
    glb: {},
    toggleLoadingScreen: function(on) {
        let div = $('#loading');
        let screens = $('.screen');
        if (on) {
            div.removeClass('hidden').addClass('visible');
            screens.addClass('blur');
        }
        else {
            div.removeClass('visible').addClass('hidden');
            if ($('.form.visible').length === 0) screens.removeClass('blur');
        }
    },
    toggleForm: function(type = null, id = null) {
        if (type === null) {
            $('.form.visible').removeClass('visible').addClass('hidden');
            $('.screen').removeClass('blur');
        }
        else {
            let form = $('.form[data-entity="'+type+'"]');
            if (form.length === 0) throw new Error('No form with type '+type);
            E.clearInputError(form.find('.error'));
            $('.screen').addClass('blur');
            if (id === null) {
                form.attr('data-id', null);
                form.find('input[type=text]').val('');
                form.find('select option[data-default="true"]').prop('selected', true);
                form.removeClass('hidden').addClass('visible');
            }
            else {
                form.attr('data-id', id);
                E.toggleLoadingScreen(true);
                E.callAPI('getEntity', {type: type, id: id}).then(response => {
                    E.toggleLoadingScreen(false);
                    form.removeClass('hidden').addClass('visible');
                    let inputs = form.find('input, select');
                    for (let i = 0; i < inputs.length; i++) {
                        let input = inputs.eq(i);
                        let value = response[input.attr('name')];
                        if (input.type === 'checkbox') input.prop('checked', value);
                        else input.val(value);
                    }
                });
            }
        }
    },
    saveForm: function() {
        let form = $('.form.visible');
        let id = form.attr('data-id');
        let type = form.attr('data-entity');
        let tab = $('.tab[data-hash="'+type+'"]');
        let data = {};
        let inputs = form.find('input, select');
        for (let i = 0; i < inputs.length; i++) {
            let input = inputs.eq(i);
            let name = input.attr('name');
            if (input.attr('type') === 'checkbox') data[name] = input.prop('selected');
            else data[name] = input.val();
            if (!E.testValue(type, name, data[name])) {
                E.msg('Значение не соответствует ограничениям', 'error');
                input.addClass('error');
                return;
            }
        }
        E.toggleLoadingScreen(true);
        let action, post;
        if (id === undefined) {
            action = 'addEntity';
            post = {type: type, data: data};
        }
        else {
            action = 'editEntity';
            post = {type: type, id: id, data: data}
        }
        E.callAPI(action, post).then(response => {
            E.toggleLoadingScreen(false);
            if (response.inputError === undefined) {
                let tr;
                if (action === 'addEntity') {
                    tr = tab.find('.model').clone(true).removeClass('model').prependTo(tab.find('tbody'));
                    tr.attr('data-id', response.id);
                }
                else tr = tab.find('.entity[data-id='+id+']');
                for (let name in response.values) if (response.values.hasOwnProperty(name))
                    data[name] = response.values[name];
                E.formatEntityBlock(tr, data);
                E.toggleForm();
            }
            else {
                E.msg(response.inputError.txt, 'error');
                form.find('[name="'+response.inputError.input+'"]').addClass('error');
            }
        });
    },
    toggleTab: function(li) {
        let hash = li.attr('data-hash');
        li.parents('.tabs').find('li.active').removeClass('active');
        li.addClass('active');
        let screen = li.parents('.screen');
        screen.find('.tab.active').removeClass('active');
        screen.find('.tab[data-hash="'+hash+'"]').addClass('active');
        E.set({activeTab: hash});
        switch (hash) {
            case 'Shop': case'User':
                E.loadData(hash);
                break;
            default:
                E.msg('No loading configured');
                break;
        }
    },
    loadData: function(hash) {
        E.toggleLoadingScreen(true);
        let tab = $('.tab[data-hash="'+hash+'"]');
        let table = tab.find('table');
        table.find('tbody tr').remove();
        E.callAPI('getList', {type: hash}).then(response => {
            E.toggleLoadingScreen(false);
            let model = table.find('.model');
            for (let i in response) {
                let entity = response[i];
                let tr = model.clone(true).removeClass('model').attr('data-id', entity.id).prependTo(tab.find('tbody'));
                E.formatEntityBlock(tr, entity);
            }
        })
    },
    formatEntityBlock: function(tr, data) {
        let nodes = tr.find('[data-name]');
        for (let i = 0; i < nodes.length; i++) {
            let node = nodes.eq(i);
            let name = node.attr('data-name');
            let value;
            if (data[name] === undefined) continue;
            value = data[name];
            switch (node[0].nodeName) {
                case 'INPUT':
                case 'SELECT':
                    node.val(value);
                    break;
                default:
                    switch (node.attr('data-type')) {
                        case 'date':
                            node.text(E.countTextDate(value, false)).attr('title', value);
                            break;
                        default:
                            node.text(value);
                            break;
                    }
                    break;
            }
        }
        let svgBlocks = tr.find('[data-addSVG]');
        for (let i = 0; i < svgBlocks.length; i++) {
            let svgBlock = svgBlocks.eq(i);
            let svgs = svgBlock.attr('data-addSVG').split(' ');
            for (let svg of svgs) {
                $.ajax('css/img/'+svg)
                    .then(response => svgBlock.html(svgBlock.html() + response.documentElement.outerHTML));
            }
            svgBlock.attr('data-addSVG', null);
        }
    },
    appendEntityEvents: function(tr) {
        tr.find('.delete').click(function() {
            if ($(this).hasClass('confirm')) {
                let tr = $(this).parents('.entity').addClass('removed');
                setTimeout(() => tr.remove(), parseFloat(tr.css('transition').split(' ')[1]));
                E.callAPI('deleteEntity', {type: tr.parents('.tab').attr('data-hash'), id: tr.attr('data-id')});
            }
            else {
                $(this).addClass('confirm');
                setTimeout(() => $(this).removeClass('confirm'), 5000);
            }
        });
        tr.find('.edit').click(function() {
            E.toggleForm($(this).parents('.tab').attr('data-hash'), $(this).parents('tr').attr('data-id'));
        });
        tr.find('input, select')
            .focus(function() {
                E.prevInputValue = $(this).val();
            })
            .change(function() {
                let data = {};
                let type = $(this).parents('.tab').attr('data-hash');
                let name = $(this).attr('data-name');
                let value = $(this).attr('type') === 'checkbox' ? $(this).prop('checked') : $(this).val();
                if (!E.testValue(type, name, value)) {
                    E.msg('Значение не соответствует ограничениям', 'error');
                    $(this).addClass('error');
                    return;
                }
                data[name] = value;
                E.callAPI('editEntity', {
                    type: type,
                    id: $(this).parents('.entity').attr('data-id'),
                    data: data
                }).then(response => {
                    if (response.inputError !== undefined) {
                        E.msg(response.inputError.txt, 'error');
                        $(this).addClass('error');
                        $(this).val(E.prevInputValue);
                    }
                });
            })
            .keypress(function() {E.clearInputError($(this));});
    },
    clearInputError: function(obj) {
        obj.removeClass('error');
        E.msg('');
    },
    testValue: function(type, name, value) {
        return E.glb[type] === undefined
            || E.glb[type].regex[name] === undefined
            || E.glb[type].regex[name].test(value);
    },
    callAPI: (action, POST = {}) => {
        POST.action = action;
        return new Promise(resolve => {
            E.get('hash').then(response => {
                if (response !== undefined) POST.hash = response;
                if (E.glb.debug) console.log('-->', POST);
                $.ajax({
                    url: API,
                    method: 'POST',
                    data: POST,
                    complete: function (jqXHR, textStatus) {
                        try {
                            if (textStatus !== 'success') {
                                console.log(jqXHR);
                                throw new Error('textStatus = ' + textStatus);
                            }
                            let response = jqXHR.responseText;
                            try {
                                response = JSON.parse(response);
                            }
                            catch (e) {
                                throw new Error('Cant parse JSON. Response:\n' + response);
                            }
                            if (response.output !== undefined) {
                                console.log('Output:\n' + response.output);
                            }
                            if (response.error !== undefined) throw new Error('Got error:\n' + response.error);
                            if (E.glb.debug) console.log('<--', response.result);
                            resolve(response.result);
                        } catch (e) {
                            E.toggleLoadingScreen(false);
                            E.msg('Got error, see console', 'error');
                            console.log(e.message);
                        }
                    }
                });
                
            });
        })
    },
    toggleAuth: (on) => {
        if (on) {
            E.get(['hash', 'role', 'login', 'activeTab']).then(response => {
                $('.screen.login').removeClass('visible');
                let screen = null;
                if (response.role === 'manager') screen = $('.screen.manager');
                else screen = $('.screen.admin');
                screen.addClass('visible');
                $('.hello .login').text(response.login);
                $('.hello .role').text(response.role);
                if (response.activeTab !== undefined) {
                    let tab = screen.find('.tabs li[data-hash="'+response.activeTab+'"]');
                    if (tab.length > 0) tab.click();
                    else screen.find('.tabs li').eq(0).click();
                }
                else screen.find('.tabs li').eq(0).click();
            })
        }
        else {
            $('.screen.visible').removeClass('visible');
            $('.screen.login').addClass('visible');
            E.rm(['hash', 'role', 'login']);
        }
    },
    msg: (str, type = '') => {
        let msg = $('#message').removeClass('visible');
        if (str) {
            void msg[0].offsetWidth;
            msg.text(str).attr('class', type).addClass('visible');
            setTimeout(() => msg.click(), 10000);
        }
        else setTimeout(() => msg.text(''), parseFloat(msg.css('transition').split(' ')[1]));
    },
    get: (name = null) => {
        return new Promise(resolve => {
            chrome.storage.local.get(name, data => {
                resolve((name === null || typeof name === 'object') ? data : data[name]);
            });
        })
    },
    set: obj => {
        return new Promise(resolve => {
            chrome.storage.local.set(obj, () => resolve(true));
        });
    },
    rm: name => {
        return new Promise(resolve => {
            chrome.storage.local.remove(name, () => resolve(true))
        });
    },
    countTextDate: function(value, future) {
        let now = new Date();
        value = new Date(value);
        value.setMinutes(value.getMinutes() - now.getTimezoneOffset());
        let dif = Math.round((value - now) / 1000);
        let str = '';
        let periods = {
            month: 3600 * 24 * 30,
            day: 3600 * 24,
            hour: 3600,
            minute: 60,
        };
        future = (future === undefined ? dif > 0 : future);
        if (Math.abs(dif) > periods.month) {
            if (future) return 'больше месяца';
            else return 'давно';
        }
        else if (Math.abs(dif) < periods.minute) {
            if (future) return 'меньше минуты';
            else return 'только что';
        }
        dif = Math.abs(dif);
        if (dif > periods.day) {
            let num = Math.floor(dif / (periods.day));
            str += num + ' ';
            switch (true) {
                case num >= 5 : str += 'дней'; break;
                case num >= 2 : str += 'дня'; break;
                case num === 1 : str += 'день'; break;
            }
        }
        else if (dif > periods.hour) {
            let num = Math.floor(dif / (periods.hour));
            str += num + ' ';
            switch (true) {
                case num >= 5 : str += 'часов'; break;
                case num >= 2 : str += 'часа'; break;
                case num === 1 : str += 'час'; break;
            }
        }
        else if (dif > periods.minute) {
            let num = Math.floor(dif / (periods.minute));
            str += num + ' ';
            switch (true) {
                case num >= 5 : str += 'минут'; break;
                case num >= 2 : str += 'минуты'; break;
                case num === 1 : str += 'минута'; break;
            }
        }
        if (future) return str;
        else return str + ' назад';
    }
};