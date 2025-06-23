
// odd, 单日
const SingleDay = 'https://aaa1.herokuapp.com'
// even, 双日
const DoubleDay = 'https://bbb2.herokuapp.com'

//const SingleDay = 'https://153xxxxx0.cn-hongkong.fc.aliyuncs.com/2016-08-15/proxy/onedrive/xxx/'
//const DoubleDay = 'https://153xxxxx0.cn-hongkong.fc.aliyuncs.com/2016-08-15/proxy/onedrive/xxx/'

// CF proxy all, 一切给CF代理，true/false
const CFproxy = true

// Used in cloudflare workers, odd or even days point to 2 heroku account.

// 由于heroku不绑卡不能自定义域名，就算绑卡后https也不方便
// 另外免费套餐每月550小时，有些人不够用
// 于是在CF Workers使用此代码，分单双日拉取不同heroku帐号下的相同网页
// 只改上面，下面不用动

addEventListener('fetch', event => {
    let url=new URL(event.request.url);
    if (url.protocol == 'http:') {
        url.protocol = 'https:'
        event.respondWith( Response.redirect(url.href) )
    } else {
        let response = null;
        let nd = new Date();
        if (nd.getDate()%2) {
            host = SingleDay
        } else {
            host = DoubleDay
        }
        if (host.substr(0, 7)!='http://'&&host.substr(0, 8)!='https://') host = 'http://' + host;

        response = fetchAndApply(host, event.request);

        event.respondWith( response );
    }
})

async function fetchAndApply(host, request) {
    let f_url = new URL(request.url);
    let a_url = new URL(host);
    let replace_path = a_url.pathname;
    if (replace_path.substr(replace_path.length-1)!='/') replace_path += '/';
    let replaced_path = '/';
    let query = f_url.search;
    let path = f_url.pathname;
    if (host.substr(host.length-1)=='/') path = path.substr(1);
    f_url.href = host + path + query;

    let response = null;
    if (!CFproxy) {
        response = await fetch(f_url, request);
    } else {
        let method = request.method;
        let body = request.body;
        let request_headers = request.headers;
        let new_request_headers = new Headers(request_headers);
        new_request_headers.set('Host', f_url.host);
        new_request_headers.set('Referer', request.url);

        response = await fetch(f_url.href, {
            method: method,
            body: body,
            headers: new_request_headers
        });
    }

    let out_headers = new Headers(response.headers);
    if (out_headers.get('Content-Disposition')=='attachment') out_headers.delete('Content-Disposition');
    let out_body = null;
    let contentType = out_headers.get('Content-Type');
    if (contentType.includes("application/text")) {
        out_body = await response.text();
        while (out_body.includes(replace_path)) out_body = out_body.replace(replace_path, replaced_path);
    } else if (contentType.includes("text/html")) {
        out_body = await response.text();
        while (replace_path!='/'&&out_body.includes(replace_path)) out_body = out_body.replace(replace_path, replaced_path);
    } else {
        out_body = await response.body;
    }

    let out_response = new Response(out_body, {
        status: response.status,
        headers: out_headers
    })

    return out_response;
}
