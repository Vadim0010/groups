class AjaxMessageSend {

    constructor(selector, url, data) {
        this.container = $(selector);
        this.url = url;
        this.data = data;
        this.status = true;

        this.container.find(this.selectors.add_form).on(
            'click',
            this.showFrom.bind(this)
        );

        this.container.find(this.selectors.send_form).on(
            'click',
            this.sendMessage.bind(this)
        );

        this.container.find(this.selectors.cancel_from).on(
            'click',
            this.hideFrom.bind(this)
        );

        this.container.find(this.selectors.reply_form).on(
            'click',
            this.replyMessage.bind(this)
        );

        this.container.find(this.selectors.inp_reply).on(
            'keydown',
            this.pressEnterReply.bind(this)
        );

        this.container.find(this.selectors.dialog_list).on(
            'click',
            this.getChatMessage.bind(this)
        )
    }

    get selectors() {
        return {
            block_form: '#block-form-message',
            add_form: '#message-add-form',
            send_form: '#message-send-form',
            reply_form: '#message-reply-form',
            cancel_from: '#message-cancel-form',
            inp_token: 'input#message_token',
            inp_sub: 'input#message_subject',
            inp_reply: 'input#message_reply',
            inp_body: 'textarea#message_body',
            message_list: '#dialog-messages-container',
            dialog_list: '.dialog-list-element',
            chat_list: '.dialog-list-wrapper',
            count_new_msg: '.count-new-message',
            dialog_form: '#dialog-message-form',
            inp_chat: '#message_chat_id',
            chat_name: '#header-chat-name',
            count_unread_msg: '#count-all-unread-messages',
            last_msg_list: '#last-message-list-container',
            preloader: '<div id="preloader"><div id="loader"></div></div>'
        }
    }

    sendMessage() {
        let btn_send = this.container.find(this.selectors.send_form),
            _token = this.container.find(this.selectors.inp_token),
            group = this.data.group,
            recipient = this.data.recipient,
            subject = this.container.find(this.selectors.inp_sub),
            body = this.container.find(this.selectors.inp_body),
            message = {
                _token: _token.val(),
                recipient: recipient,
                group: group,
                subject: subject.val(),
                body: body.val()
            };

        $.ajax({
            url: this.url.chat_new,
            method: 'POST',
            data: message,
            beforeSend: () => {
                btn_send.addClass('is-loading');
            },
            success: (data) => {
                if ( ! data.errors) {
                    this.callNoty(data.msg, 'success');
                    body.val('');
                    this.hideFrom();
                }
            },
            error: (jqXHR) => {
                console.log(jqXHR);
                btn_send.removeClass('is-loading');
                this.callNoty(jqXHR.responseJSON ? jqXHR.responseJSON.msg : jqXHR.statusText, 'error');
            },
            complete: () => {
                btn_send.removeClass('is-loading');
            }
        });
    }

    replyMessage() {
        let btn_send = this.container.find(this.selectors.reply_form),
            _token = this.container.find(this.selectors.inp_token),
            reply_msg = this.container.find(this.selectors.inp_reply),
            chat = this.container.find(this.selectors.inp_chat),
            message_list = this.container.find(this.selectors.message_list),
            message = {
                _token: _token.val(),
                chat: chat.val(),
                reply_msg: reply_msg.val()
            };

        if (!this.status || !message.reply_msg.length) {
            return false;
        }

        $.ajax({
            url: this.url.chat_reply,
            method: 'POST',
            data: message,
            beforeSend: () => {
                btn_send.addClass('is-loading');
                this.status = false;
            },
            success: (data) => {
                if ( ! data.errors) {
                    let current_chat = this.container.find('#chat-block-' + chat.val());

                    reply_msg.val(null);
                    message_list.append(data.template);
                    this.scrollMessageList(message_list);

                    if (current_chat.length) {
                        current_chat.detach().prependTo(this.container.find(this.selectors.chat_list));
                    }
                }
            },
            error: (jqXHR) => {
                console.log(jqXHR);
                btn_send.removeClass('is-loading');
                this.callNoty(jqXHR.responseJSON ? jqXHR.responseJSON.msg : jqXHR.statusText, 'error');
            },
            complete: () => {
                btn_send.removeClass('is-loading');
                this.status = true;
            }
        });
    }

    pressEnterReply(e) {
        if (e.keyCode === 13) {
            this.replyMessage()
        }
    }

    getChatMessage(e) {
        let chat = $(e.currentTarget),
            chat_id = chat.data('chat'),
            chats = this.container.find(this.selectors.dialog_list),
            count_new_msg = chat.find(this.selectors.count_new_msg),
            count_all_new_msg = $('body').find(this.selectors.count_unread_msg),
            unread_messages_list = $('body').find(this.selectors.last_msg_list),
            dialog_form = this.container.find(this.selectors.dialog_form),
            message_list = this.container.find(this.selectors.message_list),
            chat_name = this.container.find(this.selectors.chat_name);

        if (chat.hasClass('active_el') || !this.status) {
            return false;
        }

        chats.removeClass('active_el');

        $.ajax({
            url: this.url.chat_read,
            method: 'POST',
            data: {chat: chat_id},
            beforeSend: () => {
                message_list.empty();
                message_list.html(this.selectors.preloader);
                chat.addClass('active_el');
                this.status = false;
            },
            success: (data) => {
                let number_all_new_msg = Number(count_all_new_msg.text()),
                    number_new_msg = Number(count_new_msg.text());

                message_list.empty();
                message_list.append(data.template);
                chat_name.text(data.chat_name);
                dialog_form.show();
                dialog_form.find(this.selectors.inp_chat).val(chat_id);

                if (number_all_new_msg !== 0) {
                    if (number_all_new_msg >= number_new_msg) {
                        let unread_messages = unread_messages_list.find('.last-message-el[data-chat=' + chat_id + ']');

                        count_all_new_msg.text(number_all_new_msg - number_new_msg);

                        if (number_all_new_msg - number_new_msg === 0 ) {
                            unread_messages_list.remove();
                        } else {
                            unread_messages.remove();
                        }
                    }
                }

                count_new_msg.remove();
                this.scrollMessageList(message_list);
                this.container.find(this.selectors.inp_reply).focus();
            },
            error: (jqXHR) => {
                console.log(jqXHR);
                message_list.empty();
                this.callNoty(jqXHR.responseJSON ? jqXHR.responseJSON.msg : jqXHR.statusText, 'error');
            },
            complete: () => {
                this.status = true;
            }
        });
    }

    callNoty(text, type) {
        new Noty({
            text: text,
            type: type
        }).show();
    }

    scrollMessageList(message_list) {
        message_list.animate(
            {scrollTop: message_list[0].scrollHeight },
            "fast"
        );
    }

    showFrom() {
        this.container.find(this.selectors.add_form).hide();
        this.container.find(this.selectors.block_form).show();
    }

    hideFrom() {
        this.container.find(this.selectors.block_form).hide();
        this.container.find(this.selectors.add_form).show();
    }
}

window.AjaxMessageSend = AjaxMessageSend;