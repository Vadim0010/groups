class AjaxComment {
    constructor(selector, url, url_delete) {
        this.container = $(selector);
        this.url = url;
        this.url_delete = url_delete;
        this.textarea = this.container.find('textarea[name=comment-form]');

        this.container.find('#add-message-button').on(
            'click',
            this.sendComment.bind(this)
        );

        this.container.find('#container-comment-list').on(
            'click',
            'a.reply-comment',
            this.handleReplyComment.bind(this)
        );

        this.container.find('#container-comment-list').on(
            'click',
            'a.delete-comment',
            this.deleteComment.bind(this)
        );
    }

    sendComment() {
        let btn_send = this.container.find('#add-message-button'),
            comment = this.textarea.val(),
            group = this.textarea.attr('data-group-id'),
            answer_comment = this.textarea.attr('data-answer-comment'),
            token = this.container.find('input[name=_csrf_token]').val();

        $.ajax({
            url: this.url,
            method: 'post',
            data: {
                comment: comment,
                group: group,
                answer_comment: answer_comment,
                _token: token
            },
            beforeSend: () => {
                btn_send.addClass('is-loading');
            },
            success: (data) => {
                if ( ! data.errors ) {
                    let comment_list = this.container.find('#container-comment-list');

                    comment_list.append(data.template);
                    this.callNoty(data.msg, 'success');
                    this.textarea.val('');
                    this.textarea.attr('data-answer-comment', '');

                    $('html, body').animate(
                        {scrollTop: comment_list.find('#container-comment-' + data.comment_id).offset().top},
                        500
                    );
                }
            },
            error: (jqXHR) => {
                this.callNoty(jqXHR.responseJSON.msg, 'error');
                console.log(jqXHR.responseJSON);
                btn_send.removeClass('is-loading');
            },
            complete: () => {
                btn_send.removeClass('is-loading');
            }
        });
    }

    handleReplyComment(e) {
        e.preventDefault();
        let target = e.target,
            answer_comment_id = $(target).attr('data-comment'),
            answer_comment_container = this.container.find('#container-comment-' + answer_comment_id);

        this.textarea.attr('data-answer-comment', answer_comment_id);
        this.textarea.val(this.getValueReplyComment(answer_comment_container)).focus();
    }

    getValueReplyComment(comment) {
        let message =  comment.find('.message-text').html().trim().replace(/<br>/g, '');
        return '[blockquote]' + message + '[/blockquote]\n';
    }

    deleteComment(e) {
        e.preventDefault();
        let target = e.target,
            comment_id = $(target).attr('data-comment'),
            token = this.container.find('input[name=_csrf_token]').val();

        if (this.url_delete) {
            $.ajax({
                url: this.url_delete,
                method: 'DELETE',
                data: {comment: comment_id, _token: token},
                beforeSend: () => {
                    $(target).addClass('is-loading');
                },
                success: (data) => {
                    if ( ! data.errors ) {
                        this.container.find('#container-comment-' + comment_id).remove();
                        this.callNoty(data.msg, 'warning')
                    }
                },
                error: (jqXHR) => {
                    this.callNoty(jqXHR.responseJSON.msg, 'error');
                    console.log(jqXHR.responseJSON);
                    $(target).removeClass('is-loading');
                },
                complete: () => {
                    $(target).removeClass('is-loading');
                }
            });
        }


    }

    callNoty(text, type) {
        new Noty({
            text: text,
            type: type
        }).show();
    }
}

window.AjaxComment = AjaxComment;