# Etapa de construcción
FROM node:22-alpine AS build

WORKDIR /app

COPY package*.json ./

RUN apk add --no-cache python3 make g++
RUN npm install

COPY . .

# VITE_API_URL se inyecta en tiempo de build para que Vite lo incluya en el bundle
ARG VITE_API_URL=http://localhost:8000/api
ENV VITE_API_URL=${VITE_API_URL}

RUN npm run build

# Etapa de producción con Nginx
FROM nginx:alpine

COPY --from=build /app/dist /usr/share/nginx/html

COPY docker/staging/nginx.conf /etc/nginx/conf.d/default.conf

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]
