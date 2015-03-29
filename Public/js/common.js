function checkResponse(response) {
    if (response != null && typeof response == 'string' && ~response.indexOf('::')) {
        var url = response.split('::')[1],
            msg = '';
        if (response.indexOf('reauth') == 0) {
            msg = '抱歉，阿里给予您的api调用次数已满，51网已为您更换接口，请重新授权，谢谢！';
        } else if (response.indexOf('timeout') == 0) {
            msg = '抱歉，会话已超时，请重新登录，谢谢!';
        } else if (response.indexOf('verify') == 0) {
            msg = '抱歉，您操作过于频繁，请输入验证码!';
        }
        alert(msg);
        window.location = url;
    }
}

function yaIndexOf(arr, elt /*, from*/) {
    var len = arr.length >>> 0;
    var from = Number(arguments[2]) || 0;
    from = (from < 0)
        ? Math.ceil(from)
        : Math.floor(from);
    if (from < 0)
        from += len;
    for (; from < len; from++) {
        if (from in arr &&
            arr[from] === elt)
            return from;
    }
    return -1;
};

function html_decode(str) {
    var s = "";
    if (str.length == 0) return "";
    s = str.replace(/&amp;/g, "&");
    s = s.replace(/&lt;/g, "<");
    s = s.replace(/&gt;/g, ">");
    s = s.replace(/&nbsp;/g, " ");
    s = s.replace(/&#39;/g, "\'");
    s = s.replace(/&quot;/g, "\"");
    s = s.replace(/<br>/g, "\n");
    return s;
}
