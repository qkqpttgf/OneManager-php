
// Hosts Array
// 服务器数组
const H = [
    'https://herooneindex.herokuapp.com/',
    'https://onemanager.glitch.me/',
    'https://onemanager-php.vercel.app/'
]

// View Type
// 1 , only first host,
//     只第一条Host记录有用
// 2 , view top 2 host as odd/even days,
//     只有前两条记录有效，分别单双日运行
// 3 , view random host
//     所有记录随机访问
const T = 1

// CF proxy all, true/false
// 一切给CF代理，true或false
const CFproxy = true

// Used in cloudflare workers
// // // // // //

addEventListener('fetch', event => {
    let url=new URL(event.request.url);
    if (url.protocol == 'http:') {
        // force HTTPS
        url.protocol = 'https:'
        event.respondWith( Response.redirect(url.href) )
    } else {
        let host = null;
        if (T===1) {
            host = H[0];
        }
        if (T===2) {
            host = H[new Date().getDate()%2];
        }
        if (T===3) {
            let n = H.length;
            host = H[Math.round(Math.random()*n*10)%n];
        }
        //console.log(host)
        if (host.substr(0, 7)!='http://'&&host.substr(0, 8)!='https://') host = 'http://' + host;

        let response = fetchAndApply(host, event.request);

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
            /*cf: {
                cacheEverything: true,
                cacheTtl: 1000,
                mirage: true,
                polish: "on",
                minify: {
                    javascript: true,
                    css: true,
                    html: true,
                }
            },*/
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
        while (replace_path!='/'&&out_body.includes(replace_path)) out_body = out_body.replace(replace_path, replaced_path);
    } else if (contentType.includes("text/html")) {
        //f_url.href + 
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
