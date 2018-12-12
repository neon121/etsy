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
            screens.removeClass('blur');
        }
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
    msg: (str, type = '') => {
        let msg = $('#message').removeClass('visible');
        if (str) {
            void msg[0].offsetWidth;
            msg.text(str).addClass(type).addClass('visible');
        }
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
    }
};