# Database

MySQL database for the Contact Manager. The tables are defined in `schema.sql`.

## Setup

```bash
mysql -u <user> -p <database_name> < db/schema.sql
```

## Tables

### users

| Column   | Type         | Notes                       |
|----------|--------------|-----------------------------|
| id       | INT UNSIGNED | Primary key, auto-increment |
| email    | VARCHAR(255) | Required, unique            |
| username | VARCHAR(50)  | Required, unique            |

## Adding to the Database

1. Add your `CREATE TABLE` to `schema.sql`.
2. Add a section for your table under **Tables** in this README.
