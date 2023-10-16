<img width="320" alt="YoPrint" src="https://github.com/aimanaziz1993/YoPrint/assets/43428455/9e7e66de-2262-4761-8b79-fe38d7ff1052" />

The application's primary functionality involves allowing users to upload CSV files into the
system. Once uploaded, the system processes the files in the background. 

Users are then notified when the processing is complete. Additionally, the application provides users with a
history of all file uploads.

<div align="center">
  <img width="1440" alt="Main page" src="https://github.com/aimanaziz1993/YoPrint/assets/43428455/e4e6656c-38f2-487e-9565-80ec32670299">
</div>

## Quickstart

Install with **Composer** and **NPM**:

```bash
$ git clone https://github.com/aimanaziz1993/YoPrint.git

$ composer install && npm install
```

Setting up **Environment**:

```bash
$ cp .env.example .env
$ php artisan key:generate
```

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=yoprint
DB_USERNAME=root
DB_PASSWORD=

BROADCAST_DRIVER=log
CACHE_DRIVER=redis
FILESYSTEM_DISK=local
QUEUE_CONNECTION=redis
SESSION_DRIVER=file
SESSION_LIFETIME=120

MEMCACHED_HOST=127.0.0.1

REDIS_CLIENT=predis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

Install **Redis on macOS**:

[👉 Checkout Redis docs](https://redis.io/docs/getting-started/installation/install-redis-on-mac-os/)

## Horizon Installation

Documentation for Horizon can be found on the [Laravel website](https://laravel.com/docs/horizon).

---

## Running on Localhost

Serving Laravel locally:

```bash
$ php artisan migrate
$ php artisan serve
```

Serving Vite:

```bash
$ npm run dev
```

Serving Horizon Dashboard:

```bash
$ php artisan horizon
```

Access Horizon dashboard:

`http://127.0.0.1:8000/horizon`

Access main page at:

`http://127.0.0.1:8000`

---

> ⚠️ **NOTE**: Experimental job-batching features of [Laravel 10.0 - Queues](https://laravel.com/docs/10.x/queues#job-batching).
- Real-time fetch are using simple pole with JavaScript. Websockets are not implemented.
- Application are not tested using any unit test.

---

## Main features ✅

- Laravel 10.0
- Laravel Horizon
- Redis server
- Drag and drop multiple **CSV only** files upload. Using [Dropzone](https://github.com/dropzone/dropzone)
- Auto queue with job batches
- Max file size: `50MB`
  - Chunked uploads (upload large files in smaller chunks)
- History of all recent uploaded files
- Upload same file multiple times without duplicating entries.