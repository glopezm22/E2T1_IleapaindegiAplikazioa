# Etapa de construcción
FROM node:22-alpine as build

WORKDIR /app

# Argumentos de construcción
ARG VITE_API_URL
ENV VITE_API_URL=$VITE_API_URL

# Copiar archivos de dependencias
COPY package*.json ./

# Instalar dependencias
RUN apk add --no-cache python3 make g++
RUN npm install

# Copiar código fuente
COPY . .

# Construir aplicación para producción
RUN npm run build

# Verificar que la build se haya generado
RUN ls -la /app/dist

# Etapa de producción con Nginx
FROM nginx:alpine

# Copiar archivos construidos
COPY --from=build /app/dist /usr/share/nginx/html

# Copiar configuración de Nginx
COPY docker/production/nginx.conf /etc/nginx/conf.d/default.conf

# Permisos
RUN chown -R nginx:nginx /usr/share/nginx/html

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]