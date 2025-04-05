# @bitrix24/b24ui-playground-nuxt-starter-bp-activity

Look at docs to learn more:

- [Nuxt](https://nuxt.com/docs/getting-started/introduction)
- [@bitrix24/b24ui-nuxt](https://bitrix24.github.io/b24ui/)
- [@bitrix24/b24style](https://bitrix24.github.io/b24style/)
- [@bitrix24/b24icons](https://bitrix24.github.io/b24icons/)
- [@bitrix24/b24jssdk](https://bitrix24.github.io/b24jssdk/)

## Setup

Make sure to install the dependencies:

```bash
# npm
npm install

# pnpm
pnpm install

# yarn
yarn install

# bun
bun install
```

## Development Server

Start the development server on `http://localhost:3000`:

```bash
# npm
npm run dev

# pnpm
pnpm run dev

# yarn
yarn dev

# bun
bun run dev
```

## Production

Build the application for production:

```bash
# npm
npm run build

# pnpm
pnpm run build

# yarn
yarn build

# bun
bun run build
```

Locally preview production build:

```bash
# npm
npm run preview

# pnpm
pnpm run preview

# yarn
yarn preview

# bun
bun run preview
```

Check out the [deployment documentation](https://nuxt.com/docs/getting-started/deployment) for more information.

---

# Docker

> These are experimental settings for using Docker.
> Still in early Alpha

## @todo
## Docker

```bash
cp .env.dev.example .env.dev
```

### RESTART
#### ALL 
```bash
docker-compose --env-file .env.dev stop

## You must undestend -> ssl will be removed
## docker volume prune
docker-compose down --volumes --rmi all --remove-orphans
docker-compose --env-file .env.dev up -d --build

# restart all
docker-compose down && docker-compose --env-file .env.dev up -d --build
```

```bash
docker-compose --env-file .env.dev up -d --build frontend
```

### STATUS 
```bash

docker ps
docker-compose --env-file .env.dev top
```

### LOG

@todo 
```bash
cd /home/bitrix/06-nuxt-ui-bp-activity && docker-compose logs -f frontend
cd /home/bitrix/06-nuxt-ui-bp-activity && docker-compose logs -f server
cd /home/bitrix/06-nuxt-ui-bp-activity && docker-compose logs -f letsencrypt
```

```bash
docker-compose --env-file .env.dev up --build frontend
docker-compose --env-file .env.dev up --build -d frontend

docker-compose --env-file .env.dev up server

docker-compose --env-file .env.dev up --build server
docker-compose --env-file .env.dev up --build letsencrypt

```

## Useful resources

- SSL Server Test [Qualys](https://www.ssllabs.com/ssltest/index.html)
