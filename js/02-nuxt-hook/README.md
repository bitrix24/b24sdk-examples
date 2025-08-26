# @bitrix24/b24jssdk-playground

A playground for testing the library.

## Core Components

- **Required scopes**: `crm,user_brief`


## Setting Up the Environment

To work with the project, you need to set up environment variables.

### Setup Steps
1. **Install Dependencies**:

Make sure you have all the necessary dependencies installed. You can do this with:

```bash
pnpm install
```

2. **Create a `.env` File**:

In the root of the project, create a file named `.env`. This file will hold sensitive information like URLs, user IDs, and secret keys.

3. **Use the Template to Fill `.env`**:

Open the `.env.example` file, which has an example of environment variable settings. Copy its contents into your `.env` file and replace the placeholders with your own values:

```plaintext
#################
# Bitrix24 HOOK #
#################
## Specify the domain of Bitrix24. For example: https://your_domain.bitrix24.com/rest/1/xxxyyyxxx/
NUXT_B24_HOOK="insert"
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
