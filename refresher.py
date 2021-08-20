import requests
import time
import hashlib

proxies = {
    "http": None,
    "https": None,
}

s = requests.session()

url = "https://你的域名/?admin"
t = str(int(time.time()))
temp = t + "你的密码"
s1 = hashlib.sha1(temp.encode("utf-8")).hexdigest()
data = "password1=" + str(s1) + "&timestamp=" + t
r = s.post(url=url, data=data, proxies=proxies)
if "Login Success!" in r.text:
    print("登录成功！")
else:
    print("登录失败~")
url = "https://你的域名/?RefreshCache"
r = s.get(url)
if "<h1>RefreshCache</h1>" in r.text:
    print("刷新成功！")
else:
    print("刷新失败~")
