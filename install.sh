#!/bin/bash

# Laravel Web Installer Package Setup Script
# This script helps you set up and publish your package to Packagist

echo "🚀 Laravel Web Installer Package Setup"
echo "======================================"

# Check if git is initialized
if [ ! -d ".git" ]; then
    echo "📦 Initializing Git repository..."
    git init
    git add .
    git commit -m "Initial package structure"
fi

# Check if composer.json exists
if [ ! -f "composer.json" ]; then
    echo "❌ composer.json not found! Please run this script from the package root."
    exit 1
fi

echo "📋 Current package configuration:"
echo "Package name: $(grep '"name"' composer.json | cut -d'"' -f4)"
echo "Version: 1.0.0 (will be tagged)"

echo ""
echo "🔧 Next steps to publish your package:"
echo ""
echo "1. Update composer.json with your details:"
echo "   - Change 'codelone/laravel-web-installer' to 'yourvendor/yourpackage'"
echo "   - Update author information"
echo "   - Update repository URLs"
echo ""
echo "2. Create GitHub repository:"
echo "   - Create new repository on GitHub"
echo "   - Add remote: git remote add origin https://github.com/yourusername/yourrepo.git"
echo "   - Push code: git push -u origin main"
echo ""
echo "3. Register on Packagist:"
echo "   - Go to https://packagist.org"
echo "   - Sign in with GitHub"
echo "   - Submit your repository URL"
echo ""
echo "4. Tag your release:"
echo "   git tag v1.0.0"
echo "   git push origin v1.0.0"
echo ""
echo "5. Test installation:"
echo "   composer require yourvendor/yourpackage"
echo ""

# Install dependencies if vendor doesn't exist
if [ ! -d "vendor" ]; then
    echo "📦 Installing dependencies..."
    composer install
fi

echo "✅ Package setup complete!"
echo ""
echo "🎯 Package features included:"
echo "   ✅ License verification with API integration"
echo "   ✅ Database schema import from license server"
echo "   ✅ Real-time installation progress tracking"
echo "   ✅ Server requirements checking"
echo "   ✅ Folder permissions validation"
echo "   ✅ Filament-based beautiful UI"
echo "   ✅ Multi-step wizard interface"
echo "   ✅ Fallback installation system"
echo "   ✅ Admin user auto-creation"
echo "   ✅ Comprehensive documentation"
echo ""
echo "📚 Documentation: README.md"
echo "🧪 Tests: composer test"
echo "🎨 Code formatting: composer format"