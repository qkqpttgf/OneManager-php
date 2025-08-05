# OneManager Docker 部署指南

## 快速开始

### 1. 基础部署

使用 Docker Compose 快速部署 OneManager：

```bash
# 克隆项目
git clone https://github.com/qkqpttgf/OneManager-php.git
cd OneManager-php

# 启动服务
docker compose up -d
```

服务启动后，访问 `http://localhost:8080` 进行初始化配置。

### 2. 使用环境变量配置

创建 `.env` 文件自定义配置：

```bash
# 复制示例配置
cp .env.example .env

# 编辑配置
nano .env
```

示例 `.env` 配置：

```env
# 端口设置
HTTP_PORT=8080

# 时区设置
TZ=Asia/Shanghai

# PHP 配置
PHP_MEMORY_LIMIT=512M
PHP_UPLOAD_MAX_FILESIZE=128M
PHP_POST_MAX_SIZE=128M

# OneManager 配置
ONEMANAGER_CONFIG_SAVE=file

# 强制 HTTPS（当在反向代理后时）
FORCE_HTTPS=false
```

## 高级配置

### 数据持久化

OneManager 的配置数据默认保存在 Docker 卷 `onemanager-data` 中，对应容器内的 `/var/www/html/.data` 目录。

#### 方式一：使用 Docker 卷（推荐）

```yaml
volumes:
  - onemanager-data:/var/www/html/.data
```

#### 方式二：挂载到主机目录

```yaml
volumes:
  - ./data:/var/www/html/.data:rw
```

确保主机目录有正确的权限：

```bash
mkdir -p ./data
chmod 755 ./data
chown 33:33 ./data  # www-data 用户
```

### 反向代理配置

#### 使用 Nginx 反向代理

1. 取消注释 `docker-compose.yml` 中的 nginx 服务
2. 创建 `nginx.conf` 配置文件：

```nginx
events {
    worker_connections 1024;
}

http {
    upstream onemanager {
        server onemanager:80;
    }

    server {
        listen 80;
        server_name your-domain.com;
        return 301 https://$server_name$request_uri;
    }

    server {
        listen 443 ssl http2;
        server_name your-domain.com;

        ssl_certificate /etc/nginx/ssl/cert.pem;
        ssl_certificate_key /etc/nginx/ssl/key.pem;

        location / {
            proxy_pass http://onemanager;
            proxy_set_header Host $host;
            proxy_set_header X-Real-IP $remote_addr;
            proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
            proxy_set_header X-Forwarded-Proto $scheme;
        }
    }
}
```

3. 将 SSL 证书放置在 `./ssl/` 目录下

#### 使用 Traefik 反向代理

在 `docker-compose.yml` 中添加 Traefik 标签：

```yaml
services:
  onemanager:
    # ... 其他配置
    labels:
      - "traefik.enable=true"
      - "traefik.http.routers.onemanager.rule=Host(`your-domain.com`)"
      - "traefik.http.routers.onemanager.tls=true"
      - "traefik.http.routers.onemanager.tls.certresolver=letsencrypt"
```

## 环境变量说明

| 变量名 | 默认值 | 说明 |
|--------|--------|------|
| `HTTP_PORT` | `8080` | HTTP 服务端口 |
| `TZ` | `UTC` | 容器时区 |
| `PHP_MEMORY_LIMIT` | `256M` | PHP 内存限制 |
| `PHP_UPLOAD_MAX_FILESIZE` | `64M` | 上传文件大小限制 |
| `PHP_POST_MAX_SIZE` | `64M` | POST 数据大小限制 |
| `ONEMANAGER_CONFIG_SAVE` | `file` | 配置保存方式（file/env） |
| `FORCE_HTTPS` | `false` | 强制 HTTPS 重定向 |

## 常用命令

### 查看日志

```bash
# 查看所有服务日志
docker compose logs

# 查看特定服务日志
docker compose logs onemanager

# 实时跟踪日志
docker compose logs -f onemanager
```

### 重启服务

```bash
# 重启所有服务
docker compose restart

# 重启特定服务
docker compose restart onemanager
```

### 更新应用

```bash
# 拉取最新代码
git pull

# 重新构建并启动
docker compose up -d --build
```

### 备份数据

```bash
# 备份配置数据
docker run --rm -v onemanager-data:/data -v $(pwd):/backup alpine tar czf /backup/onemanager-backup.tar.gz -C /data .

# 恢复数据
docker run --rm -v onemanager-data:/data -v $(pwd):/backup alpine tar xzf /backup/onemanager-backup.tar.gz -C /data
```

## 故障排除

### 常见问题

1. **端口冲突**
   - 修改 `.env` 文件中的 `HTTP_PORT` 值
   - 或使用 `docker compose down` 停止其他占用端口的服务

2. **权限问题**
   - 确保数据目录权限正确：`chown 33:33 ./data`
   - 检查 SELinux 设置（如果启用）

3. **内存不足**
   - 增加 `PHP_MEMORY_LIMIT` 值
   - 检查主机可用内存

4. **文件上传失败**
   - 调整 `PHP_UPLOAD_MAX_FILESIZE` 和 `PHP_POST_MAX_SIZE`
   - 检查磁盘空间

### 调试模式

临时启用 PHP 错误显示：

```bash
docker compose exec onemanager sh -c "echo 'display_errors = On' >> /usr/local/etc/php/conf.d/debug.ini"
docker compose restart onemanager
```

### 健康检查

检查容器健康状态：

```bash
docker compose ps
docker inspect onemanager-php-onemanager-1 --format='{{.State.Health}}'
```

## 安全建议

1. **定期更新**
   - 定期拉取最新的 Docker 镜像
   - 及时更新 OneManager 代码

2. **网络安全**
   - 不要直接暴露 OneManager 到公网
   - 使用反向代理并配置 SSL

3. **数据备份**
   - 定期备份配置数据
   - 使用外部存储保存重要数据

4. **访问控制**
   - 设置强密码
   - 限制管理面板访问 IP

## 生产环境部署

生产环境建议的 `docker-compose.yml` 配置：

```yaml
version: '3.8'

services:
  onemanager:
    build: .
    environment:
      - TZ=Asia/Shanghai
      - PHP_MEMORY_LIMIT=512M
      - ONEMANAGER_CONFIG_SAVE=file
      - FORCE_HTTPS=true
    volumes:
      - ./data:/var/www/html/.data:rw
    restart: unless-stopped
    healthcheck:
      test: ["CMD", "curl", "-f", "http://localhost:80/"]
      interval: 30s
      timeout: 10s
      retries: 3
      start_period: 40s
    networks:
      - onemanager-network

  nginx:
    image: nginx:alpine
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./nginx.conf:/etc/nginx/nginx.conf:ro
      - ./ssl:/etc/nginx/ssl:ro
    depends_on:
      - onemanager
    restart: unless-stopped
    networks:
      - onemanager-network

networks:
  onemanager-network:
    driver: bridge

volumes:
  onemanager-data:
```

这样的配置提供了更好的隔离性和安全性。