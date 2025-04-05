FROM node:latest
MAINTAINER B24Sdk <github.com/bitrix24>

WORKDIR /frontend

COPY package*.json ./
RUN npm install

COPY . .

EXPOSE 3000

CMD ["npm", "run", "dev"]
