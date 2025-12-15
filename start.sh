#!/bin/bash

echo "========================================="
echo "🚀 Démarrage de l'application Symfony"
echo "========================================="

if ! command -v docker &> /dev/null; then
    echo "❌ Docker n'est pas installé."
    exit 1
fi

if ! command -v docker-compose &> /dev/null; then
    echo "❌ Docker Compose n'est pas installé."
    exit 1
fi

echo "🛑 Arrêt des conteneurs existants..."
docker-compose down

echo "🔨 Construction des images Docker..."
docker-compose build

echo "▶️  Démarrage des conteneurs..."
docker-compose up -d

echo "⏳ Attente de la base de données..."
sleep 15

echo "📦 Installation des dépendances..."
docker-compose exec -T web composer install

echo "🗄️  Configuration de la base de données..."
docker-compose exec -T web php bin/console doctrine:database:create --if-not-exists

echo "🔄 Exécution des migrations..."
docker-compose exec -T web php bin/console doctrine:migrations:migrate --no-interaction

echo "📁 Création des répertoires d'uploads..."
docker-compose exec -T web mkdir -p public/uploads/business_photos public/uploads/review_photos
docker-compose exec -T web chmod -R 777 public/uploads

echo "🧹 Nettoyage du cache..."
docker-compose exec -T web php bin/console cache:clear

echo ""
echo "========================================="
echo "✅ Application démarrée avec succès!"
echo "========================================="
echo ""
echo "📍 Accès:"
echo "   - Web: http://localhost:8080"
echo "   - PhpMyAdmin: http://localhost:8081"
echo ""
echo "🔑 MySQL:"
echo "   - User: app_user"
echo "   - Password: app_password"
echo "========================================="