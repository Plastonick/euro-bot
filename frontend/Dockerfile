FROM node:18-alpine AS build

ARG VITE_SWEEPSTAKES_API=http://127.0.0.1:8090
ENV VITE_SWEEPSTAKES_API=${VITE_SWEEPSTAKES_API}

WORKDIR /app

COPY package*.json ./
RUN npm ci

COPY . .
RUN npm run build

FROM nginx:1.27-alpine

COPY --from=build /app/dist /usr/share/nginx/html
