import $ from 'jquery';

const { __ } = wp.i18n;

export const NOTICE_TYPE = {
    ERROR: 'notice-error',
    WARNING: 'notice-warning',
    SUCCESS: 'notice-success',
    INFO: 'notice-info'
};

export function showNotice(content, type = NOTICE_TYPE.INFO, isDismissible = true, autoDismis = 0)
{
    if (Object.values(NOTICE_TYPE).indexOf(type) === -1) {
        console.error('Notice type [' + type + '] not supported!');
        return;
    }

    const elContent = $('<p>' + content + '</p>');
    const elOuterDiv = $('<div>');
    elOuterDiv.addClass('notice');
    elOuterDiv.addClass(type);

    if (isDismissible) {
        elOuterDiv.addClass('is-dismissible');

        function dismisNotice()
        {
            elOuterDiv.fadeTo(100, 0, function () {
                elOuterDiv.slideUp(100, function () {
                    elOuterDiv.remove();
                });
            });
            if (dismisTimer) {
                clearTimeout(dismisTimer);
            }
        }

        let dismisTimer = null;
        if (autoDismis) {
            dismisTimer = setTimeout(dismisNotice, autoDismis);
        }

        const button = $('<button type="button" class="notice-dismiss"><span class="screen-reader-text"></span></button>');
        button.find('.screen-reader-text').text(__('Dismiss this notice.'));
        button.on('click.wp-dismiss-notice', (event) => {
            event.preventDefault();
            dismisNotice();
        });
        elOuterDiv.append(button);
    }

    elOuterDiv.append(elContent);

    const noticeList = $('.notice');
    if (noticeList.length === 0) {
        // No notice is there. So we insert it as the first child into #wpbody-content
        const elWpBodyContent = $('#wpbody-content');
        if (!elWpBodyContent) {
            console.error('<div> with id #wpbody-content not found! Are you inside admin backend?');
            return;
        }

        elWpBodyContent.prepend(elOuterDiv);
    } else {
        noticeList.last().after(elOuterDiv);
    }
}