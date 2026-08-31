# Personal Contact Manager

A web application that lets each user create an account, log in, and manage their own private contacts. Contacts are per-user — no data is shared between accounts.

## Overview

When the app loads, users are greeted with a login/register screen. After authenticating, they can add, edit, delete, and search their personal contacts. All searching is done server-side through a REST API, and the client talks to the server asynchronously (AJAX) using JSON.

## Features

- **User Authentication** — Register a new account and log in.
- **Contact Management (per user)** — Add, edit, and delete contacts. Contacts are private to each user.
- **Server-Side Search** — Search contacts through an API with partial matching. The client does not cache all contacts; every search queries the server.
- **Initial Page** — Login or registration is presented as soon as the app loads.

## Tech Stack

Built on the **LAMP** stack:

- **Linux** — server operating system
- **Apache** — web server
- **MySQL** — remote database
- **PHP** — server-side application logic and API

The frontend is AJAX-enabled and communicates with the backend entirely over JSON.

## API

The application exposes a **REST-style API**. Client and server exchange data as JSON, and all data-changing and search operations go through the API rather than the client holding state.

Example endpoints:

- `POST /api/search` — search a user's contacts with partial matching
- `POST /api/contacts` — add / edit / delete a contact

API endpoints are documented and demonstrated via **SwaggerHub**.

## Deployment

- Hosted on a remote server (e.g. Digital Ocean, AWS, Azure).
- Accessed via a **domain name** (not an IP address).
- Uses a **remote MySQL database** — no local-only setup.

## Getting Started (Local Development)

1. Set up a LAMP environment (Apache, MySQL, PHP).
2. Clone this repository into the Apache web root.
3. Create the MySQL database and import the schema.
4. Configure the database connection credentials.
5. Open the site in a browser and register a new account.
