
// odd, 单日
const SingleDay = 'aaa1.herokuapp.com'
// even, 双日
const DoubleDay = 'bbb2.herokuapp.com'
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
        response = Response.redirect(url.href);
        event.respondWith( response );
    }
    let nd = new Date();
    if (nd.getDate()%2) {
        host = SingleDay
    } else {
        host = DoubleDay
    }
    if (!CFproxy) {
        url.hostname=host;
        let request=new Request(url,event.request);
        event.respondWith( fetch(request) )
    } else {
        event.respondWith( fetchAndApply(event.request) );
    }
})

async function fetchAndApply(request) {
    let response = null;
    let url = new URL(request.url);
    url.host = host;

    let method = request.method;
    let body = request.body;
    let request_headers = request.headers;
    let new_request_headers = new Headers(request_headers);

    new_request_headers.set('Host', url.host);
    new_request_headers.set('Referer', request.url);

    let original_response = await fetch(url.href, {
        method: method,
        body: body,
        headers: new_request_headers
    });

    response = new Response(original_response.body, {
        status: original_response.status,
        headers: original_response.headers
    })

    return response;
}
