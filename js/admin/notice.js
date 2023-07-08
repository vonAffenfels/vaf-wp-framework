import $ from 'jquery';

export const NOTICE_TYPE = {
    ERROR: 'notice-error',
    WARNING: 'notice-warning',
    SUCCESS: 'notice-success',
    INFO: 'notice-info'
};

export function showNotice(content, type = NOTICE_TYPE.INFO, isDismissible = true)
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
    }

    elOuterDiv.append(elContent);

    console.log("notice", elOuterDiv);

    const noticeList = $('.notice');
    console.log("NOTICE LIST", noticeList);
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