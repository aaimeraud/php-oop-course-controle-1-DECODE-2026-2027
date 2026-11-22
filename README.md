<div align="center">
<h1>Contact API</h1>
  <p><strong>A JSON REST API in PHP to store, read, update and delete contact forms.</strong></p>
</div>

## Overview

A contact form sends a name, a subject and a message. This API receives those forms over HTTP, checks them, and stores each one as a JSON file. It lets a client create, list, read, partially update and delete contacts, with explicit HTTP status codes.

The project is built without a framework: the routing, request and response handling are written from scratch, to show how a web request is handled from the router down to the controller.

## Features

- Create, list, read, partially update and delete contact forms
- Routes declared in `app/config/routes.json`, with a `<filename>` placeholder in URIs
- Input validation: allowed keys, value types, email format, JSON body and `Content-Type` header
- Safe file names: values coming from the URL are decoded and checked before use
- Explicit HTTP status codes: 200, 201, 204, 400, 404, 405 and 500

### Known limitations

- Storage is file-based: there is no database.
- Two contacts created with the same email during the same second get the same file name, and the second one overwrites the first.
- There is no authentication.
- `app/var/contacts` is inside the web root, and Apache serves its files directly. Do not store real personal data with this version.

## Requirements

- Docker
- Docker Compose

## Installation

```shell
git clone https://github.com/aaimeraud/contact-api-php
cd contact-api-php
docker compose up -d --build
docker compose exec php-framework-php composer dump-autoload
```

The API is then available at `http://127.0.0.1:8080`. Opening `/` returns `success`.

## Usage

Create a contact:

```shell
curl -i -X POST http://127.0.0.1:8080/contact \
  -H 'Content-Type: application/json' \
  -d '{"email":"leia@alderaan.com","subject":"Help me Obi Wan","message":"You are my only hope"}'
```

The response contains the file name, for example `{"file":"2026-11-22_14-20-54_leia@alderaan.com.json"}`. In the URL, use the name **without** the `.json` extension:

```shell
curl -i http://127.0.0.1:8080/contact/2026-11-22_14-20-54_leia@alderaan.com
```

Update one field, then delete the contact:

```shell
curl -i -X PATCH http://127.0.0.1:8080/contact/2026-11-22_14-20-54_leia@alderaan.com \
  -H 'Content-Type: application/json' \
  -d '{"subject":"New subject"}'

curl -i -X DELETE http://127.0.0.1:8080/contact/2026-11-22_14-20-54_leia@alderaan.com
```

### Endpoints

| Method   | URI                   | Description               | Success | Errors   |
| -------- | --------------------- | ------------------------- | ------- | -------- |
| `POST`   | `/contact`            | Create a contact          | 201     | 400      |
| `GET`    | `/contact`            | List all contacts         | 200     | —        |
| `GET`    | `/contact/<filename>` | Get one contact           | 200     | 400, 404 |
| `PATCH`  | `/contact/<filename>` | Update one or more fields | 200     | 400, 404 |
| `DELETE` | `/contact/<filename>` | Delete a contact          | 204     | 400, 404 |

A contact has three fields sent in the body: `email` (must be a valid email), `subject` and `message` (strings).

### Project structure

```
app/
├── config/routes.json      route table (method, URI, controller)
├── lib/Http/               minimal HTTP layer: Router, Request, Response, JsonBody
├── src/Controllers/        one controller per endpoint
├── src/Services/           ContactStorage (files) and ContactValidator (rules)
└── var/contacts/           stored contacts, one JSON file per contact
```

## Documentation

There is no separate documentation yet. The endpoints and examples are described in the [Usage](#usage) section above.

## Contributing

This is a school project. Ideas and bug reports are welcome through GitHub Issues.

## Support

Use [GitHub Issues](https://github.com/aaimeraud/contact-api-php/issues) for reproducible bugs and feature requests.

## Security

Please do not report security vulnerabilities in public issues. Contact the repository owner directly instead. No `SECURITY.md` file exists yet.
