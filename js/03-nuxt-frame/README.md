# @bitrix24/b24jssdk-playground

A playground for testing the library.

## Core Components

- **Required scopes**: `crm, user_brief, pull, placement, userfieldconfig`

## Setting Up the Environment

To work with the project, you need to set up environment variables.

### Setup Steps
1. **Install Dependencies**:

Make sure you have all the necessary dependencies installed. You can do this with:

```bash
# pnpm
pnpm install
```

2. **Create a `.env` File**:

In the root of the project, create a file named `.env`. This file will hold sensitive information like URLs, user IDs, and secret keys.

3. **Use the Template to Fill `.env`**:

Open the `.env.example` file, which has an example of environment variable settings. Copy its contents into your `.env` file and replace the placeholders with your own values:

```plaintext
## For example: 35
NUXT_PUBLIC_B24_FORM_ID="0"

## For example: c9qk4k
NUXT_PUBLIC_B24_FORM_SECRET="insert_secret"

## For example: https://cdn-ru.bitrix24.com/b80599/crm/form/loader_34.js
NUXT_PUBLIC_B24_FORM_LOADER_SCRIPT="insert_loaderScript"

## AI.DeepSeek ##################################
DEEPSEEK_API_KEY='sk-xxxxxxxxx'

## App ##########################################
NUXT_PUBLIC_APP_URL='https://dev.example.com'
```

4. **Make Sure `.env` is in `.gitignore`**:

Check that the `.env` file is listed in `.gitignore` so it doesn't get pushed to the repository:

```plaintext
.env
```

## Development Server

Use the service [ngrok](https://ngrok.com) or [cloudpub](https://cloudpub.ru).

In `nuxt.config.ts` specify

```ts
export default defineNuxtConfig({
  // ...
  ssr: false,
  app: {
    baseURL: '/dev-folder/'
  },
  vite: {
    server: {
      // allow incoming requests from this host
      allowedHosts: [
        '******.ngrok-free.app',
        '******.cloudpub.ru'
      ],
      // and don't forget CORS, if needed:
      cors: true
    }
  }
})
```

Start the development server on `http://localhost:3000/dev-folder/`:

```bash
# pnpm
pnpm run dev
```

Next, write in the application registration form:
* Your handler path: `https://******.ngrok-free.app/dev-folder/index.html` or `https://******.cloudpub.ru/dev-folder/index.html`
* Initial installation path: `https://******.ngrok-free.app/dev-folder/install.html` or `https://******.cloudpub.ru/dev-folder/install.html`

> **Remember:**
> All application pages are named according to the template `some-page.html.client.vue`.

## Production as static application
Generate the application for production:
```bash
# pnpm
pnpm run generate-archive-for-b24
```

Then upload `\.output\archiverForB24.zip` to the application registration form.

## 🛠 Development Tools
AI-powered translation via DeepSeek (scripts in `frontend/tools`):
```shell
# Translate UI phrases
pnpm run translate-ui
```
