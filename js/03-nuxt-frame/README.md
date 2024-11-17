# @bitrix24/b24jssdk-playground

A playground for testing the library

## Setting Up the Environment

To work with the project, you need to set up environment variables.

### Setup Steps
1. **Install Dependencies**:

Make sure you have all the necessary dependencies installed. You can do this with:

```bash
npm install
```

2. **Create a `.env.local` File**:

In the root of the project, create a file named `.env.local`. This file will hold sensitive information like URLs, user IDs, and secret keys.

3. **Use the Template to Fill `.env.local`**:

Open the `.env.local.example` file, which has an example of environment variable settings. Copy its contents into your `.env.local` file and replace the placeholders with your own values:

```plaintext
## For example: 35
VITE_B24_FORM_ID="0"

## For example: c9qk4k
VITE_B24_FORM_SECRET="insert_secret"

## For example: https://cdn-ru.bitrix24.com/b80599/crm/form/loader_34.js
VITE_B24_FORM_LOADER_SCRIPT="insert_loaderScript"
```

4. **Make Sure `.env.local` is in `.gitignore`**:

Check that the `.env.local` file is listed in `.gitignore` so it doesn't get pushed to the repository:

```plaintext
.env.local
```

5. **Run the Project**:

Once the environment variables are set, you can start the project with:

```bash
npm run dev
```