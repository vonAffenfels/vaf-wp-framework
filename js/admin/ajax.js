import $ from 'jquery';

export function ajaxRequest(action, params, success, error)
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
                success(data);
            } else {
                error(data.message);
            }
        },
        error: function (request, status, error) {
            const json = request.responseJSON || {};
            const data = json.data || {};
            error(data.message || error);
        }
    })
}