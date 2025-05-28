# Bitrix24 BI Connector Demo

This project demonstrates how to create a custom connector for the [BI Builder] in Bitrix24. The connector uses local SQLite tables as the data source and is implemented as a local application based on the [B24PHPSDK](https://github.com/bitrix24/b24phpsdk).

## Features

- **Bitrix24 BI Connector**: Implements endpoints required by Bitrix24 BI Builder for integration.
- **Local SQLite Data Source**: Uses a local SQLite database (`data/demo.db`) with sample tables and data.
- **Flexible Table/Field Discovery**: Supports listing tables and describing their fields dynamically.
- **Data Filtering**: Allows filtering, selecting, and limiting data for BI queries.
- **Logging**: Uses Monolog for detailed request and error logging.
- **Dockerized**: Includes Dockerfile and Makefile for easy setup and development.
- **Environment Configuration**: Uses `.env` files for configuration and secrets management.

## Main Files

- [`src/Application.php`](src/Application.php): Main application logic, handles Bitrix24 app installation, logging, and service initialization, stores tokens.
- [`src/Bitrix24Connector.php`](src/Bitrix24Connector.php): Implements the main connector endpoints for BI Builder (check, table list, table description, data).
- [`src/DataConnector.php`](src/DataConnector.php): Handles SQLite database access, table/field discovery, and data queries with filtering.
- [`public/index.php`](public/index.php): Entry point for HTTP requests, routes actions to the connector.
- [`public/install.php`](public/install.php): Handles Bitrix24 app installation and authentication (The application must be created with the "Script only (no user interface)" option).
- [`sql/schema.sql`](sql/schema.sql): Schema and sample data for the SQLite database.
- [`Dockerfile`](Dockerfile): Docker image definition for local development and deployment.
- [`Makefile`](Makefile): Helper commands for building, running, and initializing the database.

## How It Works

1. **App Installation**: Handles Bitrix24 app installation events and stores authentication tokens.
2. **BI Endpoints**: Exposes endpoints for BI Builder:
   - `/index.php?action=check` — Health check endpoint.
   - `/index.php?action=table_list` — Returns available tables.
   - `/index.php?action=table_description&table=...` — Returns field metadata for a table.
   - `/index.php?action=data&table=...` — Returns data from a table with optional filtering and selection.
3. **Data Source**: All data is served from a local SQLite database, which can be initialized with `make init-db`.

## Getting Started

### Prerequisites

- Docker and Docker Compose
- (Optional) PHP 8.2+, Composer (for local development)

### Setup

1. **Clone the repository**
2. **Build and start the containers:**

   ```sh
   make build
   make up
   ```

3. **Initialize the SQLite database:**

   ```sh
   make init-db
   ```

4. **Access the application** at `http://localhost:8080/` (or your configured port).

5. **Expose your local web server to the internet using ngrok**  

   Bitrix24 requires your application to be accessible from the public internet.  
   Start ngrok to tunnel HTTP traffic to your local server (default Apache port is 8080):

   ```sh
   ngrok http 8080
   ```

   After running this command, ngrok will provide a public HTTPS URL (e.g., `https://abcd1234.ngrok.io`).  
   Use this URL as the endpoint when configuring your Bitrix24 application handlers.

    ```sh
    make build
    make up
    ```

6. **Register the application** in Bitrix24 at the ngrok public URL.

### Usage

После успешной установки и запуска приложения в вашем Bitrix24 появится новый BI Connector. Вы можете использовать его для добавления подключений и датасетов из локальных таблиц SQLite.

## Project Structure

- `src/` — Application source code
- `public/` — Web entry points
- `data/` — SQLite database file
- `sql/` — Database schema and seed data
- `config/` — Environment and auth configuration
- `var/log/` — Application logs
- `Dockerfile` — Docker image definition
- `Makefile` — Helper commands for building and running the application