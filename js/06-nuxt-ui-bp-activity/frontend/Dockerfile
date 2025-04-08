FROM node:latest

LABEL maintainer="B24Sdk <github.com/bitrix24>"

WORKDIR /app

COPY package*.json ./

RUN npm install

COPY . .

EXPOSE 3000

CMD ["npm", "run", "dev"]
