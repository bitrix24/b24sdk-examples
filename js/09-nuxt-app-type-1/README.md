# Bitrix24 UI Starter

> **WARNING**
> We are still updating this page
> Some data may be missing here — we will complete it shortly.

Scopes: `crm,userfieldconfig,placement`

Look at docs to learn more:

- [Nuxt](https://nuxt.com/docs/getting-started/introduction)
- [@bitrix24/b24ui-nuxt](https://bitrix24.github.io/b24ui/)
- [@bitrix24/b24style](https://bitrix24.github.io/b24style/)
- [@bitrix24/b24icons](https://bitrix24.github.io/b24icons/)
- [@bitrix24/b24jssdk](https://bitrix24.github.io/b24jssdk/)

## Setup

Make sure to install the dependencies:

```bash
# pnpm
pnpm install
```

## Development Server

Start the development server on `http://localhost:3000/dev-folder/`:

```bash
# pnpm
pnpm run dev
```

## Production

Generate the application for production:

```bash
# pnpm
pnpm run generate-archive-for-b24
```

Then upload `\.output\archiverForB24.zip` to the bitrix24 application.

Check out the [deployment documentation](https://nuxt.com/docs/getting-started/deployment) for more information.
