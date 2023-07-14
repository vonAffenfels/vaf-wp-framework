import $ from 'jquery';

export function ajaxRequest(action, params, successCb, errorCb)
{
    if (!window[action]) {
        console.error('Action ' + action + ' not configured!');
        return;
    }

    if (!window[action].ajaxurl) {
        console.error('AJAX URL for action ' + action + ' not configured!');
        return;
    }

    $.ajax({
        url: window[action].ajaxurl,
        type: 'post',
        data: Object.assign(params, window[action].data),
        success: function (response) {
            const data = response.data || {};
            if (response.success) {
                successCb(data);
            } else {
                errorCb(data.message);
            }
        },
        error: function (request, status, error) {
            const json = request.responseJSON || {};
            const data = json.data || {};
            errorCb(data.message || error);
        }
    })
}