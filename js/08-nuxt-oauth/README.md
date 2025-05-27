# @bitrix24/b24ui-playground-nuxt-oauth

This example shows how to use OAuth connection to Bitrix24.

Required scopes: `crm`.

## Setting Up the Environment

To work with the project, you need to set up environment variables.

### Setup Steps
1. **Install Dependencies**:

Make sure you have all the necessary dependencies installed. You can do this with:

```bash
pnpm install
```

2. **Create a `.env` File**:

In the root of the project, create a file named `.env`.

3. **Use the Template to Fill `.env`**:
Open the `.env.example` file, which has an example of environment variable settings. Copy its contents into your `.env` file and replace the placeholders with your own values:

```plaintext
NUXT_SESSION_PASSWORD=a-random-password-with-at-least-32-characters
## App Keys #####################################
NUXT_OAUTH_BITRIX24_CLIENT_ID=app.xxxx.yyyyy
NUXT_OAUTH_BITRIX24_CLIENT_SECRET=xxxxxxxxxxxxxxxxxxxxxxxxxx
```

4. **Make Sure `.env` is in `.gitignore`**:

Check that the `.env` file is listed in `.gitignore` so it doesn't get pushed to the repository:

```plaintext
.env
```

5. **Run the Project**:

Once the environment variables are set, you can start the project with:

```bash
pnpm run dev
```

To test the production version

```bash
pnpm run build
pnpm run preview
```
