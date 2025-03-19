# Étape 1: Construction de l'image pour la construction du projet
FROM node:18 AS build

# Définir le répertoire de travail
WORKDIR /app

# Copier le package.json et package-lock.json (ou yarn.lock) dans le conteneur
COPY package*.json ./

# Installer les dépendances
RUN npm install

# Copier tous les fichiers du projet dans le conteneur
COPY . .

# Exécuter la construction de l'application
RUN npm run build

# Étape 2: Serveur de production
FROM nginx:alpine

# Copier les fichiers générés par Vite vers le répertoire de Nginx
COPY --from=build /app/dist /usr/share/nginx/html

# Exposer le port 80 pour l'accès HTTP
EXPOSE 80

# Démarrer Nginx en mode "foreground" pour garder le conteneur en fonctionnement
CMD ["nginx", "-g", "daemon off;"]
